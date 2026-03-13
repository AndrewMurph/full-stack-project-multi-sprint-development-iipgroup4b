<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/guard_session.php";
require_once __DIR__ . "/../../classlib/services/RosterService.php";

require_session_guard(); // logged in (no role restriction)

$minVolunteers = 3;

$svc = new RosterService($pdo);
$rows = $svc->getResourcingStatus($minVolunteers);

return [
    "minVolunteers" => $minVolunteers,
    "rows" => $rows,
];