<?php
require_once __DIR__ . "/../config/db.php";

$members = [];

try {

    $sql = "
        SELECT 
            u.UserNr,
            CONCAT(u.FirstName, ' ', u.LastName) AS name,
            t.userTypeDescr AS role,
            u.email,
            u.mobile AS phone,
            u.userEnabled AS status
        FROM cw_user u
        LEFT JOIN cw_usertype t 
            ON u.userTypeNr = t.userTypeNr
        ORDER BY u.UserNr DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $members = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

function initials($name) {
    $parts = explode(' ', trim($name));
    $letters = '';
    foreach ($parts as $p) {
        if ($p !== '') {
            $letters .= strtoupper($p[0]);
        }
    }
    return substr($letters, 0, 2);
}

function formatStatus($status) {
    return ((int)$status === 1) ? 'Active' : 'Inactive';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Members</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
            color: #0f172a;
            font-family: Arial, sans-serif;
        }

        .page-wrap {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px 32px 40px;
        }

        .topbar {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 0;
            padding: 14px 24px;
            margin-bottom: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 2rem;
            font-weight: 800;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            color: #2563eb;
            background: #eff6ff;
            font-size: 1.3rem;
        }

        .nav-pills .nav-link {
            color: #64748b;
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-pills .nav-link.active {
            background: #eaf2ff;
            color: #2563eb;
        }

        .content-panel {
            background: #f5f7fb;
            border: 1px solid #e8edf5;
            border-top: 0;
            padding: 28px 24px 20px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
        }

        .add-btn {
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 600;
        }

        .member-card {
            background: #fff;
            border: 1px solid #dfe7f1;
            border-radius: 14px;
            padding: 18px 18px 20px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            height: 100%;
        }

        .member-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .member-head {
            display: flex;
            gap: 14px;
            min-width: 0;
        }

        .avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.45rem;
            flex-shrink: 0;
        }

        .member-name {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .member-role {
            margin-top: 2px;
            color: #64748b;
            font-size: 0.95rem;
        }

        .card-actions {
            display: flex;
            gap: 12px;
            flex-shrink: 0;
        }

        .card-actions a {
            text-decoration: none;
            font-size: 0.95rem;
        }

        .edit-icon {
            color: #64748b;
        }

        .delete-icon {
            color: #ef4444;
        }

        .member-info {
            margin-top: 18px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #64748b;
            margin-bottom: 8px;
            font-size: 0.96rem;
            word-break: break-word;
        }

        .info-row i {
            font-size: 0.95rem;
        }

        .member-divider {
            border: 0;
            border-top: 1px solid #e8edf5;
            margin: 16px 0 16px;
        }

        .status-badge {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            font-size: 0.86rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 999px;
        }

        .navbar-toggler {
            border: 0;
            box-shadow: none !important;
        }

        @media (max-width: 991.98px) {
            .page-wrap {
                padding: 16px;
            }

            .topbar {
                padding: 12px 16px;
            }

            .content-panel {
                padding: 20px 16px 16px;
            }

            .page-title {
                font-size: 1.7rem;
            }
        }

        @media (max-width: 767.98px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            .add-btn {
                width: 100%;
            }

            .member-top {
                flex-direction: column;
            }

            .card-actions {
                align-self: flex-end;
            }

            .brand {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

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
                <li class="nav-item"><a class="nav-link active" href="volunteer_members.php">👥 Members</a></li>
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
            <a class="list-group-item list-group-item-action" href="volunteer_dashboard.php">📋 Dashboard</a>
            <a class="list-group-item list-group-item-action" href="volunteer_schedule.php">🗓️ Schedule</a>
            <a class="list-group-item list-group-item-action active" href="volunteer_members.php">👥 Members</a>
            <a class="list-group-item list-group-item-action" href="volunteer_availability.php">✅ Availability</a>
            <hr>
            <a class="list-group-item list-group-item-action text-danger" href="login_form.php">🚪 Logout</a>
        </div>
    </div>
</div>
</nav>
    <div class="content-panel">
        <div class="page-header">
            <h1 class="page-title">Team Members</h1>
        </div>

        <div class="row g-3 g-lg-4">
            <?php foreach ($members as $member): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="member-card">
                        <div class="member-top">
                            <div class="member-head">
                                <div class="avatar"><?= htmlspecialchars(initials($member['name'])) ?></div>

                                <div>
                                    <h2 class="member-name"><?= htmlspecialchars($member['name']) ?></h2>
                                    <div class="member-role"><?= htmlspecialchars($member['role']) ?></div>
                                </div>
                            </div>


                        </div>

                        <div class="member-info">
                            <div class="info-row">
                                <i class="bi bi-envelope"></i>
                                <span><?= htmlspecialchars($member['email']) ?></span>
                            </div>
                            <div class="info-row">
                                <i class="bi bi-telephone"></i>
                                <span><?= htmlspecialchars($member['phone']) ?></span>
                            </div>
                        </div>

                        <hr class="member-divider">

                        <span class="status-badge"><?= htmlspecialchars($member['status']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>