<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/DefenseJury.php';
require_once BASE_PATH . '/app/controllers/DefenseJuryController.php';
require_once BASE_PATH . '/app/middleware/authMiddleware.php';
require_once BASE_PATH . '/helpers/security.php';

startSecureSession();
$userId = requireAuth();
$currentUser = (new User(database()))->findById($userId);

if ($currentUser === null || ($currentUser['role'] ?? 'etudiant') !== 'admin') {
    header('Location: ../dashboard.php', true, 302);
    exit;
}

$controller = new DefenseJuryController(new DefenseJury(database()));
$juryMembers = $controller->index();
$errors = [];
$success = isset($_GET['success']) && $_GET['success'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'La session du formulaire a expire. Veuillez reessayer.';
    } else {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1 || !$controller->delete($id)) {
            $errors['form'] = 'Impossible de supprimer ce membre du jury.';
        } else {
            header('Location: jury.php?success=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurys - UniSoutenance</title>
    <link rel="stylesheet" href="../css/output.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="flex min-h-screen">
        <aside class="w-72 bg-slate-900 text-slate-100">
            <div class="border-b border-slate-700 p-6"><h1 class="text-2xl font-bold text-white">UniSoutenance</h1></div>
            <nav class="space-y-2 p-5 text-sm">
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="dashboard.php">Dashboard</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="filieres.php">Filières</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="teachers.php">Enseignants</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="students.php">Étudiants</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="rooms.php">Salles</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="defenses.php">Soutenances</a>
                <a class="block rounded-lg bg-slate-800 px-3 py-2 font-semibold" href="jury.php">Jurys</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="../logout.php">Déconnexion</a>
            </nav>
        </aside>
        <main class="flex-1 p-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Administration</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900">Composition des jurys</h2>
                </div>
                <a href="jury-form.php" class="rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-700">Ajouter un membre</a>
            </div>

            <?php if ($success): ?><div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">Membre du jury supprimé.</div><?php endif; ?>
            <?php if (isset($errors['form'])): ?><div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= e($errors['form']) ?></div><?php endif; ?>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600"><tr><th class="px-5 py-3 font-semibold">Soutenance</th><th class="px-5 py-3 font-semibold">Enseignant</th><th class="px-5 py-3 font-semibold">Rôle</th><th class="px-5 py-3 text-right font-semibold">Action</th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if ($juryMembers === []): ?>
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Aucun membre de jury enregistré.</td></tr>
                        <?php else: ?>
                            <?php foreach ($juryMembers as $member): ?>
                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-800"><?= e((string) ($member['titre'] ?? '')) ?></td>
                                    <td class="px-5 py-4"><?= e((string) ($member['prenom'] ?? '')) ?> <?= e((string) ($member['nom'] ?? '')) ?></td>
                                    <td class="px-5 py-4"><?= e((string) ($member['role_in_jury'] ?? '')) ?></td>
                                    <td class="px-5 py-4 text-right"><form method="post" action="jury.php" onsubmit="return confirm('Supprimer ce membre du jury ?');"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) ($member['id'] ?? 0) ?>"><button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100">Supprimer</button></form></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
