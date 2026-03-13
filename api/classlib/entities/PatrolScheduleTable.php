<?php
require_once __DIR__ . "/../baseClasses/TableEntity.php";

class PatrolScheduleTable extends TableEntity
{
    public function __construct(PDO $db)
    {
        parent::__construct($db, "cw_patrol_schedule");
    }

    public function getAllWithEnabledVolunteerCount(): array
    {
        $stmt = $this->run("
            SELECT
                ps.patrolNr,
                ps.patrolDate,
                ps.patrolDescription,
                ps.patrol_status,
                ps.SuperUserNr,
                COUNT(u.UserNr) AS volunteerCount
            FROM cw_patrol_schedule ps
            LEFT JOIN cw_patrol_roster r ON r.patrolNr = ps.patrolNr
            LEFT JOIN cw_user u
                ON u.UserNr = r.volunteer_ID_Nr
               AND u.userEnabled = 1
            GROUP BY
                ps.patrolNr, ps.patrolDate, ps.patrolDescription, ps.patrol_status, ps.SuperUserNr
            ORDER BY ps.patrolDate ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFuturePatrols(): array
    {
        $stmt = $this->run("
            SELECT patrolNr, patrolDate, patrolDescription, SuperUserNr
            FROM cw_patrol_schedule
            WHERE patrolDate >= CURDATE()
            ORDER BY patrolDate ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignSuper(int $patrolNr, int $superUserNr): void
    {
        $this->run("
            UPDATE cw_patrol_schedule
            SET SuperUserNr = ?
            WHERE patrolNr = ?
        ", [$superUserNr, $patrolNr]);
    }

    public function setStatus(int $patrolNr, int $status): void
    {
        $this->run("
            UPDATE cw_patrol_schedule
            SET patrol_status = ?
            WHERE patrolNr = ?
        ", [$status, $patrolNr]);
    }

    public function exists(int $patrolNr): bool
    {
        $stmt = $this->run("
        SELECT 1
        FROM cw_patrol_schedule
        WHERE patrolNr = ?
        LIMIT 1
    ", [$patrolNr]);

        return (bool)$stmt->fetchColumn();
    }

    public function findByPatrolNr(int $patrolNr): ?array
    {
        $stmt = $this->run("
        SELECT patrolNr, patrolDate, patrol_status, patrolDescription, SuperUserNr
        FROM cw_patrol_schedule
        WHERE patrolNr = ?
        LIMIT 1
    ", [$patrolNr]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function setStatusCancelled(int $patrolNr): void
    {
        // Your cancel logic sets patrol_status = 2
        $this->setStatus($patrolNr, 2);
    }

    public function createPatrol(string $patrolDate, string $description, int $status = 0): int
    {
        $this->run("
        INSERT INTO cw_patrol_schedule (patrolDate, patrolDescription, patrol_status)
        VALUES (?, ?, ?)
    ", [$patrolDate, $description, $status]);

        return (int)$this->db->lastInsertId();
    }

public function updatePatrol(int $patrolNr, string $newDate, string $newDescription): void
{
    $this->run("
        UPDATE cw_patrol_schedule
        SET patrolDate = ?, patrolDescription = ?
        WHERE patrolNr = ?
    ", [$newDate, $newDescription, $patrolNr]);
}
public function listAll(): array
{
    $stmt = $this->run("
        SELECT *
        FROM cw_patrol_schedule
        ORDER BY patrolDate ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function listReleasedOrFinalised(): array
{
    $stmt = $this->run("
        SELECT *
        FROM cw_patrol_schedule
        WHERE patrol_status IN (1, 4)
        ORDER BY patrolDate ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function publishPatrol(int $patrolNr): void
{
    $this->setStatus($patrolNr, 1);
}

public function listPublishedFuture(): array
{
    $stmt = $this->run("
        SELECT patrolNr, patrolDate, SuperUserNr, patrolDescription
        FROM cw_patrol_schedule
        WHERE patrol_status = 1
          AND patrolDate >= CURDATE()
        ORDER BY patrolDate ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getStatusByPatrolNr(int $patrolNr): ?int
{
    $stmt = $this->run("
        SELECT patrol_status
        FROM cw_patrol_schedule
        WHERE patrolNr = ?
        LIMIT 1
    ", [$patrolNr]);

    $v = $stmt->fetchColumn();
    return ($v === false) ? null : (int)$v;
}

public function getReleasedFuturePatrols(): array
{
    $stmt = $this->run("
        SELECT patrolNr, patrolDate, patrolDescription
        FROM cw_patrol_schedule
        WHERE patrol_status = 1
          AND patrolDate >= CURDATE()
        ORDER BY patrolDate ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function listPublishedBetween(string $dateFrom, string $dateTo): array
{
    $stmt = $this->run("
        SELECT patrolNr, patrolDate, patrolDescription, SuperUserNr, patrol_status
        FROM cw_patrol_schedule
        WHERE patrol_status = 1
          AND patrolDate BETWEEN ? AND ?
        ORDER BY patrolDate ASC
    ", [$dateFrom, $dateTo]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function listFutureForAvailability(): array
{
    $stmt = $this->db->prepare("
        SELECT patrolNr, patrolDate, patrolDescription, patrol_status
        FROM cw_patrol_schedule
        WHERE patrolDate >= CURDATE()
          AND patrol_status = 0
        ORDER BY patrolDate ASC
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}