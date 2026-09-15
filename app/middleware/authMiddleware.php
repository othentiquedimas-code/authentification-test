<?php

declare(strict_types=1);

require_once BASE_PATH . '/helpers/security.php';

function redirectToLogin(): void
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $redirect = str_contains($scriptName, '/admin/') ? '../login.php' : 'login.php';

    header('Location: ' . $redirect, true, 302);
}

function redirectToDashboard(): void
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $redirect = str_contains($scriptName, '/admin/') ? '../dashboard.php' : 'dashboard.php';

    header('Location: ' . $redirect, true, 302);
}

function requireAuth(): int
{
    startSecureSession();

    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

    if ($userId === false || $userId < 1) {
        redirectToLogin();
        exit;
    }

    return $userId;
}

function requireRole(array $user, string ...$roles): void
{
    if (!in_array((string) ($user['role'] ?? ''), $roles, true)) {
        redirectToDashboard();
        exit;
    }
}

function redirectIfAuthenticated(): void
{
    startSecureSession();

    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

    if ($userId !== false && $userId > 0) {
        redirectToDashboard();
        exit;
    }
}