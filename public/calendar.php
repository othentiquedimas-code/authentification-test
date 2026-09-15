<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/Calendar.php';
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

$allowedStatuses = ['planifiee', 'confirmee', 'terminee', 'annulee'];
$date = trim((string) ($_GET['date'] ?? ''));
$filiereId = filter_var($_GET['filiere_id'] ?? 0, FILTER_VALIDATE_INT);
$roomId = filter_var($_GET['room_id'] ?? 0, FILTER_VALIDATE_INT);
$statut = trim((string) ($_GET['statut'] ?? ''));

if ($date !== '' && DateTimeImmutable::createFromFormat('Y-m-d', $date) === false) {
    $date = '';
}
if ($filiereId === false || $filiereId < 1) {
    $filiereId = 0;
}
if ($roomId === false || $roomId < 1) {
    $roomId = 0;
}
if (!in_array($statut, $allowedStatuses, true)) {
    $statut = '';
}

$calendar = new Calendar(database());
$filieres = $calendar->filieres();
$rooms = $calendar->rooms();
$defenses = $calendar->defenses([
    'role' => (string) ($user['role'] ?? 'etudiant'),
    'user_id' => $userId,
    'email' => (string) ($user['email'] ?? ''),
    'date' => $date,
    'filiere_id' => $filiereId,
    'room_id' => $roomId,
    'statut' => $statut,
]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier - UniSoutenance</title>
    <link rel="stylesheet" href="css/output.css">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a class="text-xl font-bold tracking-tight text-slate-900" href="dashboard.php">UniSoutenance</a>
            <div class="flex items-center gap-4"><a class="text-sm font-semibold text-slate-600 hover:text-slate-900" href="dashboard.php">Dashboard</a><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100" type="submit">Se déconnecter</button></form></div>
        </nav>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-10">
        <div class="mb-8"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Planning</p><h1 class="mt-2 text-3xl font-bold text-slate-900">Calendrier des soutenances</h1><p class="mt-2 text-slate-500">Retrouvez les soutenances selon la date, la filière, la salle ou le statut.</p></div>

        <form method="get" action="calendar.php" class="mb-8 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
            <div><label for="date" class="mb-2 block text-sm font-medium text-slate-700">Date</label><input id="date" name="date" type="date" value="<?= e($date) ?>" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-slate-500 focus:outline-none"></div>
            <div><label for="filiere_id" class="mb-2 block text-sm font-medium text-slate-700">Filière</label><select id="filiere_id" name="filiere_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-slate-500 focus:outline-none"><option value="0">Toutes</option><?php foreach ($filieres as $filiere): ?><option value="<?= (int) $filiere['id'] ?>" <?= $filiereId === (int) $filiere['id'] ? 'selected' : '' ?>><?= e((string) $filiere['nom']) ?> (<?= e((string) $filiere['code']) ?>)</option><?php endforeach; ?></select></div>
            <div><label for="room_id" class="mb-2 block text-sm font-medium text-slate-700">Salle</label><select id="room_id" name="room_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-slate-500 focus:outline-none"><option value="0">Toutes</option><?php foreach ($rooms as $room): ?><option value="<?= (int) $room['id'] ?>" <?= $roomId === (int) $room['id'] ? 'selected' : '' ?>><?= e((string) $room['nom']) ?> - <?= e((string) $room['batiment']) ?></option><?php endforeach; ?></select></div>
            <div><label for="statut" class="mb-2 block text-sm font-medium text-slate-700">Statut</label><select id="statut" name="statut" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 focus:border-slate-500 focus:outline-none"><option value="">Tous</option><?php foreach ($allowedStatuses as $status): ?><option value="<?= e($status) ?>" <?= $statut === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></div>
            <div class="flex items-end gap-2"><button type="submit" class="flex-1 rounded-lg bg-slate-900 px-4 py-2.5 font-semibold text-white hover:bg-slate-700">Filtrer</button><a href="calendar.php" class="rounded-lg border border-slate-300 px-4 py-2.5 font-semibold text-slate-700 hover:border-slate-400">Réinitialiser</a></div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900"><?= count($defenses) ?> soutenance<?= count($defenses) > 1 ? 's' : '' ?></h2></div><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm"><thead class="bg-slate-50 text-slate-600"><tr><th class="px-6 py-3 font-semibold">Date</th><th class="px-6 py-3 font-semibold">Étudiant</th><th class="px-6 py-3 font-semibold">Sujet</th><th class="px-6 py-3 font-semibold">Filière</th><th class="px-6 py-3 font-semibold">Salle</th><th class="px-6 py-3 font-semibold">Encadreur</th><th class="px-6 py-3 font-semibold">Statut</th></tr></thead><tbody class="divide-y divide-slate-200">
            <?php if ($defenses === []): ?><tr><td colspan="7" class="px-6 py-10 text-center text-slate-500">Aucune soutenance ne correspond aux filtres.</td></tr><?php else: foreach ($defenses as $defense): ?><tr><td class="whitespace-nowrap px-6 py-4 font-medium"><?= e((string) $defense['defense_date']) ?><br><span class="text-slate-500"><?= e((string) $defense['defense_time']) ?></span></td><td class="px-6 py-4"><?= e((string) $defense['student_prenom']) ?> <?= e((string) $defense['student_nom']) ?><br><span class="text-xs text-slate-500"><?= e((string) $defense['matricule']) ?></span></td><td class="px-6 py-4"><?= e((string) $defense['titre']) ?></td><td class="px-6 py-4"><?= e((string) $defense['filiere_nom']) ?></td><td class="px-6 py-4"><?= e((string) $defense['room_nom']) ?><br><span class="text-xs text-slate-500"><?= e((string) $defense['batiment']) ?></span></td><td class="px-6 py-4"><?= e((string) $defense['teacher_prenom']) ?> <?= e((string) $defense['teacher_nom']) ?></td><td class="px-6 py-4"><?= e((string) $defense['statut']) ?></td></tr><?php endforeach; endif; ?>
        </tbody></table></div></section>
    </main>
</body>
</html>
