<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Student.php';
require_once BASE_PATH . '/app/models/Teacher.php';
require_once BASE_PATH . '/app/models/Room.php';
require_once BASE_PATH . '/app/models/Defense.php';
require_once BASE_PATH . '/app/controllers/DefenseController.php';
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

$studentModel = new Student(database());
$teacherModel = new Teacher(database());
$roomModel = new Room(database());
$defenseModel = new Defense(database());
$controller = new DefenseController($defenseModel);
$students = $studentModel->all();
$teachers = $teacherModel->all();
$rooms = $roomModel->all();

$errors = [];
$old = ['student_id' => '', 'titre' => '', 'description' => '', 'supervisor_teacher_id' => '', 'room_id' => '', 'defense_date' => '', 'defense_time' => '', 'statut' => 'planifiee', 'annee_academique' => ''];
$mode = 'create';
$editingId = null;

if (isset($_GET['id'])) {
    $editingId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($editingId !== false && $editingId > 0) {
        $existing = $defenseModel->findById($editingId);
        if ($existing !== null) {
            $mode = 'edit';
            $old = [
                'student_id' => (string) ($existing['student_id'] ?? ''),
                'titre' => (string) ($existing['titre'] ?? ''),
                'description' => (string) ($existing['description'] ?? ''),
                'supervisor_teacher_id' => (string) ($existing['supervisor_teacher_id'] ?? ''),
                'defense_date' => (string) ($existing['defense_date'] ?? ''),
                'defense_time' => (string) ($existing['defense_time'] ?? ''),
                'room_id' => (string) ($existing['room_id'] ?? ''),
                'statut' => (string) ($existing['statut'] ?? 'planifiee'),
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
            'student_id' => $_POST['student_id'] ?? '',
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'supervisor_teacher_id' => $_POST['supervisor_teacher_id'] ?? '',
            'defense_date' => $_POST['defense_date'] ?? '',
            'defense_time' => $_POST['defense_time'] ?? '',
            'room_id' => $_POST['room_id'] ?? '',
            'statut' => $_POST['statut'] ?? 'planifiee',
            'annee_academique' => $_POST['annee_academique'] ?? '',
        ];

        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $result = $controller->update((int) $_POST['id'], $payload);
            $errors = $result['errors'];
            $old = $result['old'];
            if (($result['success'] ?? false) === true) {
                header('Location: defenses.php?success=1');
                exit;
            }
        } else {
            $result = $controller->store($payload);
            $errors = $result['errors'];
            $old = $result['old'];
            if (($result['success'] ?? false) === true) {
                header('Location: defenses.php?success=1');
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
    <title><?= $mode === 'edit' ? 'Modifier' : 'Créer' ?> une soutenance - UniSoutenance</title>
    <link rel="stylesheet" href="../css/output.css">
</head>
<body class="app-background min-h-screen text-slate-800">
    <div class="mx-auto max-w-4xl p-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Administration</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-900"><?= $mode === 'edit' ? 'Modifier la soutenance' : 'Nouvelle soutenance' ?></h2>
            </div>
            <a href="defenses.php" class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 hover:border-slate-400">Retour</a>
        </div>

        <?php if (isset($errors['form'])): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= e($errors['form']) ?></div>
        <?php endif; ?>

        <form method="post" action="defenses-form.php" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <?php if ($mode === 'edit' && $editingId !== null): ?>
                <input type="hidden" name="id" value="<?= (int) $editingId ?>">
            <?php endif; ?>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="student_id" class="mb-2 block text-sm font-medium text-slate-700">Étudiant</label>
                    <select id="student_id" name="student_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                        <option value="">Sélectionner</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= (int) ($student['id'] ?? 0) ?>" <?= ((string) $old['student_id'] === (string) ($student['id'] ?? '')) ? 'selected' : '' ?>><?= e((string) ($student['prenom'] ?? '')) ?> <?= e((string) ($student['nom'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['student_id'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['student_id']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="titre" class="mb-2 block text-sm font-medium text-slate-700">Titre</label>
                    <input id="titre" name="titre" type="text" value="<?= e($old['titre']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['titre'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['titre']) ?></p><?php endif; ?>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                    <textarea id="description" name="description" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none"><?= e($old['description']) ?></textarea>
                    <?php if (isset($errors['description'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['description']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="supervisor_teacher_id" class="mb-2 block text-sm font-medium text-slate-700">Encadreur</label>
                    <select id="supervisor_teacher_id" name="supervisor_teacher_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                        <option value="">Sélectionner</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= (int) ($teacher['id'] ?? 0) ?>" <?= ((string) $old['supervisor_teacher_id'] === (string) ($teacher['id'] ?? '')) ? 'selected' : '' ?>><?= e((string) ($teacher['prenom'] ?? '')) ?> <?= e((string) ($teacher['nom'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['supervisor_teacher_id'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['supervisor_teacher_id']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="room_id" class="mb-2 block text-sm font-medium text-slate-700">Salle</label>
                    <select id="room_id" name="room_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                        <option value="">Sélectionner</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?= (int) ($room['id'] ?? 0) ?>" <?= ((string) $old['room_id'] === (string) ($room['id'] ?? '')) ? 'selected' : '' ?>><?= e((string) ($room['nom'] ?? '')) ?> - <?= e((string) ($room['batiment'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['room_id'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['room_id']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="defense_date" class="mb-2 block text-sm font-medium text-slate-700">Date</label>
                    <input id="defense_date" name="defense_date" type="date" value="<?= e($old['defense_date']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['defense_date'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['defense_date']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="defense_time" class="mb-2 block text-sm font-medium text-slate-700">Heure</label>
                    <input id="defense_time" name="defense_time" type="time" value="<?= e($old['defense_time']) ?>" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none" required>
                    <?php if (isset($errors['defense_time'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['defense_time']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label for="statut" class="mb-2 block text-sm font-medium text-slate-700">Statut</label>
                    <select id="statut" name="statut" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-slate-500 focus:outline-none">
                        <option value="planifiee" <?= ($old['statut'] === 'planifiee') ? 'selected' : '' ?>>Planifiée</option>
                        <option value="confirmee" <?= ($old['statut'] === 'confirmee') ? 'selected' : '' ?>>Confirmée</option>
                        <option value="terminee" <?= ($old['statut'] === 'terminee') ? 'selected' : '' ?>>Terminée</option>
                        <option value="annulee" <?= ($old['statut'] === 'annulee') ? 'selected' : '' ?>>Annulée</option>
                    </select>
                    <?php if (isset($errors['statut'])): ?><p class="mt-2 text-sm text-red-600"><?= e($errors['statut']) ?></p><?php endif; ?>
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
