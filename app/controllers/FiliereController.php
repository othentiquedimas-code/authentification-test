<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/models/Filiere.php';
require_once BASE_PATH . '/helpers/validation.php';

final class FiliereController
{
    public function __construct(private readonly Filiere $filiere)
    {
    }

    public function index(): array
    {
        return $this->filiere->all();
    }

    public function store(array $input): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $code = strtoupper(trim((string) ($input['code'] ?? '')));
        $description = isset($input['description']) ? trim((string) $input['description']) : null;

        $errors = validateFiliereInput($nom, $code, $description);

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('nom', 'code', 'description')];
        }

        if ($this->filiere->findByCode($code) !== null) {
            return [
                'errors' => ['code' => 'Ce code de filiere existe deja.'],
                'old' => compact('nom', 'code', 'description'),
            ];
        }

        $this->filiere->create($nom, $code, $description !== '' ? $description : null);

        return ['errors' => [], 'old' => [], 'success' => true];
    }

    public function update(int $id, array $input): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $code = strtoupper(trim((string) ($input['code'] ?? '')));
        $description = isset($input['description']) ? trim((string) $input['description']) : null;

        $errors = validateFiliereInput($nom, $code, $description);

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('nom', 'code', 'description')];
        }

        $existing = $this->filiere->findByCode($code);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            return [
                'errors' => ['code' => 'Ce code de filiere existe deja.'],
                'old' => compact('nom', 'code', 'description'),
            ];
        }

        $updated = $this->filiere->update($id, $nom, $code, $description !== '' ? $description : null);

        return [
            'errors' => $updated ? [] : ['form' => 'La mise a jour de la filiere a echoue.'],
            'old' => compact('nom', 'code', 'description'),
            'success' => $updated,
        ];
    }

    public function delete(int $id): bool
    {
        try {
            return $this->filiere->delete($id);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return false;
            }

            throw $exception;
        }
    }
}
