<?php

require_once __DIR__ . "/../entities/UserTable.php";
require_once __DIR__ . "/../entities/PatrolRosterTable.php";
require_once __DIR__ . "/../entities/PatrolScheduleTable.php";

class RosterService
{
    private UserTable $users;
    private PatrolRosterTable $roster;
    private PatrolScheduleTable $schedule;

    public function __construct(PDO $db)
    {
        $this->users = new UserTable($db);
        $this->roster = new PatrolRosterTable($db);
        $this->schedule = new PatrolScheduleTable($db);
    }

    public function getResourcingStatus(int $minVolunteers = 3): array
    {
        $patrols = $this->schedule->getFuturePatrols();
        $out = [];

        foreach ($patrols as $p) {
            $patrolNr = (int)$p['patrolNr'];

            $volunteerCount = $this->roster->countEnabledVolunteers($patrolNr);

            $superUserNr = (int)($p['SuperUserNr'] ?? 0);
            $superAssigned = $superUserNr > 0 && $this->users->isEnabledUserOfType($superUserNr, 4);

            $out[] = [
                "patrolNr" => $patrolNr,
                "patrolDate" => $p['patrolDate'],
                "patrolDescription" => $p['patrolDescription'],
                "volunteerCount" => $volunteerCount,
                "superAssigned" => $superAssigned,
                "status" => ($volunteerCount >= $minVolunteers && $superAssigned)
                    ? "Sufficiently Resourced"
                    : "Under-Resourced",
            ];
        }

        return $out;
    }
public function assignVolunteer(int $patrolNr, int $volunteerId, int $currentUserTypeNr): void
{
    // validate inputs
    if ($patrolNr <= 0 || $volunteerId <= 0) {
        throw new ValidationException("Invalid input", 400);
    }

    // ensure patrol exists
  if (!$this->schedule->exists($patrolNr)) {
      throw new ValidationException("Patrol not found", 404);
  }

    // check roster lock rule based on patrol_status
    $status = $this->schedule->getStatusByPatrolNr($patrolNr);
    if ($status === null) {
        throw new ValidationException("Patrol not found", 404);
    }

    $ROLE_ADMIN = 1;
    $ROLE_MANAGER = 2;

    // Released (1) or Finalised (4) => locked for everyone except manager/admin
    if (in_array($status, [1, 4], true) && !in_array($currentUserTypeNr, [$ROLE_MANAGER, $ROLE_ADMIN], true)) {
        throw new ValidationException("Roster is released. Only Manager may modify.", 403);
    }

    // ensure volunteer exists (prevents FK errors)
    if (!$this->users->userExists($volunteerId)) {
        throw new ValidationException("Volunteer user not found", 400);
    }

    // insert if not exists
    $inserted = $this->roster->assignVolunteerIfNotExists($patrolNr, $volunteerId);
    if (!$inserted) {
        throw new ValidationException("Volunteer already assigned to this patrol", 409);
    }
}

public function getVolunteerNamesForPatrol(int $patrolNr): array
{
    if ($patrolNr <= 0) {
        throw new ValidationException("Invalid patrol number", 400);
    }

    return $this->roster->getVolunteerNamesForPatrol($patrolNr);
}
public function getAvailabilityForVolunteer(int $volunteerId): array
{
    if ($volunteerId <= 0) {
        throw new ValidationException("Invalid volunteer ID", 400);
    }

    return $this->roster->getAvailabilityForVolunteer($volunteerId);
}
public function submitAvailability(int $patrolNr, int $volunteerId): void
{
    if ($patrolNr <= 0 || $volunteerId <= 0) {
        throw new ValidationException("Invalid input", 400);
    }

    if (!$this->schedule->exists($patrolNr)) {
        throw new ValidationException("Patrol not found", 404);
    }

    $status = $this->schedule->getStatusByPatrolNr($patrolNr);

    if ($status !== 0) {
        throw new ValidationException("Availability can only be submitted before roster release", 403);
    }

    if (!$this->users->userExists($volunteerId)) {
        throw new ValidationException("Volunteer not found", 404);
    }

    $inserted = $this->roster->assignVolunteerIfNotExists($patrolNr, $volunteerId);

    if (!$inserted) {
        throw new ValidationException("Availability already submitted", 409);
    }
}
public function withdrawAvailability(int $patrolNr, int $volunteerId): void
{
    if ($patrolNr <= 0 || $volunteerId <= 0) {
        throw new ValidationException("Invalid input", 400);
    }

    if (!$this->schedule->exists($patrolNr)) {
        throw new ValidationException("Patrol not found", 404);
    }

    $status = $this->schedule->getStatusByPatrolNr($patrolNr);

    if ($status !== 0) {
        throw new ValidationException(
            "Availability cannot be withdrawn after roster release",
            403
        );
    }

    $removed = $this->roster->removeVolunteer($patrolNr, $volunteerId);

    if (!$removed) {
        throw new ValidationException("Availability not found", 404);
    }
}

}