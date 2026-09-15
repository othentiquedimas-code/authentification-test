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

if ($currentUser === null || ($currentUser['role'] ?? 'etudiant') !== 'admin') {
    header('Location: ../dashboard.php', true, 302);
    exit;
}

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
<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="flex min-h-screen">
        <aside class="w-72 bg-slate-900 text-slate-100">
            <div class="border-b border-slate-700 p-6"><h1 class="text-2xl font-bold text-white">UniSoutenance</h1></div>
            <nav class="space-y-2 p-5 text-sm">
                <a class="block rounded-lg bg-slate-800 px-3 py-2 font-semibold" href="dashboard.php">Dashboard</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="filieres.php">Filières</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="teachers.php">Enseignants</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="students.php">Étudiants</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="rooms.php">Salles</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="defenses.php">Soutenances</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="jury.php">Jurys</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="../calendar.php">Calendrier</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="../logout.php">Déconnexion</a>
            </nav>
        </aside>
        <main class="flex-1 p-8">
            <div class="mb-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Vue d'ensemble</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-900">Bonjour, <?= e((string) ($currentUser['name'] ?? 'Administrateur')) ?></h2>
                <p class="mt-2 text-slate-500">Suivez les ressources et les prochaines soutenances.</p>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <?php foreach ([
                    ['label' => 'Filières', 'value' => $counts['filieres'] ?? 0, 'href' => 'filieres.php'],
                    ['label' => 'Étudiants', 'value' => $counts['students'] ?? 0, 'href' => 'students.php'],
                    ['label' => 'Enseignants', 'value' => $counts['teachers'] ?? 0, 'href' => 'teachers.php'],
                    ['label' => 'Salles', 'value' => $counts['rooms'] ?? 0, 'href' => 'rooms.php'],
                    ['label' => 'Soutenances', 'value' => $counts['defenses'] ?? 0, 'href' => 'defenses.php'],
                ] as $stat): ?>
                    <a href="<?= e($stat['href']) ?>" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-400">
                        <p class="text-sm font-medium text-slate-500"><?= e($stat['label']) ?></p>
                        <p class="mt-3 text-3xl font-bold text-slate-900"><?= (int) $stat['value'] ?></p>
                    </a>
                <?php endforeach; ?>
            </section>

            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                    <div><h3 class="text-lg font-bold text-slate-900">Prochaines soutenances</h3><p class="mt-1 text-sm text-slate-500">Les cinq prochaines dates planifiées.</p></div>
                    <a href="defenses.php" class="text-sm font-semibold text-slate-700 hover:text-slate-900">Tout afficher</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600"><tr><th class="px-6 py-3 font-semibold">Étudiant</th><th class="px-6 py-3 font-semibold">Sujet</th><th class="px-6 py-3 font-semibold">Date</th><th class="px-6 py-3 font-semibold">Salle</th><th class="px-6 py-3 font-semibold">Statut</th></tr></thead>
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
