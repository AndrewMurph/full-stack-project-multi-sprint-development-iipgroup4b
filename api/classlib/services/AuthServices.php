<?php
require_once __DIR__ . "/../entities/UserTable.php";
require_once __DIR__ . "/../../config/jwt.php";
require_once __DIR__ . "/../../libs/jwt_loader.php";
require_once __DIR__ . "/../baseClasses/ValidationException.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthServices
{
    private UserTable $users;

    public function __construct(PDO $db)
    {
        $this->users = new UserTable($db);
    }

    /**
     * @throws ValidationException
     */
    public function changePassword(int $userNr, string $oldPassword, string $newPassword): void
    {
        $hash = $this->users->getPasswordHashIfEnabled($userNr);
        if ($hash === null) {
            throw new ValidationException("User Not Found", 404);
        }

        if (!password_verify($oldPassword, $hash)) {
            throw new ValidationException("Your Password is incorrect", 401);
        }

        // password rules
        if (strlen($newPassword) < 8 ||
            !preg_match('/[A-Z]/', $newPassword) ||
            !preg_match('/[^a-zA-Z0-9]/', $newPassword)
        ) {
            throw new ValidationException(
                "New Password must have over 8 Characters and contain atleast one capital and special character.",
                400
            );
        }

        if (password_verify($newPassword, $hash)) {
            throw new ValidationException("New password must be different from old password", 400);
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $ok = $this->users->updatePasswordHash($userNr, $newHash);
        if (!$ok) {
            // update didn't affect rows
            throw new ValidationException("Password update failed", 500);
        }
    }

public function generatePasswordResetLink(string $email): array
{
    $email = trim($email);
    if ($email === "") {
        throw new ValidationException("Email required", 400);
    }

    // Don't reveal whether email exists
    $generic = ["message" => "If account exists, reset link generated"];

    $userNr = $this->users->findUserNrByEmail($email);
    if ($userNr === null) {
        return $generic;
    }

    // jwt.php should provide $resetKey
    // Token valid 15 minutes
    $payload = [
        "iss" => "cw_roster",
        "aud" => "cwp_users",
        "iat" => time(),
        "exp" => time() + 900,
        "purpose" => "password_reset",
        "data" => [
            "UserNr" => $userNr
        ]
    ];

    $token = JWT::encode($payload, $GLOBALS['resetKey'], 'HS256');

    // Build link
    $reset_link =
        "http://localhost:8081/full-stack-project-multi-sprint-development-iipgroup4b/api/auth/reset_password.php?token="
        . $token;

    return [
        "message" => "Reset link generated",
        "reset_link" => $reset_link
    ];
}

public function login(string $email, string $password): array
{
    $email = trim($email);

    if ($email === "" || $password === "") {
        throw new ValidationException("Email and Password must be filled", 400);
    }

    $user = $this->users->findForLoginByEmail($email);

    // do not reveal whether email exists
    if (!$user || !password_verify($password, $user['PassWord'])) {
        throw new ValidationException("Invalid email or password", 401);
    }

    if ((int)$user['userEnabled'] !== 1) {
        throw new ValidationException("User account is disabled", 403);
    }

    $payload = [
        "iss" => "cw_roster",
        "aud" => "cwp_users",
        "iat" => time(),
        "exp" => time() + 3600,
        "purpose" => "login",
        "data" => [
            "UserNr" => (int)$user["UserNr"],
            "email" => $user["email"],
            "userTypeNr" => (int)$user["userTypeNr"]
        ]
    ];

    // jwt.php provides $LoginKey
    $jwt = JWT::encode($payload, $GLOBALS['LoginKey'], 'HS256');

    // Session user
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user'] = [
        "UserNr" => (int)$user["UserNr"],
        "email" => $user["email"],
        "userTypeNr" => (int)$user["userTypeNr"]
    ];

    // remove password before returning any user data
    unset($user['PassWord']);

    return [
        "token" => $jwt,
        "user" => $user
    ];
}
public function logout(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // clear session array
    $_SESSION = [];

    // remove session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
public function getSessionUser(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user'])) {
        throw new ValidationException("Not logged in", 401);
    }

    return (array)$_SESSION['user'];
}

public function register(array $data): int
{
    $firstName = trim((string)($data["FirstName"] ?? ""));
    $lastName  = trim((string)($data["LastName"] ?? ""));
    $email     = trim((string)($data["email"] ?? ""));
    $mobile    = trim((string)($data["mobile"] ?? ""));
    $password  = (string)($data["PassWord"] ?? "");

    $userID      = $email;
    $userEnabled = 1;
    $userTypeNr  = 99;

    if ($firstName === "" || $lastName === "" || $email === "" || $password === "") {
        throw new ValidationException("Missing required fields", 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new ValidationException("Invalid email", 400);
    }

    if ($mobile !== "" && !preg_match('/^[0-9+\s\-]{7,20}$/', $mobile)) {
        throw new ValidationException("Invalid mobile number, must be at least 7 characters", 400);
    }

    if ($this->users->emailExists($email)) {
        throw new ValidationException("Email already registered", 409);
    }

    if (strlen($password) < 8) {
        throw new ValidationException("Password must be at least 8 characters", 400);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    return $this->users->insertUser([
        "FirstName" => $firstName,
        "LastName" => $lastName,
        "email" => $email,
        "mobile" => $mobile,
        "userID" => $userID,
        "userEnabled" => $userEnabled,
        "userTypeNr" => $userTypeNr,
        "PassWordHash" => $hash,
    ]);
}

public function resetPasswordWithToken(string $token, string $newPassword): void
{
    if ($token === "" || $newPassword === "") {
        throw new ValidationException("Token and new password required", 400);
    }

    if (strlen($newPassword) < 8) {
        throw new ValidationException("Password must be at least 8 characters", 400);
    }

    try {
        $decoded = JWT::decode($token, new Key($GLOBALS['resetKey'], 'HS256'));
    } catch (Throwable $e) {
        throw new ValidationException("Invalid or expired token", 401);
    }

    if (!isset($decoded->purpose) || $decoded->purpose !== "password_reset") {
        throw new ValidationException("Invalid or expired token", 401);
    }

    if (!isset($decoded->data->UserNr)) {
        throw new ValidationException("Invalid or expired token", 401);
    }

    $userNr = (int)$decoded->data->UserNr;

    $hash = password_hash($newPassword, PASSWORD_BCRYPT);

    $ok = $this->users->updatePasswordHash($userNr, $hash);
    if (!$ok) {
        // user might not exist
        throw new ValidationException("Password reset failed", 500);
    }
}
}