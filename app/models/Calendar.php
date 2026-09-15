<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class Calendar
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function filieres(): array
    {
        return $this->connection->query('SELECT id, nom, code FROM filiers ORDER BY nom ASC')->fetchAll();
    }

    public function rooms(): array
    {
        return $this->connection->query('SELECT id, nom, batiment FROM rooms ORDER BY batiment ASC, nom ASC')->fetchAll();
    }

    public function defenses(array $filters): array
    {
        $conditions = [];
        $parameters = [];

        if (($filters['date'] ?? '') !== '') {
            $conditions[] = 'd.defense_date = :defense_date';
            $parameters['defense_date'] = $filters['date'];
        }

        if (($filters['filiere_id'] ?? 0) > 0) {
            $conditions[] = 's.filiere_id = :filiere_id';
            $parameters['filiere_id'] = (int) $filters['filiere_id'];
        }

        if (($filters['room_id'] ?? 0) > 0) {
            $conditions[] = 'd.room_id = :room_id';
            $parameters['room_id'] = (int) $filters['room_id'];
        }

        if (($filters['statut'] ?? '') !== '') {
            $conditions[] = 'd.statut = :statut';
            $parameters['statut'] = $filters['statut'];
        }

        $where = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $statement = $this->connection->prepare(
            'SELECT d.id, d.titre, d.description, d.defense_date, d.defense_time, d.statut,
                    s.nom AS student_nom, s.prenom AS student_prenom, s.matricule,
                    f.nom AS filiere_nom, f.code AS filiere_code,
                    r.nom AS room_nom, r.batiment,
                    t.nom AS teacher_nom, t.prenom AS teacher_prenom
             FROM defenses d
             INNER JOIN students s ON s.id = d.student_id
             INNER JOIN filiers f ON f.id = s.filiere_id
             INNER JOIN rooms r ON r.id = d.room_id
             INNER JOIN teachers t ON t.id = d.supervisor_teacher_id
             ' . $where . '
             ORDER BY d.defense_date ASC, d.defense_time ASC'
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }
}
