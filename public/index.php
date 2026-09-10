<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/app/middleware/authMiddleware.php';

startSecureSession();

if (isset($_SESSION['user_id'])) {
	header('Location: dashboard.php', true, 302);
	exit;
}

header('Location: login.php', true, 302);
exit;
