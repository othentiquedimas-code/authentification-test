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

function validateFiliereInput(string $nom, string $code, ?string $description): array
{
    $errors = [];

    if ($nom === '') {
        $errors['nom'] = 'Le nom de la filiere est obligatoire.';
    } elseif (mb_strlen($nom) > 150) {
        $errors['nom'] = 'Le nom de la filiere ne peut pas depasser 150 caracteres.';
    }

    if ($code === '') {
        $errors['code'] = 'Le code de la filiere est obligatoire.';
    } elseif (!preg_match('/^[A-Z0-9-]+$/', $code)) {
        $errors['code'] = 'Le code ne doit contenir que des lettres, chiffres et traits d\'union.';
    } elseif (mb_strlen($code) > 50) {
        $errors['code'] = 'Le code ne peut pas depasser 50 caracteres.';
    }

    if ($description !== null && $description !== '' && mb_strlen($description) > 1000) {
        $errors['description'] = 'La description ne peut pas depasser 1000 caracteres.';
    }

    return $errors;
}

function validateTeacherInput(string $nom, string $prenom, string $email, string $telephone, string $specialite): array
{
    $errors = [];

    if ($nom === '') {
        $errors['nom'] = 'Le nom est obligatoire.';
    } elseif (mb_strlen($nom) > 100) {
        $errors['nom'] = 'Le nom ne peut pas depasser 100 caracteres.';
    }

    if ($prenom === '') {
        $errors['prenom'] = 'Le prenom est obligatoire.';
    } elseif (mb_strlen($prenom) > 100) {
        $errors['prenom'] = 'Le prenom ne peut pas depasser 100 caracteres.';
    }

    if ($email === '') {
        $errors['email'] = 'L\'email est obligatoire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'email n\'est pas valide.';
    } elseif (mb_strlen($email) > 255) {
        $errors['email'] = 'L\'email ne peut pas depasser 255 caracteres.';
    }

    if ($telephone !== '' && mb_strlen($telephone) > 30) {
        $errors['telephone'] = 'Le telephone ne peut pas depasser 30 caracteres.';
    }

    if ($specialite === '') {
        $errors['specialite'] = 'La specialite est obligatoire.';
    } elseif (mb_strlen($specialite) > 150) {
        $errors['specialite'] = 'La specialite ne peut pas depasser 150 caracteres.';
    }

    return $errors;
}

function validateStudentInput(string $nom, string $prenom, string $matricule, string $email, ?string $telephone, int $filiereId, string $niveau, string $anneeAcademique): array
{
    $errors = [];

    if ($nom === '') {
        $errors['nom'] = 'Le nom est obligatoire.';
    } elseif (mb_strlen($nom) > 100) {
        $errors['nom'] = 'Le nom ne peut pas depasser 100 caracteres.';
    }

    if ($prenom === '') {
        $errors['prenom'] = 'Le prenom est obligatoire.';
    } elseif (mb_strlen($prenom) > 100) {
        $errors['prenom'] = 'Le prenom ne peut pas depasser 100 caracteres.';
    }

    if ($matricule === '') {
        $errors['matricule'] = 'Le matricule est obligatoire.';
    } elseif (mb_strlen($matricule) > 50) {
        $errors['matricule'] = 'Le matricule ne peut pas depasser 50 caracteres.';
    }

    if ($email === '') {
        $errors['email'] = 'L\'email est obligatoire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'email n\'est pas valide.';
    } elseif (mb_strlen($email) > 255) {
        $errors['email'] = 'L\'email ne peut pas depasser 255 caracteres.';
    }

    if ($telephone !== null && $telephone !== '' && mb_strlen($telephone) > 30) {
        $errors['telephone'] = 'Le telephone ne peut pas depasser 30 caracteres.';
    }

    if ($filiereId < 1) {
        $errors['filiere_id'] = 'Veuillez selectionner une filiere.';
    }

    if ($niveau === '') {
        $errors['niveau'] = 'Le niveau est obligatoire.';
    } elseif (mb_strlen($niveau) > 50) {
        $errors['niveau'] = 'Le niveau ne peut pas depasser 50 caracteres.';
    }

    if ($anneeAcademique === '') {
        $errors['annee_academique'] = 'L\'annee academique est obligatoire.';
    } elseif (mb_strlen($anneeAcademique) > 20) {
        $errors['annee_academique'] = 'L\'annee academique ne peut pas depasser 20 caracteres.';
    }

    return $errors;
}

function validateRoomInput(string $nom, string $batiment, int $capacite): array
{
    $errors = [];

    if ($nom === '') {
        $errors['nom'] = 'Le nom de la salle est obligatoire.';
    } elseif (mb_strlen($nom) > 100) {
        $errors['nom'] = 'Le nom de la salle ne peut pas depasser 100 caracteres.';
    }

    if ($batiment === '') {
        $errors['batiment'] = 'Le batiment est obligatoire.';
    } elseif (mb_strlen($batiment) > 100) {
        $errors['batiment'] = 'Le batiment ne peut pas depasser 100 caracteres.';
    }

    if ($capacite < 1) {
        $errors['capacite'] = 'La capacite doit etre superieure a 0.';
    }

    return $errors;
}

function validateDefenseInput(string $titre, string $description, int $studentId, int $supervisorTeacherId, int $roomId, string $defenseDate, string $defenseTime, string $statut, string $anneeAcademique): array
{
    $errors = [];

    if ($titre === '') {
        $errors['titre'] = 'Le titre de la soutenance est obligatoire.';
    } elseif (mb_strlen($titre) > 255) {
        $errors['titre'] = 'Le titre ne peut pas depasser 255 caracteres.';
    }

    if ($description !== '' && mb_strlen($description) > 2000) {
        $errors['description'] = 'La description ne peut pas depasser 2000 caracteres.';
    }

    if ($studentId < 1) {
        $errors['student_id'] = 'Veuillez selectionner un etudiant.';
    }

    if ($supervisorTeacherId < 1) {
        $errors['supervisor_teacher_id'] = 'Veuillez selectionner un encadreur.';
    }

    if ($roomId < 1) {
        $errors['room_id'] = 'Veuillez selectionner une salle.';
    }

    if ($defenseDate === '' || !DateTimeImmutable::createFromFormat('Y-m-d', $defenseDate)) {
        $errors['defense_date'] = 'La date de soutenance est invalide.';
    }

    if ($defenseTime === '' || !DateTimeImmutable::createFromFormat('H:i', $defenseTime)) {
        $errors['defense_time'] = 'L\'heure de soutenance est invalide.';
    }

    $allowedStatuses = ['planifiee', 'confirmee', 'terminee', 'annulee'];
    if (!in_array($statut, $allowedStatuses, true)) {
        $errors['statut'] = 'Le statut choisi est invalide.';
    }

    if ($anneeAcademique === '') {
        $errors['annee_academique'] = 'L\'annee academique est obligatoire.';
    } elseif (mb_strlen($anneeAcademique) > 20) {
        $errors['annee_academique'] = 'L\'annee academique ne peut pas depasser 20 caracteres.';
    }

    return $errors;
}

function validateDefenseJuryInput(int $defenseId, int $teacherId, string $roleInJury): array
{
    $errors = [];

    if ($defenseId < 1) {
        $errors['defense_id'] = 'La soutenance est obligatoire.';
    }

    if ($teacherId < 1) {
        $errors['teacher_id'] = 'Veuillez selectionner un enseignant.';
    }

    $allowedRoles = ['president', 'examinateur', 'rapporteur'];
    if (!in_array($roleInJury, $allowedRoles, true)) {
        $errors['role_in_jury'] = 'Le role dans le jury est invalide.';
    }

    return $errors;
}