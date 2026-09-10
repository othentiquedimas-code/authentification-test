<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/helpers/security.php';

startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(405);
    exit('Methode non autorisee.');
}

$_SESSION = [];

$cookieParameters = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 42000,
    'path' => $cookieParameters['path'],
    'domain' => $cookieParameters['domain'],
    'secure' => $cookieParameters['secure'],
    'httponly' => $cookieParameters['httponly'],
    'samesite' => $cookieParameters['samesite'] ?? 'Lax',
]);

session_destroy();

header('Location: login.php', true, 302);
exit;