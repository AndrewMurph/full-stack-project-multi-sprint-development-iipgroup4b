<?php

class PatrolStatusTable
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT patrol_status_nr, patrol_status_description
            FROM cw_patrol_status
            ORDER BY patrol_status_nr
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $statusNr): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT patrol_status_nr, patrol_status_description
            FROM cw_patrol_status
            WHERE patrol_status_nr = ?
        ");

        $stmt->execute([$statusNr]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function exists(int $statusNr): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM cw_patrol_status
            WHERE patrol_status_nr = ?
        ");

        $stmt->execute([$statusNr]);

        return $stmt->fetchColumn() > 0;
    }
}