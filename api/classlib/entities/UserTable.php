<?php
require_once __DIR__ . "/../baseClasses/TableEntity.php";

class UserTable extends TableEntity
{
    public function __construct(PDO $db)
    {
        parent::__construct($db, "cw_user");
    }

    public function isEnabledSuper(int $userNr): bool
    {
        $stmt = $this->run("
            SELECT COUNT(*)
            FROM cw_user
            WHERE UserNr = ?
              AND userEnabled = 1
              AND userTypeNr = 4
        ", [$userNr]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function getEnabledSupers(): array
    {
        $stmt = $this->run("
            SELECT UserNr
            FROM cw_user
            WHERE userEnabled = 1 AND userTypeNr = 4
            ORDER BY UserNr
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

  public function getPasswordHashIfEnabled(int $userNr): ?string
    {
        $stmt = $this->run("
            SELECT PassWord
            FROM cw_user
            WHERE UserNr = ? AND userEnabled = 1
            LIMIT 1
        ", [$userNr]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['PassWord'] : null;
    }

  public function updatePasswordHash(int $userNr, string $hash): bool
    {
        $stmt = $this->run("
            UPDATE cw_user
            SET PassWord = ?
            WHERE UserNr = ?
        ", [$hash, $userNr]);

        return $stmt->rowCount() > 0;
    }

public function findUserNrByEmail(string $email): ?int
{
    $stmt = $this->run("
        SELECT UserNr
        FROM cw_user
        WHERE email = ?
        LIMIT 1
    ", [$email]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['UserNr'] : null;
}

public function findForLoginByEmail(string $email): ?array
{
    $stmt = $this->run("
        SELECT UserNr, FirstName, LastName, email, userID, userEnabled, userTypeNr, idcounty, PassWord
        FROM cw_user
        WHERE email = ?
        LIMIT 1
    ", [$email]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

public function emailExists(string $email): bool
{
    $stmt = $this->run("
        SELECT 1
        FROM cw_user
        WHERE email = ?
        LIMIT 1
    ", [$email]);

    return (bool)$stmt->fetchColumn();
}

public function insertUser(array $data): int
{
    $stmt = $this->run("
        INSERT INTO cw_user
            (FirstName, LastName, PassWord, email, mobile, userID, userEnabled, userTypeNr)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?)
    ", [
        $data['FirstName'],
        $data['LastName'],
        $data['PassWordHash'],
        $data['email'],
        $data['mobile'],
        $data['userID'],
        $data['userEnabled'],
        $data['userTypeNr'],
    ]);

    return (int)$this->db->lastInsertId();
}

public function getAllUsers(): array
{
    $stmt = $this->run("
        SELECT
            UserNr,
            FirstName,
            LastName,
            email,
            mobile,
            userID,
            userEnabled,
            userTypeNr,
            idcounty
        FROM cw_user
        ORDER BY UserNr DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function isEnabledUserOfType(int $userNr, int $userTypeNr): bool
    {
        $stmt = $this->run("
        SELECT COUNT(*)
        FROM cw_user
        WHERE UserNr = ?
          AND userEnabled = 1
          AND userTypeNr = ?
    ", [$userNr, $userTypeNr]);

        return (int)$stmt->fetchColumn() > 0;
    }

public function userExists(int $userNr): bool
{
    $stmt = $this->run("
        SELECT 1
        FROM cw_user
        WHERE UserNr = ?
        LIMIT 1
    ", [$userNr]);

    return (bool)$stmt->fetchColumn();
}
}