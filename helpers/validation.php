<?php

declare(strict_types=1);

function validateRegistration(string $name, string $email, string $password, string $passwordConfirmation): array
{
    $errors = [];

    if ($name === '') {
        $errors['name'] = 'Le nom est obligatoire.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Le nom ne peut pas depasser 100 caracteres.';
    }

    if ($email === '') {
        $errors['email'] = 'L\'email est obligatoire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'email n\'est pas valide.';
    } elseif (mb_strlen($email) > 255) {
        $errors['email'] = 'L\'email ne peut pas depasser 255 caracteres.';
    }

    if ($password === '') {
        $errors['password'] = 'Le mot de passe est obligatoire.';
    } elseif (mb_strlen($password) < 8) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caracteres.';
    }

    if ($passwordConfirmation === '') {
        $errors['password_confirmation'] = 'La confirmation du mot de passe est obligatoire.';
    } elseif ($password !== $passwordConfirmation) {
        $errors['password_confirmation'] = 'Les mots de passe ne correspondent pas.';
    }

    return $errors;
}