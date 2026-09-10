<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/helpers/validation.php';
require_once BASE_PATH . '/helpers/security.php';

final class AuthController
{
    public function __construct(private readonly User $user)
    {
    }

    public function register(array $input): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');
        $passwordConfirmation = (string) ($input['password_confirmation'] ?? '');

        $errors = validateRegistration($name, $email, $password, $passwordConfirmation);

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('name', 'email')];
        }

        if ($this->user->findByEmail($email) !== null) {
            return [
                'errors' => ['email' => 'Cette adresse email est deja utilisee.'],
                'old' => compact('name', 'email'),
            ];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $this->user->create($name, $email, $passwordHash);

        return ['errors' => [], 'old' => [], 'success' => true];
    }

    public function login(array $input): array
    {
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return [
                'errors' => ['form' => 'Email ou mot de passe incorrect.'],
                'old' => ['email' => $email],
            ];
        }

        $user = $this->user->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return [
                'errors' => ['form' => 'Email ou mot de passe incorrect.'],
                'old' => ['email' => $email],
            ];
        }

        startSecureSession();
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        regenerateCsrfToken();

        return ['errors' => [], 'old' => [], 'success' => true];
    }
}