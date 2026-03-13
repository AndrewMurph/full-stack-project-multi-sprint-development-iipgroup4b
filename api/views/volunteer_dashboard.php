<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../classlib/services/PatrolService.php";
require_once __DIR__ . "/../classlib/entities/UserTable.php";
require_once __DIR__ . "/../classlib/services/RosterService.php";

$rosterService = new RosterService($pdo);
$patrolService = new PatrolService($pdo);
$userTable = new UserTable($pdo);

$patrols = $patrolService->listPublishedFuturePatrols();
$users = $userTable->getAllUsers();


// Stats
$stats = [
    "active_patrols" => 0,
    "upcoming_patrols" => count($patrols),
    "active_members" => count(array_filter(
        $users,
        fn($u) => $u["userEnabled"] == 1 && $u["userTypeNr"] == 3
    ))
];

// Upcoming patrol list
$upcomingPatrols = [];

foreach ($patrols as $p) {
      $memberRows = $rosterService->getVolunteerNamesForPatrol((int)$p['patrolNr']);

      $memberNames = array_map(function($m) {
          return trim($m['FirstName'] . ' ' . $m['LastName']);
      }, $memberRows);

    $upcomingPatrols[] = [
    "date" => date("l, F j, Y", strtotime($p["patrolDate"])),
    "time" => "TBD",
    "district" => $p["patrolDescription"] ?? "Patrol",
    "members" => $memberNames,
    "supervisorAssigned" => !empty($p["SuperUserNr"])
];

}

// Active patrols
$activePatrols = [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Patrol Scheduler - Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f6f7fb; }
        .content { width: 100%; padding: 28px; }

        .brand { display:flex; align-items:center; gap:12px; font-weight:700; font-size:22px; }
        .brand-badge {
            width: 36px; height: 36px; border-radius: 12px;
            background: #e7f1ff; display: grid; place-items: center;
            color: #0d6efd; font-weight: 700;
        }

        .stat-card, .section-card {
            border: 0; border-radius: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 999px;
            display: grid; place-items: center; font-weight: 700;
        }

        .section-card { overflow: hidden; }
        .section-header {
            padding: 18px 20px;
            border-bottom: 1px solid #eef2f7;
            font-weight: 800;
            font-size: 20px;
            background: #fff;
        }
        .section-body { padding: 18px 20px; background: #fff; }

        .pill {
            font-size: 12px; padding: 6px 10px;
            border-radius: 999px; display: inline-block;
            background: #e9fbe9; color: #1f7a1f; font-weight: 700;
        }

        .meta-line { color: #475569; display:flex; gap:18px; flex-wrap:wrap; }
        .member-tag {
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            padding: 6px 10px;
            display: inline-flex; align-items: center; gap: 8px;
            background: #f8fafc; color: #0f172a;
            margin-right: 10px; margin-top: 10px;
            white-space: nowrap;
        }
        .muted { color: #64748b; }

        /* ✅ Mobile tweaks */
        @media (max-width: 576px) {
            .content { padding: 14px; }
            .brand { font-size: 18px; }
            .brand-badge { width: 32px; height: 32px; border-radius: 10px; }
            .section-header { font-size: 18px; padding: 14px 14px; }
            .section-body { padding: 14px 14px; }
            .member-tag { font-size: 13px; padding: 6px 9px; }
            .meta-line { gap: 10px; font-size: 14px; }
            .display-6 { font-size: 2rem; } /* stats number smaller on mobile */
        }
        .logo{
            width: 60px;
            display:block;
            margin: 0 auto 10px;
            border-radius: 50%;
        }
        .nav-pills .nav-link.active { background:#e7f1ff; color:#0d6efd; }
        .nav-pills .nav-link { border-radius: 12px; padding: 10px 14px; display:flex; gap:10px; color:#334155; }
    </style>
</head>

<body>
<main class="content">

    <nav class="navbar navbar-expand-md bg-white border-bottom sticky-top">
        <div class="container-fluid px-3 px-md-4">

            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="volunteer_dashboard.php">
                <img src="../img/smalllogo.png" width="38" height="38" style="border-radius:50%;" alt="">
                <span>Claddagh Watch</span>
            </a>


            <button class="navbar-toggler d-md-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#mobileMenu"
                    aria-controls="mobileMenu" aria-label="Open menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="d-none d-md-flex ms-auto">
                <ul class="nav nav-pills gap-2">
                    <li class="nav-item"><a class="nav-link active" href="volunteer_dashboard.php">📋 Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="volunteer_schedule.php">🗓️ Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="volunteer_members.php">👥 Members</a></li>
                    <li class="nav-item"><a class="nav-link" href="volunteer_availability.php">✅ Availability</a></li>
                    <hr>
                    <li class="nav-item"><a class="nav-link" href="login_form.php">🚪 Logout</a></li>
                </ul>
            </div>

        </div>
    </nav>


    <div class="offcanvas offcanvas-end d-md-none" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold" id="mobileMenuLabel">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action active" href="volunteer_dashboard.php">📋 Dashboard</a>
                <a class="list-group-item list-group-item-action" href="volunteer_schedule.php">🗓️ Schedule</a>
                <a class="list-group-item list-group-item-action" href="volunteer_members.php">👥 Members</a>
                <a class="list-group-item list-group-item-action" href="volunteer_availability.php">✅ Availability</a>
                <hr>
                <a class="list-group-item list-group-item-action text-danger" href="login_form.php">🚪 Logout</a>
            </div>
        </div>
    </div>
    </nav>

    <!-- Stats -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card stat-card p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="muted">Active Patrols</div>
                        <div class="display-6 fw-bold"><?= (int)$stats['active_patrols'] ?></div>
                    </div>
                    <div class="stat-icon" style="background:#e9fbe9;color:#16a34a;">!</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card stat-card p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="muted">Upcoming Patrols</div>
                        <div class="display-6 fw-bold"><?= (int)$stats['upcoming_patrols'] ?></div>
                    </div>
                    <div class="stat-icon" style="background:#e7f1ff;color:#0d6efd;">🕒</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card stat-card p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="muted">Active Members</div>
                        <div class="display-6 fw-bold"><?= (int)$stats['active_members'] ?></div>
                    </div>
                    <div class="stat-icon" style="background:#f3e8ff;color:#7c3aed;">👤</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Patrols -->
    <div class="card section-card mb-4">
        <div class="section-header">Active Patrols</div>
        <div class="section-body">
            <?php if (empty($activePatrols)): ?>
                <div class="muted">No active patrols right now.</div>
            <?php else: ?>
                <?php foreach ($activePatrols as $p): ?>
                    <div class="mb-2">
                        <span class="pill"><?= htmlspecialchars($p['status']) ?></span>
                    </div>

                    <div class="meta-line mb-2">
                        <div>🕘 <?= htmlspecialchars($p['time']) ?></div>
                        <div>📍 <?= htmlspecialchars($p['district']) ?></div>
                    </div>

                    <div class="d-flex flex-wrap">
                        <?php foreach ($p['members'] as $m): ?>
                            <span class="member-tag">👤 <?= htmlspecialchars($m) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Patrols -->
    <div class="card section-card">
        <div class="section-header">Upcoming Patrols</div>
        <div class="section-body">
            <?php if (empty($upcomingPatrols)): ?>
                <div class="muted">No upcoming patrols.</div>
            <?php else: ?>
                <?php foreach ($upcomingPatrols as $index => $p): ?>
                    <div class="mb-4">
                        <div class="fw-bold mb-2"><?= htmlspecialchars($p['date']) ?></div>

                        <div class="meta-line mb-2">
                            <div>🕘 <?= htmlspecialchars($p['time']) ?></div>
                            <div>📍 <?= htmlspecialchars($p['district']) ?></div>
                        </div>
                        <div class="meta-line mb-2">
                            <div>🛡️ Supervisor assigned: <?= $p['supervisorAssigned'] ? 'Yes' : 'No' ?></div>
                        </div>

                                            <div class="d-flex flex-wrap">
                            <?php if (empty($p['members'])): ?>
                                <span class="member-tag">👤 No volunteers assigned</span>
                            <?php else: ?>
                                <?php foreach ($p['members'] as $m): ?>
                                    <span class="member-tag">👤 <?= htmlspecialchars($m) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($index < count($upcomingPatrols) - 1): ?>
                        <hr class="my-3" style="border-color:#eef2f7;">
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>