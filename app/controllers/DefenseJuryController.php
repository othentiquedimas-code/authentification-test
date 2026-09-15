<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/models/DefenseJury.php';
require_once BASE_PATH . '/helpers/validation.php';

final class DefenseJuryController
{
    public function __construct(private readonly DefenseJury $defenseJury)
    {
    }

    public function index(): array
    {
        return $this->defenseJury->all();
    }

    public function forDefense(int $defenseId): array
    {
        return $this->defenseJury->findByDefense($defenseId);
    }

    public function store(array $input): array
    {
        $defenseId = filter_var($input['defense_id'] ?? 0, FILTER_VALIDATE_INT);
        $teacherId = filter_var($input['teacher_id'] ?? 0, FILTER_VALIDATE_INT);
        $roleInJury = trim((string) ($input['role_in_jury'] ?? ''));

        $errors = validateDefenseJuryInput(
            $defenseId === false ? 0 : $defenseId,
            $teacherId === false ? 0 : $teacherId,
            $roleInJury
        );

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('defenseId', 'teacherId', 'roleInJury')];
        }

        $this->defenseJury->create($defenseId, $teacherId, $roleInJury);

        return ['errors' => [], 'old' => [], 'success' => true];
    }

    public function delete(int $id): bool
    {
        return $this->defenseJury->delete($id);
    }
}
