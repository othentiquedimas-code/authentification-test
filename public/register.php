<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/app/middleware/authMiddleware.php';
require_once BASE_PATH . '/helpers/security.php';

startSecureSession();
redirectIfAuthenticated();

$errors = [];
$old = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'La session du formulaire a expire. Veuillez reessayer.';
    } else {
        $controller = new AuthController(new User(database()));
        $result = $controller->register($_POST);
        $errors = $result['errors'];
        $old = $result['old'];
        $success = $result['success'] ?? false;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Auth Native PHP</title>
    <link rel="stylesheet" href="css/output.css">
</head>
<body class="min-h-screen bg-[#f5f2eb] px-6 py-10 text-[#172126]">
    <main class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-2xl items-center justify-center">
        <section class="w-full rounded-[2rem] bg-[#172126] p-8 shadow-2xl shadow-[#172126]/20 sm:p-12">
            <div class="flex items-center justify-between gap-4">
                <p class="text-sm font-bold uppercase tracking-[0.28em] text-[#e76f51]">Auth Native</p>
                <a class="text-sm font-semibold text-white/60 underline-offset-4 hover:text-[#e76f51] hover:underline" href="login.php">Connexion</a>
            </div>
            <h1 class="mt-12 font-display text-4xl font-bold text-[#f5f2eb]">Créer un compte</h1>
            <p class="mt-3 text-white/60">Commencez avec un espace personnel sécurisé.</p>

            <?php if ($success): ?>
                <p class="mt-8 rounded-xl bg-emerald-400/15 p-4 text-sm text-emerald-200" role="status">Votre compte a été créé. Vous pouvez maintenant vous connecter.</p>
                <p class="mt-6"><a class="font-semibold text-[#e76f51] underline-offset-4 hover:underline" href="login.php">Se connecter</a></p>
            <?php else: ?>
                <?php if (isset($errors['form'])): ?>
                    <p class="mt-6 rounded-xl bg-red-400/15 p-4 text-sm text-red-200" role="alert"><?= e($errors['form']) ?></p>
                <?php endif; ?>

            <form class="mt-8 grid gap-5" method="post" action="register.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#f5f2eb]" for="name">Nom</label>
                    <input class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-[#f5f2eb] outline-none transition focus:border-[#e76f51] focus:ring-2 focus:ring-[#e76f51]/30" id="name" name="name" type="text" value="<?= e($old['name'] ?? '') ?>" required maxlength="100" autocomplete="name">
                    <?php if (isset($errors['name'])): ?><p class="mt-2 text-sm text-red-300" role="alert"><?= e($errors['name']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#f5f2eb]" for="email">Email</label>
                    <input class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-[#f5f2eb] outline-none transition focus:border-[#e76f51] focus:ring-2 focus:ring-[#e76f51]/30" id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" required maxlength="255" autocomplete="email">
                    <?php if (isset($errors['email'])): ?><p class="mt-2 text-sm text-red-300" role="alert"><?= e($errors['email']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#f5f2eb]" for="password">Mot de passe</label>
                    <input class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-[#f5f2eb] outline-none transition focus:border-[#e76f51] focus:ring-2 focus:ring-[#e76f51]/30" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                    <?php if (isset($errors['password'])): ?><p class="mt-2 text-sm text-red-300" role="alert"><?= e($errors['password']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-[#f5f2eb]" for="password_confirmation">Confirmation du mot de passe</label>
                    <input class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-[#f5f2eb] outline-none transition focus:border-[#e76f51] focus:ring-2 focus:ring-[#e76f51]/30" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
                    <?php if (isset($errors['password_confirmation'])): ?><p class="mt-2 text-sm text-red-300" role="alert"><?= e($errors['password_confirmation']) ?></p><?php endif; ?>
                </div>

                <button class="mt-2 rounded-xl bg-[#e76f51] px-4 py-3 font-bold text-[#172126] transition hover:bg-[#f28b70] focus:outline-none focus:ring-2 focus:ring-[#e76f51] focus:ring-offset-2 focus:ring-offset-[#172126]" type="submit">Créer mon compte</button>
            </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>