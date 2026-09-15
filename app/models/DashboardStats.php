<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class DashboardStats
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function counts(): array
    {
        $statement = $this->connection->query(
            'SELECT
                (SELECT COUNT(*) FROM filiers) AS filieres,
                (SELECT COUNT(*) FROM students) AS students,
                (SELECT COUNT(*) FROM teachers) AS teachers,
                (SELECT COUNT(*) FROM defenses) AS defenses,
                (SELECT COUNT(*) FROM rooms) AS rooms'
        );

        return $statement->fetch() ?: [];
    }

    public function upcomingDefenses(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        $statement = $this->connection->query(
            'SELECT d.id, d.titre, d.defense_date, d.defense_time, d.statut,
                    s.nom AS student_nom, s.prenom AS student_prenom,
                    r.nom AS room_nom, r.batiment
             FROM defenses d
             INNER JOIN students s ON s.id = d.student_id
             INNER JOIN rooms r ON r.id = d.room_id
             WHERE d.defense_date >= CURRENT_DATE
             ORDER BY d.defense_date ASC, d.defense_time ASC
             LIMIT ' . $limit
        );

        return $statement->fetchAll();
    }
}
