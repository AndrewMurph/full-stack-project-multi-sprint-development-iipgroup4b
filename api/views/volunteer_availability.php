<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../classlib/services/PatrolService.php";
require_once __DIR__ . "/../classlib/services/RosterService.php";

session_start();

$userNr = $_SESSION['user']['UserNr'] ?? 0;

if ($userNr <= 0) {
    header("Location: login_form.php");
    exit;
}

$patrolService = new PatrolService($pdo);
$rosterService = new RosterService($pdo);

$upcomingPatrols = $patrolService->listFuturePatrolsForAvailability();
$currentAvailability = $rosterService->getAvailabilityForVolunteer($userNr);

$currentAvailabilityMap = [];
foreach ($currentAvailability as $item) {
    $currentAvailabilityMap[(int)$item['patrolNr']] = true;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Availability</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body{
            background:#f5f7fb;
            color:#0f172a;
            font-family: Arial, sans-serif;
        }

        .page-wrap{
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 32px 40px;
        }

        .page-label{
            color:#94a3b8;
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .shell{
            background:#fff;
            border:1px solid #e5eaf2;
        }

        .topbar{
            padding:14px 20px;
            border-bottom:1px solid #e5eaf2;
            background:#fff;
        }

        .brand{
            display:flex;
            align-items:center;
            gap:10px;
            font-weight:800;
            font-size:1.6rem;
            color:#0f172a;
            text-decoration:none;
        }

        .brand-icon{
            width:34px;
            height:34px;
            border-radius:10px;
            display:grid;
            place-items:center;
            background:#eff6ff;
            color:#2563eb;
            font-size:1.15rem;
        }

        .nav-pills .nav-link{
            color:#64748b;
            border-radius:10px;
            padding:10px 14px;
            display:flex;
            align-items:center;
            gap:8px;
        }

        .nav-pills .nav-link.active{
            background:#eaf2ff;
            color:#2563eb;
        }

        .content{
            padding:20px;
        }

        .page-title{
            font-size:2rem;
            font-weight:800;
            margin-bottom:18px;
        }

        .panel{
            background:#fff;
            border:1px solid #e5eaf2;
            border-radius:12px;
            margin-bottom:16px;
            overflow:hidden;
        }

        .panel-header{
            padding:14px 16px;
            border-bottom:1px solid #edf2f7;
            font-weight:800;
            font-size:1rem;
        }

        .panel-subtitle{
            display:block;
            font-size:0.86rem;
            color:#64748b;
            font-weight:500;
            margin-top:2px;
        }

        .panel-body{
            padding:14px 16px;
        }

        .guide-box{
            background:#eff6ff;
            border:1px solid #dbeafe;
            border-radius:10px;
            padding:14px 16px;
            color:#334155;
        }

        .guide-title{
            color:#2563eb;
            font-weight:700;
            font-size:0.95rem;
            margin-bottom:6px;
            display:flex;
            align-items:flex-start;
            gap:8px;
        }

        .guide-list{
            margin:0;
            padding-left:24px;
            color:#64748b;
            font-size:0.92rem;
            line-height:1.7;
        }

        .availability-card{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            background:#f0fdf4;
            border:1px solid #ccefd7;
            border-radius:10px;
            padding:14px 16px;
            margin-bottom:10px;
        }

        .availability-card:last-child{
            margin-bottom:0;
        }

        .availability-left{
            display:flex;
            align-items:flex-start;
            gap:10px;
            min-width:0;
        }

        .availability-check{
            color:#22c55e;
            font-size:1rem;
            margin-top:2px;
        }

        .availability-date{
            font-weight:700;
            font-size:0.98rem;
            color:#334155;
            margin-bottom:2px;
        }

        .availability-meta{
            color:#64748b;
            font-size:0.88rem;
        }

        .withdraw-link{
            color:#ef4444;
            font-weight:600;
            text-decoration:none;
            white-space:nowrap;
            font-size:0.9rem;
        }

        .patrol-row{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            padding:14px 16px;
            border-bottom:1px solid #edf2f7;
            background:#fff;
        }

        .patrol-row:last-child{
            border-bottom:0;
        }

        .patrol-row.released{
            background:#f8fafc;
        }

        .patrol-left{
            display:flex;
            align-items:flex-start;
            gap:10px;
            min-width:0;
        }

        .patrol-icon{
            color:#64748b;
            margin-top:2px;
            font-size:0.95rem;
        }

        .patrol-date{
            font-weight:700;
            color:#334155;
            font-size:0.96rem;
            margin-bottom:2px;
        }

        .patrol-meta{
            color:#64748b;
            font-size:0.88rem;
        }

        .btn-submit-availability{
            background:#2563eb;
            color:#fff;
            border:0;
            border-radius:8px;
            padding:8px 14px;
            font-size:0.88rem;
            font-weight:600;
            white-space:nowrap;
        }

        .btn-submit-availability:hover{
            background:#1d4ed8;
            color:#fff;
        }

        .status-pill{
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:8px 12px;
            border-radius:8px;
            font-size:0.86rem;
            font-weight:700;
            white-space:nowrap;
        }

        .status-available{
            background:#dcfce7;
            color:#16a34a;
        }

        .status-released{
            background:#e2e8f0;
            color:#94a3b8;
        }

        .release-note{
            display:inline-block;
            margin-top:4px;
            background:#e2e8f0;
            color:#64748b;
            border-radius:6px;
            padding:2px 8px;
            font-size:0.72rem;
            font-weight:600;
        }

        .navbar-toggler{
            border:0;
            box-shadow:none !important;
        }

        @media (max-width: 991.98px){
            .page-wrap{
                padding:16px;
            }

            .content{
                padding:16px;
            }
        }

        @media (max-width: 767.98px){
            .page-title{
                font-size:1.6rem;
            }

            .availability-card,
            .patrol-row{
                flex-direction:column;
                align-items:stretch;
            }

            .withdraw-link,
            .btn-submit-availability,
            .status-pill{
                align-self:flex-start;
            }

            .btn-submit-availability{
                width:100%;
                text-align:center;
            }

            .brand{
                font-size:1.2rem;
            }

            .guide-list{
                padding-left:18px;
            }
        }
    </style>
</head>
<body>

<div class="page-wrap">
    <div class="page-label">Availability</div>

    <div class="shell">
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
                        <li class="nav-item"><a class="nav-link " href="volunteer_dashboard.php">📋 Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="volunteer_schedule.php">🗓️ Schedule</a></li>
                        <li class="nav-item"><a class="nav-link" href="volunteer_members.php">👥 Members</a></li>
                        <li class="nav-item"><a class="nav-link active" href="volunteer_availability.php">✅ Availability</a></li>
                        <hr>
                        <li class="nav-item"><a class="nav-link" href="login_form.php">🚪 Logout</a></li>
                    </ul>
                </div>

            </div>
        </nav>

    </div>
        <div class="offcanvas offcanvas-end d-md-none" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title fw-bold" id="mobileMenuLabel">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body">
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action" href="volunteer_dashboard.php">📋 Dashboard</a>
                    <a class="list-group-item list-group-item-action" href="volunteer_schedule.php">🗓️ Schedule</a>
                    <a class="list-group-item list-group-item-action" href="volunteer_members.php">👥 Members</a>
                    <a class="list-group-item list-group-item-action active" href="volunteer_availability.php">✅ Availability</a>
                    <hr>
                    <a class="list-group-item list-group-item-action text-danger" href="login_form.php">🚪 Logout</a>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="page-title">Volunteer Availability</div>
                <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Availability submitted successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['withdraw'])): ?>
    <div class="alert alert-success">Availability withdrawn successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>
            <div class="guide-box mb-3">
                <div class="guide-title">
                    <i class="bi bi-info-circle"></i>
                    <span>Availability Submission Guidelines</span>
                </div>
                <ul class="guide-list">
                    <li>Submit your availability for one or more patrol dates</li>
                    <li>You can withdraw availability before roster release</li>
                    <li>Once roster is released, availability cannot be changed</li>
                </ul>
            </div>

            <div class="panel">
                <div class="panel-header">Your Current Availability</div>
                <div class="panel-body">
              <?php if (empty($currentAvailability)): ?>
    <div class="text-muted">You have not submitted availability yet.</div>
<?php else: ?>
    <?php foreach ($currentAvailability as $item): ?>
        <div class="availability-card">
            <div class="availability-left">
                <div class="availability-check"><i class="bi bi-check-lg"></i></div>
                <div>
                    <div class="availability-date">
                        <?= htmlspecialchars(date("l, F j, Y", strtotime($item['patrolDate']))) ?>
                    </div>
                    <div class="availability-meta">
                        TBD · <?= htmlspecialchars($item['patrolDescription'] ?? 'Patrol') ?>
                    </div>
                </div>
            </div>

            <form method="POST" action="../controllers/roster/withdraw_availability.php" class="m-0">
                <input type="hidden" name="patrolNr" value="<?= (int)$item['patrolNr'] ?>">
                <button type="submit" class="withdraw-link border-0 bg-transparent p-0">
                    <i class="bi bi-x-lg"></i> Withdraw
                </button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    Upcoming Patrol Dates
                    <span class="panel-subtitle">Click to submit your availability</span>
                </div>

                <?php if (empty($upcomingPatrols)): ?>
    <div class="panel-body">
        <div class="text-muted">No patrols currently open for availability.</div>
    </div>
<?php else: ?>
    <?php foreach ($upcomingPatrols as $patrol): ?>
        <?php
            $patrolNr = (int)$patrol['patrolNr'];
            $alreadyAvailable = isset($currentAvailabilityMap[$patrolNr]);
        ?>
        <div class="patrol-row">
            <div class="patrol-left">
                <div class="patrol-icon"><i class="bi bi-calendar3"></i></div>
                <div>
                    <div class="patrol-date">
                        <?= htmlspecialchars(date("l, F j, Y", strtotime($patrol['patrolDate']))) ?>
                    </div>
                    <div class="patrol-meta">
                        TBD · <?= htmlspecialchars($patrol['patrolDescription'] ?? 'Patrol') ?>
                    </div>
                </div>
            </div>

            <div>
                <?php if ($alreadyAvailable): ?>
                    <span class="status-pill status-available">
                        <i class="bi bi-check-lg"></i> Available
                    </span>
                <?php else: ?>
                    <form method="POST" action="../controllers/roster/submit_availability.php" class="m-0">
                        <input type="hidden" name="patrolNr" value="<?= $patrolNr ?>">
                        <button type="submit" class="btn-submit-availability">
                            Submit Availability
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>