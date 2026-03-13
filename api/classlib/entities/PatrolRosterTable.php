<?php
require_once __DIR__ . "/../baseClasses/TableEntity.php";

class PatrolRosterTable extends TableEntity
{
    public function __construct(PDO $db)
    {
        parent::__construct($db, "cw_patrol_roster");
    }

    public function assignVolunteerIfNotExists(int $patrolNr, int $volunteerNr): bool
    {
        $stmt = $this->run("
            INSERT INTO cw_patrol_roster (patrolNr, volunteer_ID_Nr)
            SELECT ?, ?
            FROM DUAL
            WHERE NOT EXISTS (
                SELECT 1
                FROM cw_patrol_roster
                WHERE patrolNr = ? AND volunteer_ID_Nr = ?
            )
        ", [$patrolNr, $volunteerNr, $patrolNr, $volunteerNr]);

        // rowCount() === 1 means it inserted; 0 means it already existed
        return $stmt->rowCount() === 1;
    }

    public function countEnabledVolunteers(int $patrolNr): int
    {
        $stmt = $this->run("
            SELECT COUNT(*)
            FROM cw_patrol_roster r
            JOIN cw_user u ON u.UserNr = r.volunteer_ID_Nr
            WHERE r.patrolNr = ?
              AND u.userEnabled = 1
        ", [$patrolNr]);

        return (int)$stmt->fetchColumn();
    }

public function getVolunteerNamesForPatrol(int $patrolNr): array
{
    $stmt = $this->run("
        SELECT u.FirstName, u.LastName
        FROM cw_patrol_roster r
        JOIN cw_user u ON u.UserNr = r.volunteer_ID_Nr
        WHERE r.patrolNr = ?
          AND u.userEnabled = 1
        ORDER BY u.FirstName, u.LastName
    ", [$patrolNr]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getAvailabilityForVolunteer(int $volunteerId): array
{
    $stmt = $this->run("
        SELECT 
            ps.patrolNr,
            ps.patrolDate,
            ps.patrolDescription,
            ps.patrol_status
        FROM cw_patrol_roster pr
        INNER JOIN cw_patrol_schedule ps
            ON ps.patrolNr = pr.patrolNr
        WHERE pr.volunteer_ID_Nr = ?
          AND ps.patrolDate >= CURDATE()
        ORDER BY ps.patrolDate ASC
    ", [$volunteerId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function removeVolunteer(int $patrolNr, int $volunteerId): bool
{
    $stmt = $this->run("
        DELETE FROM cw_patrol_roster
        WHERE patrolNr = ?
          AND volunteer_ID_Nr = ?
    ", [$patrolNr, $volunteerId]);

    return $stmt->rowCount() > 0;
}
}