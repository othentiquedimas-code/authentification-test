<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/TeacherDashboard.php';
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

if (($user['role'] ?? 'etudiant') !== 'enseignant') {
    header('Location: dashboard.php', true, 302);
    exit;
}

$teacherDashboard = new TeacherDashboard(database());
$teacher = $teacherDashboard->findTeacher($userId, (string) ($user['email'] ?? ''));
$counts = $teacher !== null ? $teacherDashboard->counts((int) $teacher['id']) : [];
$upcomingDefenses = $teacher !== null ? $teacherDashboard->upcomingDefenses((int) $teacher['id']) : [];
$juries = $teacher !== null ? $teacherDashboard->juries((int) $teacher['id']) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace enseignant - UniSoutenance</title>
    <link rel="stylesheet" href="css/output.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a class="text-xl font-bold tracking-tight text-slate-900" href="teacher-dashboard.php">UniSoutenance</a>
            <div class="flex items-center gap-4">
                <span class="hidden text-sm text-slate-500 sm:inline"><?= e((string) ($user['email'] ?? '')) ?></span>
                <form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100" type="submit">Se déconnecter</button></form>
            </div>
        </nav>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-10">
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Espace enseignant</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Bonjour, <?= e((string) ($user['name'] ?? 'Enseignant')) ?></h1>
            <?php if ($teacher !== null): ?><p class="mt-2 text-slate-500"><?= e((string) ($teacher['specialite'] ?? '')) ?></p><?php endif; ?>
        </div>

        <?php if ($teacher === null): ?>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-800"><h2 class="font-bold">Profil enseignant introuvable</h2><p class="mt-2 text-sm">Votre compte est bien associé au rôle enseignant, mais aucun profil ne correspond à votre email. Demandez à un administrateur de créer ou corriger votre fiche enseignant.</p></div>
        <?php else: ?>
            <section class="grid gap-4 sm:grid-cols-3">
                <?php foreach ([['label' => 'Soutenances encadrées', 'value' => $counts['supervised_defenses'] ?? 0], ['label' => 'Étudiants encadrés', 'value' => $counts['supervised_students'] ?? 0], ['label' => 'Participations aux jurys', 'value' => $counts['jury_assignments'] ?? 0]] as $stat): ?>
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><p class="text-sm text-slate-500"><?= e($stat['label']) ?></p><p class="mt-3 text-3xl font-bold text-slate-900"><?= (int) $stat['value'] ?></p></div>
                <?php endforeach; ?>
            </section>

            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900">Mes prochaines soutenances</h2></div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="bg-slate-50 text-slate-600"><tr><th class="px-6 py-3 font-semibold">Étudiant</th><th class="px-6 py-3 font-semibold">Sujet</th><th class="px-6 py-3 font-semibold">Date</th><th class="px-6 py-3 font-semibold">Salle</th><th class="px-6 py-3 font-semibold">Statut</th></tr></thead><tbody class="divide-y divide-slate-200">
                    <?php if ($upcomingDefenses === []): ?><tr><td colspan="5" class="px-6 py-10 text-center text-slate-500">Aucune soutenance à venir.</td></tr><?php else: foreach ($upcomingDefenses as $defense): ?><tr><td class="px-6 py-4 font-medium"><?= e((string) $defense['student_prenom']) ?> <?= e((string) $defense['student_nom']) ?></td><td class="px-6 py-4"><?= e((string) $defense['titre']) ?></td><td class="px-6 py-4"><?= e((string) $defense['defense_date']) ?> <?= e((string) $defense['defense_time']) ?></td><td class="px-6 py-4"><?= e((string) $defense['room_nom']) ?>, <?= e((string) $defense['batiment']) ?></td><td class="px-6 py-4"><?= e((string) $defense['statut']) ?></td></tr><?php endforeach; endif; ?>
                </tbody></table></div>
            </section>

            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900">Mes jurys</h2></div><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="bg-slate-50 text-slate-600"><tr><th class="px-6 py-3 font-semibold">Soutenance</th><th class="px-6 py-3 font-semibold">Étudiant</th><th class="px-6 py-3 font-semibold">Date</th><th class="px-6 py-3 font-semibold">Rôle</th></tr></thead><tbody class="divide-y divide-slate-200">
                <?php if ($juries === []): ?><tr><td colspan="4" class="px-6 py-10 text-center text-slate-500">Aucune participation à un jury.</td></tr><?php else: foreach ($juries as $jury): ?><tr><td class="px-6 py-4 font-medium"><?= e((string) $jury['titre']) ?></td><td class="px-6 py-4"><?= e((string) $jury['student_prenom']) ?> <?= e((string) $jury['student_nom']) ?></td><td class="px-6 py-4"><?= e((string) $jury['defense_date']) ?> <?= e((string) $jury['defense_time']) ?></td><td class="px-6 py-4"><?= e((string) $jury['role_in_jury']) ?></td></tr><?php endforeach; endif; ?>
            </tbody></table></div></section>
        <?php endif; ?>
    </main>
</body>
</html>
