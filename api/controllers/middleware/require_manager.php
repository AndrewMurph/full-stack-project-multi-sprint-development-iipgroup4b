<?php

function requireManager(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userTypeNr = $_SESSION['user']['userTypeNr'] ?? null;

    if ((int)$userTypeNr !== 2) {
        header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/unauthorised.php");
        exit;
    }
}