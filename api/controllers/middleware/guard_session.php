<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_session_guard(): array {
  if (!isset($_SESSION['user']['UserNr'])) {
    header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/login_form.php");
    exit;
  }
  return $_SESSION['user'];
}

function require_session_role(array $allowed): void {
  $u = require_session_guard();
  if (!in_array((int)$u['userTypeNr'], $allowed, true)) {
    header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/unauthorised.php");
    exit;
  }

}