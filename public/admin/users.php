<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
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

$userModel = new User(database());
$errors = [];
$success = false;
$allowedStatuses = ['en_attente', 'actif', 'rejete'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'La session du formulaire a expire. Veuillez reessayer.';
    } else {
        $targetId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $status = (string) ($_POST['account_status'] ?? '');
        if ($targetId === false || $targetId < 1 || !in_array($status, $allowedStatuses, true)) {
            $errors['form'] = 'Paramètres de validation invalides.';
        } elseif ($targetId === $userId && $status !== 'actif') {
            $errors['form'] = 'Le compte administrateur connecté ne peut pas être désactivé ici.';
        } elseif (!$userModel->updateStatus($targetId, $status)) {
            $errors['form'] = 'Impossible de mettre à jour ce compte.';
        } else {
            header('Location: users.php?success=1');
            exit;
        }
    }
}

$users = $userModel->all();
$success = isset($_GET['success']) && $_GET['success'] === '1';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptes inscrits - UniSoutenance</title>
    <link rel="stylesheet" href="../css/output.css">
</head>
<body class="app-background min-h-screen text-slate-800">
    <div class="min-h-screen lg:flex">
        <aside class="app-sidebar w-full bg-slate-900 text-slate-100 lg:min-h-screen lg:w-72">
            <div class="border-b border-slate-700 p-6"><p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-300">Administration</p><h1 class="mt-2 text-2xl font-bold text-white">UniSoutenance</h1></div>
            <nav class="flex flex-wrap gap-2 p-4 text-sm lg:block lg:space-y-2 lg:p-5">
                <a class="app-sidebar-link block rounded-xl px-3 py-2.5" href="dashboard.php">Dashboard</a>
                <a class="app-sidebar-link is-active block rounded-xl px-3 py-2.5" href="users.php">Comptes inscrits</a>
                <a class="app-sidebar-link block rounded-xl px-3 py-2.5" href="filieres.php">Filières</a>
                <a class="app-sidebar-link block rounded-xl px-3 py-2.5" href="teachers.php">Enseignants</a>
                <a class="app-sidebar-link block rounded-xl px-3 py-2.5" href="students.php">Étudiants</a>
                <a class="app-sidebar-link block rounded-xl px-3 py-2.5" href="../calendar.php">Calendrier</a>
                <form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="app-sidebar-link block w-full rounded-xl px-3 py-2.5 text-left" type="submit">Déconnexion</button></form>
            </nav>
        </aside>
        <main class="min-w-0 flex-1 p-5 sm:p-8 lg:p-10">
            <div class="mb-8"><p class="app-kicker">Validation</p><h2 class="mt-2 font-display text-3xl font-bold text-slate-900">Comptes inscrits</h2><p class="mt-2 text-slate-500">Validez une inscription avant que l’utilisateur accède à son espace.</p></div>
            <?php if ($success): ?><div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">Le statut du compte a été mis à jour.</div><?php endif; ?>
            <?php if (isset($errors['form'])): ?><div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= e($errors['form']) ?></div><?php endif; ?>
            <section class="app-surface overflow-hidden rounded-2xl"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="app-table-head"><tr><th class="px-6 py-3 font-semibold">Nom</th><th class="px-6 py-3 font-semibold">Email</th><th class="px-6 py-3 font-semibold">Rôle</th><th class="px-6 py-3 font-semibold">Statut</th><th class="px-6 py-3 text-right font-semibold">Action</th></tr></thead><tbody class="divide-y divide-slate-200">
                <?php foreach ($users as $account): ?><tr><td class="px-6 py-4 font-medium"><?= e((string) $account['name']) ?></td><td class="px-6 py-4"><?= e((string) $account['email']) ?></td><td class="px-6 py-4"><?= e((string) $account['role']) ?></td><td class="px-6 py-4"><?= e((string) $account['account_status']) ?></td><td class="px-6 py-4 text-right"><div class="flex justify-end gap-2"><form method="post" action="users.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $account['id'] ?>"><input type="hidden" name="account_status" value="actif"><button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700" type="submit">Valider</button></form><form method="post" action="users.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $account['id'] ?>"><input type="hidden" name="account_status" value="rejete"><button class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100" type="submit">Rejeter</button></form></div></td></tr><?php endforeach; ?>
            </tbody></table></div></section>
        </main>
    </div>
</body>
</html>
