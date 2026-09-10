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
        $result = $controller->login($_POST);
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
    <title>Connexion - Auth Native PHP</title>
    <link rel="stylesheet" href="css/output.css">
</head>
<body class="min-h-screen bg-[#f5f2eb] px-6 py-10 text-[#172126]">
    <main class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-5xl items-center justify-center">
        <section class="grid w-full overflow-hidden rounded-[2rem] bg-[#172126] shadow-2xl shadow-[#172126]/20 md:grid-cols-[0.85fr_1.15fr]">
            <div class="flex flex-col justify-between bg-[#e76f51] p-8 text-[#172126] sm:p-12">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.28em]">Auth Native</p>
                    <h1 class="mt-16 max-w-xs font-display text-4xl font-bold leading-tight sm:text-5xl">Votre espace, sans détour.</h1>
                </div>
                <p class="mt-16 max-w-xs text-sm leading-6 text-[#172126]/75">Une authentification claire, construite en PHP natif et pensée pour apprendre.</p>
            </div>
            <div class="p-8 sm:p-12">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#e76f51]">Bienvenue</p>
                <h2 class="mt-3 font-display text-3xl font-bold text-[#f5f2eb]">Se connecter</h2>

                <?php if ($success): ?>
                    <p class="mt-8 rounded-xl bg-emerald-400/15 p-4 text-sm text-emerald-200" role="status">Connexion réussie.</p>
                    <p class="mt-6"><a class="font-semibold text-[#e76f51] underline-offset-4 hover:underline" href="dashboard.php">Accéder au tableau de bord</a></p>
                <?php else: ?>
                    <?php if (isset($errors['form'])): ?>
                        <p class="mt-6 rounded-xl bg-red-400/15 p-4 text-sm text-red-200" role="alert"><?= e($errors['form']) ?></p>
                    <?php endif; ?>

                    <form class="mt-8 space-y-5" method="post" action="login.php">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-[#f5f2eb]" for="email">Email</label>
                            <input class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-[#f5f2eb] outline-none transition placeholder:text-white/40 focus:border-[#e76f51] focus:ring-2 focus:ring-[#e76f51]/30" id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" required autocomplete="email">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-[#f5f2eb]" for="password">Mot de passe</label>
                            <input class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-[#f5f2eb] outline-none transition placeholder:text-white/40 focus:border-[#e76f51] focus:ring-2 focus:ring-[#e76f51]/30" id="password" name="password" type="password" required autocomplete="current-password">
                        </div>

                        <button class="w-full rounded-xl bg-[#e76f51] px-4 py-3 font-bold text-[#172126] transition hover:bg-[#f28b70] focus:outline-none focus:ring-2 focus:ring-[#e76f51] focus:ring-offset-2 focus:ring-offset-[#172126]" type="submit">Se connecter</button>
                    </form>

                    <p class="mt-8 text-sm text-white/60">Pas encore de compte ? <a class="font-semibold text-[#e76f51] underline-offset-4 hover:underline" href="register.php">Créer un compte</a></p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>