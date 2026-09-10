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
    <title>Dashboard - Auth Native PHP</title>
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
    <main class="mx-auto max-w-6xl px-6 py-12 sm:py-16">
        <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#e76f51]">Espace privé</p>
        <h1 class="mt-4 font-display text-4xl font-bold tracking-tight sm:text-5xl">Bonjour, <?= e($user['name']) ?>.</h1>
        <p class="mt-4 max-w-xl text-lg leading-8 text-[#172126]/60">Votre espace d’authentification est actif. Retrouvez vos informations et gérez votre session depuis ici.</p>

        <section class="mt-12 grid gap-5 md:grid-cols-[1.3fr_0.7fr]">
            <div class="rounded-[1.5rem] bg-[#172126] p-7 text-[#f5f2eb] shadow-xl shadow-[#172126]/10 sm:p-9">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#e76f51]">Compte connecté</p>
                <p class="mt-8 font-display text-2xl font-bold"><?= e($user['email']) ?></p>
                <p class="mt-2 text-sm text-white/55">Session sécurisée par PHP natif.</p>
            </div>
            <a class="group rounded-[1.5rem] border border-[#172126]/10 bg-white p-7 transition hover:-translate-y-1 hover:border-[#e76f51] sm:p-9" href="profile.php">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#e76f51]">Votre profil</p>
                <p class="mt-8 font-display text-2xl font-bold">Voir les détails <span class="inline-block transition group-hover:translate-x-1">→</span></p>
                <p class="mt-2 text-sm text-[#172126]/55">Nom, email et date d’inscription.</p>
            </a>
        </section>
    </main>
</body>
</html>