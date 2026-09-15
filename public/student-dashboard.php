<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/StudentDashboard.php';
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

if (($user['role'] ?? 'etudiant') !== 'etudiant') {
    header('Location: dashboard.php', true, 302);
    exit;
}

$studentDashboard = new StudentDashboard(database());
$student = $studentDashboard->findStudent($userId, (string) ($user['email'] ?? ''));
$defense = $student !== null ? $studentDashboard->defense((int) $student['id']) : null;
$jury = $defense !== null ? $studentDashboard->jury((int) $defense['id']) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace étudiant - UniSoutenance</title>
    <link rel="stylesheet" href="css/output.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a class="text-xl font-bold tracking-tight text-slate-900" href="student-dashboard.php">UniSoutenance</a>
            <div class="flex items-center gap-4">
                <span class="hidden text-sm text-slate-500 sm:inline"><?= e((string) ($user['email'] ?? '')) ?></span>
                <form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100" type="submit">Se déconnecter</button></form>
            </div>
        </nav>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-10">
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Espace étudiant</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Bonjour, <?= e((string) ($user['name'] ?? 'Étudiant')) ?></h1>
        </div>

        <?php if ($student === null): ?>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-800"><h2 class="font-bold">Profil étudiant introuvable</h2><p class="mt-2 text-sm">Votre compte est bien associé au rôle étudiant, mais aucune fiche ne correspond à votre email. Demandez à un administrateur de créer ou corriger votre fiche étudiant.</p></div>
        <?php else: ?>
            <section class="grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-brand-500">Mon profil académique</p>
                    <h2 class="mt-5 text-2xl font-bold text-slate-900"><?= e((string) $student['prenom']) ?> <?= e((string) $student['nom']) ?></h2>
                    <dl class="mt-6 space-y-4 text-sm"><div><dt class="text-slate-500">Matricule</dt><dd class="mt-1 font-semibold"><?= e((string) $student['matricule']) ?></dd></div><div><dt class="text-slate-500">Filière</dt><dd class="mt-1 font-semibold"><?= e((string) $student['filiere_nom']) ?> (<?= e((string) $student['filiere_code']) ?>)</dd></div><div><dt class="text-slate-500">Niveau</dt><dd class="mt-1 font-semibold"><?= e((string) $student['niveau']) ?></dd></div><div><dt class="text-slate-500">Année académique</dt><dd class="mt-1 font-semibold"><?= e((string) $student['annee_academique']) ?></dd></div></dl>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between"><div><p class="text-sm font-semibold uppercase tracking-[0.16em] text-brand-500">Ma soutenance</p><h2 class="mt-2 text-2xl font-bold text-slate-900"><?= $defense !== null ? e((string) $defense['titre']) : 'Pas encore planifiée' ?></h2></div><?php if ($defense !== null): ?><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= e((string) $defense['statut']) ?></span><?php endif; ?></div>
                    <?php if ($defense === null): ?><p class="mt-6 text-slate-500">Votre soutenance n’a pas encore été enregistrée.</p><?php else: ?><div class="mt-6 grid gap-5 sm:grid-cols-2 text-sm"><div><p class="text-slate-500">Date et heure</p><p class="mt-1 font-semibold"><?= e((string) $defense['defense_date']) ?> à <?= e((string) $defense['defense_time']) ?></p></div><div><p class="text-slate-500">Salle</p><p class="mt-1 font-semibold"><?= e((string) $defense['room_nom']) ?>, <?= e((string) $defense['batiment']) ?></p></div><div><p class="text-slate-500">Encadreur</p><p class="mt-1 font-semibold"><?= e((string) $defense['teacher_prenom']) ?> <?= e((string) $defense['teacher_nom']) ?></p></div><div><p class="text-slate-500">Email encadreur</p><p class="mt-1 font-semibold"><?= e((string) $defense['teacher_email']) ?></p></div></div><?php if ((string) ($defense['description'] ?? '') !== ''): ?><p class="mt-6 border-t border-slate-200 pt-5 text-sm leading-6 text-slate-600"><?= e((string) $defense['description']) ?></p><?php endif; ?><?php endif; ?>
                </div>
            </section>

            <?php if ($defense !== null): ?><section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900">Mon jury</h2></div><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="bg-slate-50 text-slate-600"><tr><th class="px-6 py-3 font-semibold">Enseignant</th><th class="px-6 py-3 font-semibold">Spécialité</th><th class="px-6 py-3 font-semibold">Rôle</th></tr></thead><tbody class="divide-y divide-slate-200"><?php if ($jury === []): ?><tr><td colspan="3" class="px-6 py-10 text-center text-slate-500">Le jury n’est pas encore composé.</td></tr><?php else: foreach ($jury as $member): ?><tr><td class="px-6 py-4 font-medium"><?= e((string) $member['prenom']) ?> <?= e((string) $member['nom']) ?></td><td class="px-6 py-4"><?= e((string) $member['specialite']) ?></td><td class="px-6 py-4"><?= e((string) $member['role_in_jury']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section><?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>
