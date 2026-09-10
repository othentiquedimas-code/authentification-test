<?php

declare(strict_types=1);

require_once BASE_PATH . '/helpers/security.php';

function requireAuth(): int
{
    startSecureSession();

    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

    if ($userId === false || $userId < 1) {
        header('Location: login.php', true, 302);
        exit;
    }

    return $userId;
}

function redirectIfAuthenticated(): void
{
    startSecureSession();

    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

    if ($userId !== false && $userId > 0) {
        header('Location: dashboard.php', true, 302);
        exit;
    }
}