<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/models/Teacher.php';
require_once BASE_PATH . '/helpers/validation.php';

final class TeacherController
{
    public function __construct(private readonly Teacher $teacher)
    {
    }

    public function index(): array
    {
        return $this->teacher->all();
    }

    public function store(array $input): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $prenom = trim((string) ($input['prenom'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $telephone = isset($input['telephone']) ? trim((string) $input['telephone']) : null;
        $specialite = trim((string) ($input['specialite'] ?? ''));

        $errors = validateTeacherInput($nom, $prenom, $email, $telephone ?? '', $specialite);

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('nom', 'prenom', 'email', 'telephone', 'specialite')];
        }

        if ($this->teacher->findByEmail($email) !== null) {
            return [
                'errors' => ['email' => 'Cet enseignant existe deja avec cet email.'],
                'old' => compact('nom', 'prenom', 'email', 'telephone', 'specialite'),
            ];
        }

        $this->teacher->create($nom, $prenom, $email, $telephone !== '' ? $telephone : null, $specialite);

        return ['errors' => [], 'old' => [], 'success' => true];
    }

    public function update(int $id, array $input): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $prenom = trim((string) ($input['prenom'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $telephone = isset($input['telephone']) ? trim((string) $input['telephone']) : null;
        $specialite = trim((string) ($input['specialite'] ?? ''));

        $errors = validateTeacherInput($nom, $prenom, $email, $telephone ?? '', $specialite);

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('nom', 'prenom', 'email', 'telephone', 'specialite')];
        }

        $existing = $this->teacher->findByEmail($email);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            return [
                'errors' => ['email' => 'Cet enseignant existe deja avec cet email.'],
                'old' => compact('nom', 'prenom', 'email', 'telephone', 'specialite'),
            ];
        }

        $updated = $this->teacher->update($id, $nom, $prenom, $email, $telephone !== '' ? $telephone : null, $specialite);

        return [
            'errors' => $updated ? [] : ['form' => 'La mise a jour de l\'enseignant a echoue.'],
            'old' => compact('nom', 'prenom', 'email', 'telephone', 'specialite'),
            'success' => $updated,
        ];
    }

    public function delete(int $id): bool
    {
        return $this->teacher->delete($id);
    }
}
