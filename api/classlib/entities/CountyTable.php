<?php

class CountyTable
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT idcounty, countyName
            FROM cw_county
            ORDER BY countyName
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $countyId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT idcounty, countyName
            FROM cw_county
            WHERE idcounty = ?
        ");

        $stmt->execute([$countyId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}