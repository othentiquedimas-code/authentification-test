<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/models/Room.php';
require_once BASE_PATH . '/helpers/validation.php';

final class RoomController
{
    public function __construct(private readonly Room $room)
    {
    }

    public function index(): array
    {
        return $this->room->all();
    }

    public function store(array $input): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $batiment = trim((string) ($input['batiment'] ?? ''));
        $capacite = filter_var($input['capacite'] ?? 0, FILTER_VALIDATE_INT);

        $errors = validateRoomInput($nom, $batiment, $capacite === false ? 0 : $capacite);

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('nom', 'batiment', 'capacite')];
        }

        $this->room->create($nom, $batiment, $capacite);

        return ['errors' => [], 'old' => [], 'success' => true];
    }

    public function update(int $id, array $input): array
    {
        $nom = trim((string) ($input['nom'] ?? ''));
        $batiment = trim((string) ($input['batiment'] ?? ''));
        $capacite = filter_var($input['capacite'] ?? 0, FILTER_VALIDATE_INT);

        $errors = validateRoomInput($nom, $batiment, $capacite === false ? 0 : $capacite);

        if ($errors !== []) {
            return ['errors' => $errors, 'old' => compact('nom', 'batiment', 'capacite')];
        }

        $updated = $this->room->update($id, $nom, $batiment, $capacite);

        return [
            'errors' => $updated ? [] : ['form' => 'La mise a jour de la salle a echoue.'],
            'old' => compact('nom', 'batiment', 'capacite'),
            'success' => $updated,
        ];
    }

    public function delete(int $id): bool
    {
        return $this->room->delete($id);
    }
}
