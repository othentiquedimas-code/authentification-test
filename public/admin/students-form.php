<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Filiere.php';
require_once BASE_PATH . '/app/models/Student.php';
require_once BASE_PATH . '/app/controllers/StudentController.php';
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
$filiers = $filiereModel->all();
$studentModel = new Student(database());
$controller = new StudentController($studentModel);
$errors = [];
$old = ['nom' => '', 'prenom' => '', 'matricule' => '', 'email' => '', 'telephone' => '', 'filiere_id' => '', 'niveau' => '', 'annee_academique' => ''];
$mode = 'create';
$editingId = null;

if (isset($_GET['id'])) {
    $editingId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($editingId !== false && $editingId > 0) {
        $existing = $studentModel->findById($editingId);
        if ($existing !== null) {
            $mode = 'edit';
            $old = [
                'nom' => (string) ($existing['nom'] ?? ''),
                'prenom' => (string) ($existing['prenom'] ?? ''),
                'matricule' => (string) ($existing['matricule'] ?? ''),
                'email' => (string) ($existing['email'] ?? ''),
                'telephone' => (string) ($existing['telephone'] ?? ''),
                'filiere_id' => (string) ($existing['filiere_id'] ?? ''),
                'niveau' => (string) ($existing['niveau'] ?? ''),
                'annee_academique' => (string) ($existing['annee_academique'] ?? ''),
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
            'prenom' => $_POST['prenom'] ?? '',
            'matricule' => $_POST['matricule'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'filiere_id' => $_POST['filiere_id'] ?? 0,
            'niveau' => $_POST['niveau'] ?? '',
            'annee_academique' => $_POST['annee_academique'] ?? '',
        ];

        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $result = $controller->update((int) $_POST['id'], $payload);
            $errors = $result['errors'];
            $old = $result['old'];
            if (($result['success'] ?? false) === true) {
                header('Location: students.php?success=1');
                exit;
            }
        } else {
            $result = $controller->store($payload);
            $errors = $result['errors'];
            $old = $result['old'];
            if (($result['success'] ?? false) === true) {
                header('Location: students.php?success=1');
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
    <title><?= $mode === 'edit' ? 'Modifier' : 'Créer' ?> un étudiant - UniSoutenance</title>
    <link rel="stylesheet" href="../css/output.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="mx-auto max-w-4xl p-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Administration</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-900"><?= $mode === 'edit' ? 'Modifier l\'étudiant' : 'Nouvel étudiant' ?></h2>
            </div>
            <a href="students.php" class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 hover:border-slate-400">Retour</a>
        </div>

        <?php if (isset($errors['form'])): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= e($errors['form']) ?></div>
        <?php endif; ?>

        <form method="post" action="students-form.php" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <?php if ($mode === 'edit' && $editingId !== null): ?>
                <input type="hidden" name="id" value="<?= (int) $editingId ?>">
            <?php endif; ?>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="nom" class="mb-2 block text-sm font-medium text-slate-700">Nom</label>
                    <input id="nom" name="nom" type="text" value="<?= e($old['nom']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['nom'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['nom']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="prenom" class="mb-2 block text-sm font-medium text-slate-700">Prénom</label>
                    <input id="prenom" name="prenom" type="text" value="<?= e($old['prenom']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['prenom'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['prenom']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="matricule" class="mb-2 block text-sm font-medium text-slate-700">Matricule</label>
                    <input id="matricule" name="matricule" type="text" value="<?= e($old['matricule']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 uppercase focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['matricule'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['matricule']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="<?= e($old['email']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['email'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['email']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="telephone" class="mb-2 block text-sm font-medium text-slate-700">Téléphone</label>
                    <input id="telephone" name="telephone" type="text" value="<?= e($old['telephone']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none">
                    <?php if (isset($errors['telephone'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['telephone']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="filiere_id" class="mb-2 block text-sm font-medium text-slate-700">Filière</label>
                    <select id="filiere_id" name="filiere_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                        <option value="">Choisir une filière</option>
                        <?php foreach ($filiers as $filiere): ?>
                            <option value="<?= (int) ($filiere['id'] ?? 0) ?>" <?= ((string) ($old['filiere_id'] ?? '') === (string) ($filiere['id'] ?? '')) ? 'selected' : '' ?>><?= e((string) ($filiere['nom'] ?? '')) ?> (<?= e((string) ($filiere['code'] ?? '')) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['filiere_id'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['filiere_id']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="niveau" class="mb-2 block text-sm font-medium text-slate-700">Niveau</label>
                    <input id="niveau" name="niveau" type="text" value="<?= e($old['niveau']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['niveau'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['niveau']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="annee_academique" class="mb-2 block text-sm font-medium text-slate-700">Année académique</label>
                    <input id="annee_academique" name="annee_academique" type="text" value="<?= e($old['annee_academique']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['annee_academique'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['annee_academique']) ?></p><?php endif; ?>
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
