<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Defense.php';
require_once BASE_PATH . '/app/models/Teacher.php';
require_once BASE_PATH . '/app/models/DefenseJury.php';
require_once BASE_PATH . '/app/controllers/DefenseJuryController.php';
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

$defenses = (new Defense(database()))->all();
$teachers = (new Teacher(database()))->all();
$controller = new DefenseJuryController(new DefenseJury(database()));
$errors = [];
$old = ['defense_id' => '', 'teacher_id' => '', 'role_in_jury' => 'president'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'La session du formulaire a expire. Veuillez reessayer.';
    } else {
        $result = $controller->store([
            'defense_id' => $_POST['defense_id'] ?? '',
            'teacher_id' => $_POST['teacher_id'] ?? '',
            'role_in_jury' => $_POST['role_in_jury'] ?? '',
        ]);
        $errors = $result['errors'];
        $old = [
            'defense_id' => (string) ($result['old']['defenseId'] ?? $_POST['defense_id'] ?? ''),
            'teacher_id' => (string) ($result['old']['teacherId'] ?? $_POST['teacher_id'] ?? ''),
            'role_in_jury' => (string) ($result['old']['roleInJury'] ?? $_POST['role_in_jury'] ?? ''),
        ];
        if (($result['success'] ?? false) === true) {
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
    <title>Ajouter un membre - UniSoutenance</title>
    <link rel="stylesheet" href="../css/output.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="mx-auto max-w-3xl p-8">
        <div class="mb-8 flex items-center justify-between">
            <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Administration</p><h2 class="mt-2 text-3xl font-bold text-slate-900">Ajouter un membre au jury</h2></div>
            <a href="jury.php" class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 hover:border-slate-400">Retour</a>
        </div>

        <?php if (isset($errors['form'])): ?><div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= e($errors['form']) ?></div><?php endif; ?>

        <form method="post" action="jury-form.php" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="grid gap-6">
                <div>
                    <label for="defense_id" class="mb-2 block text-sm font-medium text-slate-700">Soutenance</label>
                    <select id="defense_id" name="defense_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                        <option value="">Sélectionner une soutenance</option>
                        <?php foreach ($defenses as $defense): ?>
                            <option value="<?= (int) ($defense['id'] ?? 0) ?>" <?= $old['defense_id'] === (string) ($defense['id'] ?? '') ? 'selected' : '' ?>><?= e((string) ($defense['titre'] ?? '')) ?> - <?= e((string) ($defense['student_nom'] ?? '')) ?> <?= e((string) ($defense['student_prenom'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['defense_id'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['defense_id']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="teacher_id" class="mb-2 block text-sm font-medium text-slate-700">Enseignant</label>
                    <select id="teacher_id" name="teacher_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                        <option value="">Sélectionner un enseignant</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= (int) ($teacher['id'] ?? 0) ?>" <?= $old['teacher_id'] === (string) ($teacher['id'] ?? '') ? 'selected' : '' ?>><?= e((string) ($teacher['prenom'] ?? '')) ?> <?= e((string) ($teacher['nom'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['teacher_id'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['teacher_id']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="role_in_jury" class="mb-2 block text-sm font-medium text-slate-700">Rôle</label>
                    <select id="role_in_jury" name="role_in_jury" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                        <option value="president" <?= $old['role_in_jury'] === 'president' ? 'selected' : '' ?>>Président</option>
                        <option value="examinateur" <?= $old['role_in_jury'] === 'examinateur' ? 'selected' : '' ?>>Examinateur</option>
                        <option value="rapporteur" <?= $old['role_in_jury'] === 'rapporteur' ? 'selected' : '' ?>>Rapporteur</option>
                    </select>
                    <?php if (isset($errors['role_in_jury'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['role_in_jury']) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="mt-8 flex justify-end"><button type="submit" class="rounded-lg bg-slate-900 px-5 py-3 font-semibold text-white hover:bg-slate-700">Ajouter au jury</button></div>
        </form>
    </div>
</body>
</html>
