<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/DashboardStats.php';
require_once BASE_PATH . '/app/middleware/authMiddleware.php';
require_once BASE_PATH . '/helpers/security.php';

startSecureSession();
$userId = requireAuth();
$currentUser = (new User(database()))->findById($userId);

if ($currentUser === null) {
    header('Location: ../dashboard.php', true, 302);
    exit;
}
requireRole($currentUser, 'admin');

$stats = new DashboardStats(database());
$counts = $stats->counts();
$upcomingDefenses = $stats->upcomingDefenses();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard admin - UniSoutenance</title>
    <link rel="stylesheet" href="../css/output.css">
</head>
<body class="app-background min-h-screen text-slate-800">
    <div class="min-h-screen lg:flex">
        <aside class="app-sidebar w-full shrink-0 text-slate-100 lg:min-h-screen lg:w-72">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-6"><div><p class="text-xs font-bold uppercase tracking-[0.24em] text-brand-300">Portail universitaire</p><h1 class="mt-2 font-display text-2xl font-bold text-white">UniSoutenance</h1></div><span class="rounded-full bg-brand-500 px-3 py-1 text-[0.65rem] font-bold uppercase tracking-wider text-white">Admin</span></div>
            <nav class="flex flex-wrap gap-2 p-4 text-sm lg:block lg:space-y-2 lg:p-5">
                <a class="app-sidebar-link is-active block whitespace-nowrap rounded-xl px-3 py-2.5 font-semibold" href="dashboard.php">Vue d'ensemble</a>
                <a class="app-sidebar-link block whitespace-nowrap rounded-xl px-3 py-2.5" href="filieres.php">Filières</a>
                <a class="app-sidebar-link block whitespace-nowrap rounded-xl px-3 py-2.5" href="teachers.php">Enseignants</a>
                <a class="app-sidebar-link block whitespace-nowrap rounded-xl px-3 py-2.5" href="students.php">Étudiants</a>
                <a class="app-sidebar-link block whitespace-nowrap rounded-xl px-3 py-2.5" href="rooms.php">Salles</a>
                <a class="app-sidebar-link block whitespace-nowrap rounded-xl px-3 py-2.5" href="defenses.php">Soutenances</a>
                <a class="app-sidebar-link block whitespace-nowrap rounded-xl px-3 py-2.5" href="jury.php">Jurys</a>
                <a class="app-sidebar-link block whitespace-nowrap rounded-xl px-3 py-2.5" href="../calendar.php">Calendrier</a>
                <form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="app-sidebar-link block w-full whitespace-nowrap rounded-xl px-3 py-2.5 text-left" type="submit">Déconnexion</button></form>
            </nav>
        </aside>
        <main class="min-w-0 flex-1 p-5 sm:p-8 lg:p-10">
            <div class="mb-8 overflow-hidden rounded-[1.75rem] bg-ink-950 p-7 text-white shadow-2xl shadow-slate-900/10 sm:p-9">
                <div class="flex flex-col justify-between gap-8 md:flex-row md:items-end"><div><p class="app-kicker text-brand-300">Tableau de pilotage</p><h2 class="mt-3 max-w-xl font-display text-3xl font-bold leading-tight sm:text-4xl">Bonjour, <?= e((string) ($currentUser['name'] ?? 'Administrateur')) ?>.</h2><p class="mt-3 max-w-lg text-sm leading-6 text-white/60">Une vue claire des ressources, des soutenances et du rythme de votre établissement.</p></div><a href="defenses-form.php" class="app-button-primary inline-flex w-fit items-center rounded-xl px-4 py-3 text-sm font-bold">+ Planifier une soutenance</a></div>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <?php foreach ([
                    ['label' => 'Filières', 'value' => $counts['filieres'] ?? 0, 'href' => 'filieres.php'],
                    ['label' => 'Étudiants', 'value' => $counts['students'] ?? 0, 'href' => 'students.php'],
                    ['label' => 'Enseignants', 'value' => $counts['teachers'] ?? 0, 'href' => 'teachers.php'],
                    ['label' => 'Salles', 'value' => $counts['rooms'] ?? 0, 'href' => 'rooms.php'],
                    ['label' => 'Soutenances', 'value' => $counts['defenses'] ?? 0, 'href' => 'defenses.php'],
                ] as $stat): ?>
                    <a href="<?= e($stat['href']) ?>" class="app-stat-card rounded-2xl p-5">
                        <span class="relative z-10 block text-2xl text-brand-500">●</span><p class="relative z-10 mt-4 text-sm font-semibold text-slate-500"><?= e($stat['label']) ?></p>
                        <p class="relative z-10 mt-2 font-display text-3xl font-bold text-slate-900"><?= (int) $stat['value'] ?></p>
                    </a>
                <?php endforeach; ?>
            </section>

            <section class="app-surface mt-8 overflow-hidden rounded-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                    <div><p class="app-kicker">Agenda</p><h3 class="mt-1 text-xl font-bold text-slate-900">Prochaines soutenances</h3><p class="mt-1 text-sm text-slate-500">Les cinq prochaines dates planifiées.</p></div>
                    <a href="defenses.php" class="rounded-lg bg-brand-50 px-3 py-2 text-sm font-bold text-brand-700 hover:bg-brand-100">Tout afficher</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="app-table-head"><tr><th class="px-6 py-3 font-semibold">Étudiant</th><th class="px-6 py-3 font-semibold">Sujet</th><th class="px-6 py-3 font-semibold">Date</th><th class="px-6 py-3 font-semibold">Salle</th><th class="px-6 py-3 font-semibold">Statut</th></tr></thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php if ($upcomingDefenses === []): ?>
                                <tr><td colspan="5" class="px-6 py-10 text-center text-slate-500">Aucune soutenance à venir.</td></tr>
                            <?php else: ?>
                                <?php foreach ($upcomingDefenses as $defense): ?>
                                    <tr>
                                        <td class="px-6 py-4 font-medium text-slate-800"><?= e((string) ($defense['student_prenom'] ?? '')) ?> <?= e((string) ($defense['student_nom'] ?? '')) ?></td>
                                        <td class="px-6 py-4"><?= e((string) ($defense['titre'] ?? '')) ?></td>
                                        <td class="px-6 py-4"><?= e((string) ($defense['defense_date'] ?? '')) ?> <?= e((string) ($defense['defense_time'] ?? '')) ?></td>
                                        <td class="px-6 py-4"><?= e((string) ($defense['room_nom'] ?? '')) ?>, <?= e((string) ($defense['batiment'] ?? '')) ?></td>
                                        <td class="px-6 py-4"><?= e((string) ($defense['statut'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
