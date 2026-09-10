<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/middleware/authMiddleware.php';
require_once BASE_PATH . '/helpers/security.php';

$userId = requireAuth();
$user = (new User(database()))->findById($userId);

if ($user === null) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Auth Native PHP</title>
    <link rel="stylesheet" href="css/output.css">
</head>
<body class="min-h-screen bg-[#f5f2eb] text-[#172126]">
    <header class="border-b border-[#172126]/10 bg-white/70">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <a class="font-display text-xl font-bold tracking-tight" href="dashboard.php">Auth <span class="text-[#e76f51]">Native</span></a>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <button class="rounded-lg px-3 py-2 text-sm font-semibold text-[#172126]/65 transition hover:bg-[#172126]/5 hover:text-[#172126]" type="submit">Se déconnecter</button>
            </form>
        </nav>
    </header>
    <main class="mx-auto max-w-3xl px-6 py-12 sm:py-16">
        <a class="text-sm font-semibold text-[#e76f51] underline-offset-4 hover:underline" href="dashboard.php">← Retour au tableau de bord</a>
        <h1 class="mt-8 font-display text-4xl font-bold tracking-tight">Mon profil</h1>
        <p class="mt-3 text-[#172126]/60">Les informations de votre compte, au même endroit.</p>

        <dl class="mt-10 divide-y divide-[#172126]/10 rounded-[1.5rem] bg-white p-7 shadow-lg shadow-[#172126]/5 sm:p-9">
            <div class="flex flex-col gap-2 py-5 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                <dt class="text-sm font-semibold uppercase tracking-[0.16em] text-[#172126]/45">Nom</dt>
                <dd class="text-lg font-semibold"><?= e($user['name']) ?></dd>
            </div>

            <div class="flex flex-col gap-2 py-5 sm:flex-row sm:items-center sm:justify-between">
                <dt class="text-sm font-semibold uppercase tracking-[0.16em] text-[#172126]/45">Email</dt>
                <dd class="text-lg font-semibold"><?= e($user['email']) ?></dd>
            </div>

            <div class="flex flex-col gap-2 py-5 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                <dt class="text-sm font-semibold uppercase tracking-[0.16em] text-[#172126]/45">Membre depuis</dt>
                <dd class="text-lg font-semibold"><?= e($user['created_at']) ?></dd>
            </div>
        </dl>
    </main>
</body>
</html>