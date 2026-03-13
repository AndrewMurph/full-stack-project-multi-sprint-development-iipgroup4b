<?php
require_once __DIR__ . "/../entities/UserTable.php";

class UserService
{
    private UserTable $users;

    public function __construct(PDO $db)
    {
        $this->users = new UserTable($db);
    }

    public function listUsers(): array
    {
        return $this->users->getAllUsers();
    }

}