<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Room.php';
require_once BASE_PATH . '/app/controllers/RoomController.php';
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

$roomModel = new Room(database());
$controller = new RoomController($roomModel);
$rooms = $controller->index();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'La session du formulaire a expire. Veuillez reessayer.';
    } else {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            $errors['form'] = 'Identifiant invalide.';
        } else {
            $deleted = $controller->delete($id);
            $success = $deleted;
            if ($deleted) {
                header('Location: rooms.php?success=1');
                exit;
            }
            $errors['form'] = 'Impossible de supprimer cette salle.';
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] === '1') {
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salles - UniSoutenance</title>
    <link rel="stylesheet" href="../css/output.css">
</head>
<body class="app-background min-h-screen text-slate-800">
    <div class="min-h-screen lg:flex">
        <aside class="app-sidebar w-full bg-slate-900 text-slate-100 lg:min-h-screen lg:w-72">
            <div class="border-b border-slate-700 p-6">
                <h1 class="text-2xl font-bold text-white">UniSoutenance</h1>
            </div>
            <nav class="flex flex-wrap gap-2 p-4 text-sm lg:block lg:space-y-2 lg:p-5">
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="dashboard.php">Dashboard</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="filieres.php">Filières</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="teachers.php">Enseignants</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="students.php">Étudiants</a>
                <a class="block rounded-lg bg-slate-800 px-3 py-2 font-semibold" href="rooms.php">Salles</a>
                <a class="block rounded-lg px-3 py-2 hover:bg-slate-800" href="../calendar.php">Calendrier</a>
                <form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="block w-full rounded-lg px-3 py-2 text-left hover:bg-slate-800" type="submit">Déconnexion</button></form>
            </nav>
        </aside>
        <main class="min-w-0 flex-1 p-5 sm:p-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Administration</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900">Gestion des salles</h2>
                </div>
                <a href="rooms-form.php" class="rounded-lg bg-slate-900 px-4 py-2 font-semibold text-white hover:bg-slate-700">Nouvelle salle</a>
            </div>

            <?php if ($success): ?>
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">Opération réussie.</div>
            <?php endif; ?>
            <?php if (isset($errors['form'])): ?>
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><?= e($errors['form']) ?></div>
            <?php endif; ?>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Nom</th>
                            <th class="px-5 py-3 font-semibold">Bâtiment</th>
                            <th class="px-5 py-3 font-semibold">Capacité</th>
                            <th class="px-5 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if ($rooms === []): ?>
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Aucune salle enregistrée.</td></tr>
                        <?php else: ?>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-800"><?= e((string) ($room['nom'] ?? '')) ?></td>
                                    <td class="px-5 py-4"><?= e((string) ($room['batiment'] ?? '')) ?></td>
                                    <td class="px-5 py-4"><?= e((string) ($room['capacite'] ?? '')) ?></td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-slate-400" href="rooms-form.php?id=<?= (int) ($room['id'] ?? 0) ?>">Modifier</a>
                                            <form method="post" action="rooms.php" onsubmit="return confirm('Supprimer cette salle ?');">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) ($room['id'] ?? 0) ?>">
                                                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
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
