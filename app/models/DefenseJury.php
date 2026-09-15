<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class DefenseJury
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            'SELECT dj.id, dj.defense_id, d.titre, dj.teacher_id, t.nom, t.prenom, dj.role_in_jury
             FROM defense_jury dj
             INNER JOIN defenses d ON d.id = dj.defense_id
             INNER JOIN teachers t ON t.id = dj.teacher_id
             ORDER BY d.defense_date ASC, d.defense_time ASC, dj.role_in_jury ASC'
        );

        return $statement->fetchAll();
    }

    public function findByDefense(int $defenseId): array
    {
        $statement = $this->connection->prepare(
            'SELECT dj.id, dj.defense_id, dj.teacher_id, t.nom, t.prenom, dj.role_in_jury
             FROM defense_jury dj
             INNER JOIN teachers t ON t.id = dj.teacher_id
             WHERE dj.defense_id = :defense_id
             ORDER BY dj.role_in_jury ASC'
        );
        $statement->execute(['defense_id' => $defenseId]);

        return $statement->fetchAll();
    }

    public function create(int $defenseId, int $teacherId, string $roleInJury): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO defense_jury (defense_id, teacher_id, role_in_jury)
             VALUES (:defense_id, :teacher_id, :role_in_jury)'
        );
        $statement->execute([
            'defense_id' => $defenseId,
            'teacher_id' => $teacherId,
            'role_in_jury' => $roleInJury,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM defense_jury WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }
}
