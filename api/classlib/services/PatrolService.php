<?php

require_once __DIR__ . "/../entities/UserTable.php";
require_once __DIR__ . "/../entities/PatrolRosterTable.php";
require_once __DIR__ . "/../entities/PatrolScheduleTable.php";

class PatrolService
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

    public function listPatrols(): array
    {
        return $this->schedule->getAllWithEnabledVolunteerCount();
    }

    public function assignSupervisor(int $patrolNr, int $superUserNr): void
    {
        if ($patrolNr <= 0 || $superUserNr <= 0) {
            throw new InvalidArgumentException("Invalid PatrolNr or SuperUserNr");
        }

        // validate SUPER (userTypeNr = 4) + enabled
        if (!$this->users->isEnabledUserOfType($superUserNr, 4)) {
            throw new RuntimeException("That SuperUserNr is not an enabled SUPER (userTypeNr=4).");
        }

        $this->schedule->assignSuper($patrolNr, $superUserNr);
    }
    

    public function setPatrolStatus(int $patrolNr, int $statusNr): void
    {
        if ($patrolNr <= 0) {
            throw new InvalidArgumentException("Invalid PatrolNr");
        }

        $this->schedule->setStatus($patrolNr, $statusNr);
    }

    /**
     * Returns true if inserted, false if already existed.
     */
    public function assignVolunteer(int $patrolNr, int $volunteerNr): bool
    {
        if ($patrolNr <= 0 || $volunteerNr <= 0) {
            throw new InvalidArgumentException("Invalid PatrolNr or Volunteer ID");
        }

        // validate VOLUNTEER (userTypeNr = 3) + enabled
        if (!$this->users->isEnabledUserOfType($volunteerNr, 3)) {
            throw new RuntimeException("That user is not an enabled VOLUNTEER (userTypeNr=3).");
        }

        return $this->roster->assignVolunteerIfNotExists($patrolNr, $volunteerNr);
    }

    public function cancelPatrol(int $patrolNr): void
    {
        if ($patrolNr <= 0) {
            throw new ValidationException("Patrol number is required", 400);
        }

        $patrol = $this->schedule->findByPatrolNr($patrolNr);

        if (!$patrol) {
            throw new ValidationException("Patrol not found", 404);
        }

        // Cannot cancel past patrol
        if ($patrol["patrolDate"] < date("Y-m-d")) {
            throw new ValidationException("Cannot cancel past patrol", 400);
        }

        // Cannot cancel finalised patrol ( status 4)
        if ((int)$patrol["patrol_status"] === 4) {
            throw new ValidationException("Cannot cancel finalised patrol", 400);
        }

        $this->schedule->setStatusCancelled($patrolNr);
    }

    public function createPatrol(string $patrolDate, string $description = "Regular Scheduled Patrol"): int
    {
        $patrolDate = trim($patrolDate);
        $description = trim($description) ?: "Regular Scheduled Patrol";

        if ($patrolDate === "") {
            throw new ValidationException("Patrol date is required", 400);
        }

        // Ensure future date only
        if ($patrolDate < date("Y-m-d")) {
            throw new ValidationException("Cannot create patrol in the past", 400);
        }

        return $this->schedule->createPatrol($patrolDate, $description, 0);
    }
public function editPatrol(int $patrolNr, string $newDate, string $newDescription): void
{
    if ($patrolNr <= 0) {
        throw new ValidationException("Patrol number is required", 400);
    }

    $newDate = trim($newDate);
    $newDescription = trim($newDescription);

    if ($newDate === "" || $newDescription === "") {
        throw new ValidationException("Missing required fields", 400);
    }

    $patrol = $this->schedule->findByPatrolNr($patrolNr);

    if (!$patrol) {
        throw new ValidationException("Patrol not found", 404);
    }

    if ($patrol["patrolDate"] < date("Y-m-d")) {
        throw new ValidationException("Cannot edit past patrol", 400);
    }

    if ((int)$patrol["patrol_status"] === 4) {
        throw new ValidationException("Cannot edit finalised patrol", 400);
    }

    // also prevent setting NEW date into the past
    if ($newDate < date("Y-m-d")) {
        throw new ValidationException("Cannot set patrol date in the past", 400);
    }

    $this->schedule->updatePatrol($patrolNr, $newDate, $newDescription);
}

public function listPatrolsForUserType(int $userTypeNr): array
{
    // ROLE_VOLUNTEER = 3
    if ($userTypeNr === 3) {
        return $this->schedule->listReleasedOrFinalised();
    }

    // Manager/Admin/Super see everything
    return $this->schedule->listAll();
}

public function publishPatrol(int $patrolNr): void
{
    if ($patrolNr <= 0) {
        throw new ValidationException("Patrol number is required", 400);
    }

    $patrol = $this->schedule->findByPatrolNr($patrolNr);

    if (!$patrol) {
        throw new ValidationException("Patrol not found", 404);
    }

    if ($patrol["patrolDate"] < date("Y-m-d")) {
        throw new ValidationException("Cannot publish past patrol", 400);
    }

    if ((int)$patrol["patrol_status"] === 1) {
        throw new ValidationException("Patrol already published", 400);
    }

    $this->schedule->publishPatrol($patrolNr);
}

public function listPublishedFuturePatrols(): array
{
    return $this->schedule->listPublishedFuture();
}

public function listPublishedBetween(string $dateFrom, string $dateTo): array
{
    return $this->schedule->listPublishedBetween($dateFrom, $dateTo);
}
public function listFuturePatrolsForAvailability(): array
{
    return $this->schedule->listFutureForAvailability();
}
}