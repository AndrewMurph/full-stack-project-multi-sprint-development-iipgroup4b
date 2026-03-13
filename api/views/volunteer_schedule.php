<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../classlib/services/PatrolService.php";
require_once __DIR__ . "/../classlib/services/RosterService.php";

// ====== 1) Week navigation (Mon-Sun) ======
$offset = isset($_GET['w']) ? (int)$_GET['w'] : 0;
$today = new DateTime('today');
$today->modify(($offset * 7) . ' days');

// get Monday of that week
$monday = clone $today;
$dayOfWeek = (int)$monday->format('N'); // 1=Mon ... 7=Sun
$monday->modify('-' . ($dayOfWeek - 1) . ' days');

$sunday = clone $monday;
$sunday->modify('+6 days');

$weekLabel = $monday->format('M j') . ' - ' . $sunday->format('M j, Y');

// ====== 2) Load real patrols from DB ======
$patrolService = new PatrolService($pdo);
$rosterService = new RosterService($pdo);

$patrols = $patrolService->listPublishedBetween(
    $monday->format('Y-m-d'),
    $sunday->format('Y-m-d')
);

// Build events array in same structure your HTML expects
$events = [];

foreach ($patrols as $p) {
    $patrolDate = $p['patrolDate'] ?? null;

    if (!$patrolDate) {
        continue;
    }

    $dateObj = DateTime::createFromFormat('Y-m-d', $patrolDate);
    if (!$dateObj) {
        continue;
    }

    if ($dateObj < $monday || $dateObj > $sunday) {
        continue;
    }

    $key = $dateObj->format('Y-m-d');

    $memberRows = $rosterService->getVolunteerNamesForPatrol((int)$p['patrolNr']);

    $memberNames = array_map(function($m) {
        return trim(($m['FirstName'] ?? '') . ' ' . ($m['LastName'] ?? ''));
    }, $memberRows);

    $events[$key][] = [
        'id' => (int)($p['patrolNr'] ?? 0),
        'time' => 'TBD',
        'district' => $p['patrolDescription'] ?? 'Patrol',
        'members' => $memberNames,
        'supervisorAssigned' => !empty($p['SuperUserNr'])
    ];
}

// Days array Mon..Sun for rendering
$days = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $monday;
    $d->modify("+$i days");
    $days[] = $d;
}

// today highlight
$todayStr = (new DateTime('today'))->format('Y-m-d');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Weekly Schedule</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f6f7fb; }

        .topbar {
            background:#fff;
            border-bottom:1px solid #eef2f7;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .nav-pills .nav-link {
            border-radius: 12px;
            padding: 10px 14px;
            display:flex;
            gap:10px;
            color:#334155;
        }

        .nav-pills .nav-link.active {
            background:#e7f1ff;
            color:#0d6efd;
        }

        .page {
            padding: 22px;
        }

        .title-row {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            margin-bottom: 14px;
        }

        .title-row h1 {
            font-weight: 900;
            margin:0;
            font-size: clamp(26px, 2.8vw, 40px);
        }

        .week-nav {
            display:flex;
            align-items:center;
            gap:10px;
            background:#fff;
            border:1px solid #eef2f7;
            border-radius: 14px;
            padding: 8px 10px;
            box-shadow: 0 8px 24px rgba(15,23,42,0.06);
            white-space: nowrap;
        }

        .week-nav a {
            text-decoration:none;
            width:34px;
            height:34px;
            border-radius: 12px;
            display:grid;
            place-items:center;
            color:#0f172a;
            background:#f8fafc;
            border:1px solid #eef2f7;
        }

        .week-label {
            font-weight: 700;
            padding: 0 8px;
            color:#0f172a;
        }

        .week-grid {
            background:#fff;
            border:1px solid #eef2f7;
            border-radius: 16px;
            overflow:hidden;
            box-shadow: 0 10px 28px rgba(15,23,42,0.06);
        }

        .grid-header, .grid-body {
            display:grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .grid-header > div {
            padding: 14px 12px;
            border-right: 1px solid #eef2f7;
            text-align:center;
            color:#334155;
            font-weight: 800;
            letter-spacing: .02em;
            background:#fff;
        }

        .grid-header > div:last-child {
            border-right:0;
        }

        .grid-cell {
            min-height: 440px;
            padding: 12px;
            border-top: 1px solid #eef2f7;
            border-right: 1px solid #eef2f7;
            background:#fff;
        }

        .grid-cell:last-child {
            border-right:0;
        }

        .daynum {
            font-size: 28px;
            font-weight: 900;
            line-height: 1;
            color:#0f172a;
            margin-top: 6px;
        }

        .dow {
            font-size: 12px;
            font-weight: 900;
            color:#64748b;
            letter-spacing:.08em;
        }

        .is-today {
            background: #eff6ff;
        }

        .is-today .daynum {
            color:#0d6efd;
        }

        .event-card {
            border: 1px solid rgba(13,110,253,.25);
            background:#f5faff;
            border-radius: 12px;
            padding: 10px 10px;
            margin-top: 10px;
        }

        .event-link {
            text-decoration:none;
            color:inherit;
            display:block;
        }

        .event-link:hover .event-card {
            transform:translateY(-2px);
            box-shadow:0 8px 18px rgba(15,23,42,0.08);
            transition:0.2s ease;
        }

        .event-line {
            display:flex;
            gap:8px;
            align-items:flex-start;
            color:#0f172a;
            font-size: 14px;
        }

        .event-line .icon {
            width:18px;
            text-align:center;
            opacity:.85;
        }

        .member {
            color:#334155;
        }

        .mobile-daybar {
            display:flex;
            gap:8px;
            overflow:auto;
            padding-bottom: 6px;
        }

        .day-chip {
            flex: 0 0 auto;
            border:1px solid #eef2f7;
            background:#fff;
            border-radius: 14px;
            padding: 10px 12px;
            min-width: 92px;
            text-align:center;
            cursor:pointer;
            user-select:none;
            box-shadow: 0 8px 20px rgba(15,23,42,0.05);
        }

        .day-chip.active {
            border-color: rgba(13,110,253,.35);
            background:#eff6ff;
            color:#0d6efd;
            font-weight: 800;
        }

        .mobile-list {
            margin-top: 12px;
            background:#fff;
            border:1px solid #eef2f7;
            border-radius: 16px;
            padding: 12px;
            box-shadow: 0 10px 28px rgba(15,23,42,0.06);
        }

        .logo {
            width: 60px;
            display:block;
            margin: 0 auto 10px;
            border-radius: 50%;
        }

        @media (max-width: 992px) {
            .grid-cell { min-height: 360px; }
        }

        @media (max-width: 768px) {
            .page { padding: 14px; }
            .week-grid { display:none; }
            .mobile-only { display:block; }
        }

        @media (min-width: 769px) {
            .mobile-only { display:none; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-md bg-white border-bottom sticky-top">
    <div class="container-fluid px-3 px-md-4">

        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="volunteer_dashboard.php">
            <img src="../img/smalllogo.png" alt="logo" class="logo"/>
            <span>Claddagh Watch</span>
        </a>

        <button class="navbar-toggler d-md-none" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#mobileMenu"
                aria-controls="mobileMenu" aria-label="Open menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse d-none d-md-flex justify-content-end">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item"><a class="nav-link" href="volunteer_dashboard.php">📋 Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="volunteer_schedule.php">🗓️ Schedule</a></li>
                <li class="nav-item"><a class="nav-link" href="volunteer_members.php">👥 Members</a></li>
                <li class="nav-item"><a class="nav-link" href="volunteer_availability.php">✅ Availability</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>

    </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold" id="mobileMenuLabel">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <div class="list-group list-group-flush">
            <a class="list-group-item list-group-item-action" href="volunteer_dashboard.php">📋 Dashboard</a>
            <a class="list-group-item list-group-item-action active" href="volunteer_schedule.php">🗓️ Schedule</a>
            <a class="list-group-item list-group-item-action" href="volunteer_members.php">👥 Members</a>
            <a class="list-group-item list-group-item-action" href="volunteer_availability.php">✅ Availability</a>
            <hr>
            <a class="list-group-item list-group-item-action text-danger" href="login_form.php">🚪 Logout</a>
        </div>
    </div>
</div>

<div class="page">
    <div class="title-row">
        <h1>Weekly Schedule</h1>

        <div class="week-nav">
            <a href="?w=<?= $offset - 1 ?>" aria-label="Previous week">‹</a>
            <div class="week-label"><?= htmlspecialchars($weekLabel) ?></div>
            <a href="?w=<?= $offset + 1 ?>" aria-label="Next week">›</a>
        </div>
    </div>

    <!-- Mobile view -->
    <div class="mobile-only">
        <div class="mobile-daybar" id="dayBar">
            <?php foreach ($days as $d):
                $key = $d->format('Y-m-d');
                $isToday = ($key === $todayStr);
                ?>
                <div class="day-chip <?= $isToday ? 'active' : '' ?>" data-day="<?= htmlspecialchars($key) ?>">
                    <div style="font-size:12px;font-weight:900;letter-spacing:.08em;opacity:.7;">
                        <?= strtoupper($d->format('D')) ?>
                    </div>
                    <div style="font-size:22px;font-weight:900;line-height:1.1;">
                        <?= $d->format('j') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mobile-list" id="mobileList"></div>
    </div>

    <!-- Desktop / tablet grid -->
    <div class="week-grid">
        <div class="grid-header">
            <?php foreach ($days as $d): ?>
                <div><?= strtoupper($d->format('D')) ?></div>
            <?php endforeach; ?>
        </div>

        <div class="grid-body">
            <?php foreach ($days as $d):
                $key = $d->format('Y-m-d');
                $isToday = ($key === $todayStr);
                $dayEvents = $events[$key] ?? [];
                ?>
                <div class="grid-cell <?= $isToday ? 'is-today' : '' ?>">
                    <div class="text-center">
                        <div class="dow"><?= strtoupper($d->format('D')) ?></div>
                        <div class="daynum"><?= $d->format('j') ?></div>
                    </div>

                    <?php foreach ($dayEvents as $ev): ?>
                        <a href="patrol_detail.php?id=<?= (int)$ev['id'] ?>" class="event-link">
                            <div class="event-card">
                                <div class="event-line">
                                    <div class="icon">🕘</div>
                                    <div><strong style="color:#0d6efd;"><?= htmlspecialchars($ev['time']) ?></strong></div>
                                </div>
                                <div class="event-line mt-1">
                                    <div class="icon">📍</div>
                                    <div><?= htmlspecialchars($ev['district']) ?></div>
                                </div>
                                <div class="event-line mt-1">
                                    <div class="icon">🛡️</div>
                                    <div>Supervisor assigned: <?= !empty($ev['supervisorAssigned']) ? 'Yes' : 'No' ?></div>
                                </div>

                                <?php if (empty($ev['members'])): ?>
                                    <div class="event-line mt-1 member">
                                        <div class="icon">👤</div>
                                        <div>No volunteers assigned</div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($ev['members'] as $m): ?>
                                        <div class="event-line mt-1 member">
                                            <div class="icon">👤</div>
                                            <div><?= htmlspecialchars($m) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const EVENTS = <?= json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    function renderMobileDay(dayKey){
        const box = document.getElementById('mobileList');
        const list = EVENTS[dayKey] || [];

        if(!list.length){
            box.innerHTML = `
                <div style="padding:18px;">
                    <div style="font-weight:900;font-size:18px;">No patrols</div>
                    <div style="color:#64748b;margin-top:6px;">No scheduled patrols for this day.</div>
                </div>
            `;
            return;
        }

        const html = list.map(ev => {
            const members = (ev.members && ev.members.length)
                ? ev.members.map(m => `
                    <div class="event-line mt-1 member">
                        <div class="icon">👤</div>
                        <div>${escapeHtml(m)}</div>
                    </div>
                `).join('')
                : `
                    <div class="event-line mt-1 member">
                        <div class="icon">👤</div>
                        <div>No volunteers assigned</div>
                    </div>
                `;

            return `
                <a href="patrol_detail.php?id=${encodeURIComponent(ev.id)}" class="event-link">
                    <div class="event-card" style="margin-top:0;margin-bottom:12px;">
                        <div class="event-line">
                            <div class="icon">🕘</div>
                            <div><strong style="color:#0d6efd;">${escapeHtml(ev.time)}</strong></div>
                        </div>
                        <div class="event-line mt-1">
                            <div class="icon">📍</div>
                            <div>${escapeHtml(ev.district)}</div>
                        </div>
                        <div class="event-line mt-1">
                            <div class="icon">🛡️</div>
                            <div>Supervisor assigned: ${ev.supervisorAssigned ? 'Yes' : 'No'}</div>
                        </div>
                        ${members}
                    </div>
                </a>
            `;
        }).join('');

        box.innerHTML = html;
    }

    function escapeHtml(str){
        return String(str)
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'","&#039;");
    }

    const dayChips = document.querySelectorAll('.day-chip');
    let selected = document.querySelector('.day-chip.active')?.dataset.day || (dayChips[0]?.dataset.day);

    renderMobileDay(selected);

    dayChips.forEach(chip => {
        chip.addEventListener('click', () => {
            dayChips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            renderMobileDay(chip.dataset.day);
        });
    });
</script>
</body>
</html>