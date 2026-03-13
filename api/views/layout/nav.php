<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userTypeNr = $_SESSION['user']['userTypeNr'] ?? null;
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow mb-4">
  <div class="container-fluid">

    <!-- Brand -->
    <a class="navbar-brand" href="#">CWP Roster</a>

    <!-- Mobile toggle button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">

        <li class="nav-item">
          <a class="nav-link" href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/login_form.php">Login</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/register_form.php">Register</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/reset_password_form.php">Reset Password</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/view_all_users.php">View Users</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/roster_status.php">Roster Status</a>
        </li>

        <?php if ($userTypeNr == 2): ?>
        <li class="nav-item">
          <a class="nav-link" href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/dashboard.php">
            Ops Manager
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>

  </div>
</nav>

