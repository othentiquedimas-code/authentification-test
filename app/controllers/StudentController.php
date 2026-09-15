<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/models/Student.php';
require_once BASE_PATH . '/helpers/validation.php';

final class StudentController
{
    public function __construct(private readonly Student $student)
    {
    }

    public function index(): array
    {
        return $this->student->all();
    }

    public function store(array $input): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $prenom = trim((string) ($input['prenom'] ?? ''));
        $matricule = strtoupper(trim((string) ($input['matricule'] ?? '')));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $telephone = isset($input['telephone']) ? trim((string) $input['telephone']) : null;
        $filiereId = filter_var($input['filiere_id'] ?? 0, FILTER_VALIDATE_INT);
        $niveau = trim((string) ($input['niveau'] ?? ''));
        $anneeAcademique = trim((string) ($input['annee_academique'] ?? ''));

        $errors = validateStudentInput($nom, $prenom, $matricule, $email, $telephone, $filiereId === false ? 0 : $filiereId, $niveau, $anneeAcademique);

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('nom', 'prenom', 'matricule', 'email', 'telephone', 'filiereId', 'niveau', 'anneeAcademique')];
        }

        if ($this->student->findByMatricule($matricule) !== null) {
            return [
                'errors' => ['matricule' => 'Ce matricule existe deja.'],
                'old' => compact('nom', 'prenom', 'matricule', 'email', 'telephone', 'filiereId', 'niveau', 'anneeAcademique'),
            ];
        }

        $this->student->create($nom, $prenom, $matricule, $email, $telephone !== '' ? $telephone : null, $filiereId, $niveau, $anneeAcademique);

        return ['errors' => [], 'old' => [], 'success' => true];
    }

    public function update(int $id, array $input): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $prenom = trim((string) ($input['prenom'] ?? ''));
        $matricule = strtoupper(trim((string) ($input['matricule'] ?? '')));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $telephone = isset($input['telephone']) ? trim((string) $input['telephone']) : null;
        $filiereId = filter_var($input['filiere_id'] ?? 0, FILTER_VALIDATE_INT);
        $niveau = trim((string) ($input['niveau'] ?? ''));
        $anneeAcademique = trim((string) ($input['annee_academique'] ?? ''));

        $errors = validateStudentInput($nom, $prenom, $matricule, $email, $telephone, $filiereId === false ? 0 : $filiereId, $niveau, $anneeAcademique);

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('nom', 'prenom', 'matricule', 'email', 'telephone', 'filiereId', 'niveau', 'anneeAcademique')];
        }

        $existing = $this->student->findByMatricule($matricule);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            return [
                'errors' => ['matricule' => 'Ce matricule existe deja.'],
                'old' => compact('nom', 'prenom', 'matricule', 'email', 'telephone', 'filiereId', 'niveau', 'anneeAcademique'),
            ];
        }

        $updated = $this->student->update($id, $nom, $prenom, $matricule, $email, $telephone !== '' ? $telephone : null, $filiereId, $niveau, $anneeAcademique);

        return [
            'errors' => $updated ? [] : ['form' => 'La mise a jour de l\'etudiant a echoue.'],
            'old' => compact('nom', 'prenom', 'matricule', 'email', 'telephone', 'filiereId', 'niveau', 'anneeAcademique'),
            'success' => $updated,
        ];
    }

    public function delete(int $id): bool
    {
        return $this->student->delete($id);
    }
}
