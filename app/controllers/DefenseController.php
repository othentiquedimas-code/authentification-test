<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/models/Defense.php';
require_once BASE_PATH . '/helpers/validation.php';

final class DefenseController
{
    public function __construct(private readonly Defense $defense)
    {
    }

    public function index(): array
    {
        return $this->defense->all();
    }

    public function store(array $input): array
    {
        $studentId = filter_var($input['student_id'] ?? 0, FILTER_VALIDATE_INT);
        $titre = trim((string) ($input['titre'] ?? ''));
        $description = isset($input['description']) ? trim((string) $input['description']) : null;
        $supervisorTeacherId = filter_var($input['supervisor_teacher_id'] ?? 0, FILTER_VALIDATE_INT);
        $roomId = filter_var($input['room_id'] ?? 0, FILTER_VALIDATE_INT);
        $defenseDate = trim((string) ($input['defense_date'] ?? ''));
        $defenseTime = trim((string) ($input['defense_time'] ?? ''));
        $statut = trim((string) ($input['statut'] ?? ''));
        $anneeAcademique = trim((string) ($input['annee_academique'] ?? ''));

        $errors = validateDefenseInput(
            $titre,
            $description ?? '',
            $studentId === false ? 0 : $studentId,
            $supervisorTeacherId === false ? 0 : $supervisorTeacherId,
            $roomId === false ? 0 : $roomId,
            $defenseDate,
            $defenseTime,
            $statut,
            $anneeAcademique
        );

        if ($errors !== []) {
            return [
                'errors' => $errors,
                'old' => compact('studentId', 'titre', 'description', 'supervisorTeacherId', 'roomId', 'defenseDate', 'defenseTime', 'statut', 'anneeAcademique'),
            ];
        }

        $this->defense->create(
            $studentId,
            $titre,
            $description !== '' ? $description : null,
            $supervisorTeacherId,
            $roomId,
            $defenseDate,
            $defenseTime,
            $statut,
            $anneeAcademique
        );

        return ['errors' => [], 'old' => [], 'success' => true];
    }

    public function update(int $id, array $input): array
    {
        $studentId = filter_var($input['student_id'] ?? 0, FILTER_VALIDATE_INT);
        $titre = trim((string) ($input['titre'] ?? ''));
        $description = isset($input['description']) ? trim((string) $input['description']) : null;
        $supervisorTeacherId = filter_var($input['supervisor_teacher_id'] ?? 0, FILTER_VALIDATE_INT);
        $roomId = filter_var($input['room_id'] ?? 0, FILTER_VALIDATE_INT);
        $defenseDate = trim((string) ($input['defense_date'] ?? ''));
        $defenseTime = trim((string) ($input['defense_time'] ?? ''));
        $statut = trim((string) ($input['statut'] ?? ''));
        $anneeAcademique = trim((string) ($input['annee_academique'] ?? ''));

        $errors = validateDefenseInput(
            $titre,
            $description ?? '',
            $studentId === false ? 0 : $studentId,
            $supervisorTeacherId === false ? 0 : $supervisorTeacherId,
            $roomId === false ? 0 : $roomId,
            $defenseDate,
            $defenseTime,
            $statut,
            $anneeAcademique
        );

        if ($errors !== []) {
            return [
                'errors' => $errors,
                'old' => compact('studentId', 'titre', 'description', 'supervisorTeacherId', 'roomId', 'defenseDate', 'defenseTime', 'statut', 'anneeAcademique'),
            ];
        }

        $updated = $this->defense->update(
            $id,
            $studentId,
            $titre,
            $description !== '' ? $description : null,
            $supervisorTeacherId,
            $roomId,
            $defenseDate,
            $defenseTime,
            $statut,
            $anneeAcademique
        );

        return [
            'errors' => $updated ? [] : ['form' => 'La mise a jour de la soutenance a echoue.'],
            'old' => compact('studentId', 'titre', 'description', 'supervisorTeacherId', 'roomId', 'defenseDate', 'defenseTime', 'statut', 'anneeAcademique'),
            'success' => $updated,
        ];
    }

    public function delete(int $id): bool
    {
        try {
            return $this->defense->delete($id);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return false;
            }

            throw $exception;
        }
    }
}
