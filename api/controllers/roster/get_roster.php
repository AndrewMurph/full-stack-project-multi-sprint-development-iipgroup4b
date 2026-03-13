<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/guard.php';

header('Content-Type: application/json; charset=UTF-8');

// GET only (recommended)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed. Use GET."]);
    exit;
}

$user = require_guard();
$userTypeNr = (int)$user['userTypeNr'];

// Role mapping from cw_usertype
define('ROLE_ADMIN', 1);
define('ROLE_MANAGER', 2);
define('ROLE_VOLUNTEER', 3);
define('ROLE_SUPER', 4);

// Read patrolNr
$patrolNr = isset($_GET['patrolNr']) ? (int)$_GET['patrolNr'] : 0;
if ($patrolNr <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid patrolNr"]);
    exit;
}

try {
    // 1) Load patrol + status
    $p = $pdo->prepare("
        SELECT s.patrolNr, s.patrolDate, s.patrolDescription, s.SuperUserNr,
               s.patrol_status,
               ps.patrol_status_description AS patrol_status_desc
        FROM cw_patrol_schedule s
        LEFT JOIN cw_patrol_status ps ON ps.patrol_status_nr = s.patrol_status
        WHERE s.patrolNr = ?
        LIMIT 1
    ");
    $p->execute([$patrolNr]);
    $patrol = $p->fetch(PDO::FETCH_ASSOC);

    if (!$patrol) {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Patrol not found"]);
        exit;
    }

    $status = (int)$patrol['patrol_status'];

       // 3.6.2 Visibility:
       // Volunteer can ONLY view Released (1) or Finalised (4)
       if ($userTypeNr === ROLE_VOLUNTEER && !in_array($status, [1, 4], true)) {
           http_response_code(403);
           echo json_encode(["success" => false, "error" => "Roster not released"]);
           exit;
       }

       // 2) Get assigned volunteers (limit data for VOLUNTEER, show contacts for SUPER/MANAGER/ADMIN)
       if ($userTypeNr === ROLE_VOLUNTEER) {
           $r = $pdo->prepare("
               SELECT r.volunteer_ID_Nr,
                      u.FirstName, u.LastName
               FROM cw_patrol_roster r
               LEFT JOIN cw_user u ON u.UserNr = r.volunteer_ID_Nr
               WHERE r.patrolNr = ?
               ORDER BY u.LastName ASC, u.FirstName ASC
           ");
       } else {
           $r = $pdo->prepare("
               SELECT r.volunteer_ID_Nr,
                      u.FirstName, u.LastName, u.email, u.mobile
               FROM cw_patrol_roster r
               LEFT JOIN cw_user u ON u.UserNr = r.volunteer_ID_Nr
               WHERE r.patrolNr = ?
               ORDER BY u.LastName ASC, u.FirstName ASC
           ");
       }
       $r->execute([$patrolNr]);
       $volunteers = $r->fetchAll(PDO::FETCH_ASSOC);

  //include SUPER details ONLY for SUPER/MANAGER/ADMIN
    $super = null;
    if (in_array($userTypeNr, [ROLE_SUPER, ROLE_MANAGER, ROLE_ADMIN], true)) {
        if (!empty($patrol['SuperUserNr'])) {
            $s = $pdo->prepare("
                SELECT UserNr, FirstName, LastName, email
                FROM cw_user
                WHERE UserNr = ?
                LIMIT 1
            ");
            $s->execute([(int)$patrol['SuperUserNr']]);
            $super = $s->fetch(PDO::FETCH_ASSOC);
        }
    } else {
        //volunteers cannot see super assignment info
        unset($patrol['SuperUserNr']);
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "patrol" => $patrol,
            "super" => $super,
            "volunteers" => $volunteers
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}
