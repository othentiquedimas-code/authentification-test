<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Filiere.php';
require_once BASE_PATH . '/app/controllers/FiliereController.php';
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

$filiereModel = new Filiere(database());
$controller = new FiliereController($filiereModel);
$errors = [];
$old = ['nom' => '', 'code' => '', 'description' => ''];
$mode = 'create';
$editingId = null;

if (isset($_GET['id'])) {
    $editingId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($editingId !== false && $editingId > 0) {
        $existing = $filiereModel->findById($editingId);
        if ($existing !== null) {
            $mode = 'edit';
            $old = [
                'nom' => (string) ($existing['nom'] ?? ''),
                'code' => (string) ($existing['code'] ?? ''),
                'description' => (string) ($existing['description'] ?? ''),
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'La session du formulaire a expire. Veuillez reessayer.';
    } else {
        $payload = [
            'nom' => $_POST['nom'] ?? '',
            'code' => $_POST['code'] ?? '',
            'description' => $_POST['description'] ?? '',
        ];

        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = (int) $_POST['id'];
            $result = $controller->update($id, $payload);
            $errors = $result['errors'];
            $old = $result['old'];
            if (($result['success'] ?? false) === true) {
                header('Location: filieres.php?success=1');
                exit;
            }
        } else {
            $result = $controller->store($payload);
            $errors = $result['errors'];
            $old = $result['old'];
            if ($result['success'] ?? false) {
                header('Location: filieres.php?success=1');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'edit' ? 'Modifier' : 'Créer' ?> une filière - UniSoutenance</title>
    <link rel="stylesheet" href="../css/output.css">
</head>
<body class="app-background min-h-screen text-slate-800">
    <div class="mx-auto max-w-3xl p-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Administration</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-900"><?= $mode === 'edit' ? 'Modifier la filière' : 'Nouvelle filière' ?></h2>
            </div>
            <a href="filieres.php" class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 hover:border-slate-400">Retour</a>
        </div>

        <?php if (isset($errors['form'])): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= e($errors['form']) ?></div>
        <?php endif; ?>

        <form method="post" action="filieres-form.php" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <?php if ($mode === 'edit' && $editingId !== null): ?>
                <input type="hidden" name="id" value="<?= (int) $editingId ?>">
            <?php endif; ?>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="nom" class="mb-2 block text-sm font-medium text-slate-700">Nom</label>
                    <input id="nom" name="nom" type="text" value="<?= e($old['nom']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['nom'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['nom']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="code" class="mb-2 block text-sm font-medium text-slate-700">Code</label>
                    <input id="code" name="code" type="text" value="<?= e($old['code']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 uppercase focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['code'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['code']) ?></p><?php endif; ?>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"><?= e($old['description']) ?></textarea>
                    <?php if (isset($errors['description'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['description']) ?></p><?php endif; ?>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-3 font-semibold text-white hover:bg-slate-700">
                    <?= $mode === 'edit' ? 'Enregistrer' : 'Créer' ?>
                </button>
            </div>
        </form>
    </div>
</body>
</html>
