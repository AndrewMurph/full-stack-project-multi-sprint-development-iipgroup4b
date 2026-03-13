<?php

class UserTypeTable
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT userTypeNr, userTypeDescr
            FROM cw_usertype
            ORDER BY userTypeNr
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $typeNr): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT userTypeNr, userTypeDescr
            FROM cw_usertype
            WHERE userTypeNr = ?
        ");

        $stmt->execute([$typeNr]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}