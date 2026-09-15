<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class Defense
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            'SELECT d.id, d.student_id, s.nom AS student_nom, s.prenom AS student_prenom, s.matricule, d.titre, d.description,
                    d.supervisor_teacher_id, t.nom AS teacher_nom, t.prenom AS teacher_prenom,
                    d.room_id, r.nom AS room_nom, r.batiment,
                    d.defense_date, d.defense_time, d.statut, d.annee_academique,
                    d.created_at, d.updated_at
             FROM defenses d
             INNER JOIN students s ON s.id = d.student_id
             INNER JOIN teachers t ON t.id = d.supervisor_teacher_id
             INNER JOIN rooms r ON r.id = d.room_id
             ORDER BY d.defense_date ASC, d.defense_time ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT d.id, d.student_id, s.nom AS student_nom, s.prenom AS student_prenom, s.matricule,
                    d.titre, d.description, d.supervisor_teacher_id, t.nom AS teacher_nom, t.prenom AS teacher_prenom,
                    d.room_id, r.nom AS room_nom, r.batiment, d.defense_date, d.defense_time, d.statut, d.annee_academique
             FROM defenses d
             INNER JOIN students s ON s.id = d.student_id
             INNER JOIN teachers t ON t.id = d.supervisor_teacher_id
             INNER JOIN rooms r ON r.id = d.room_id
             WHERE d.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(
        int $studentId,
        string $titre,
        ?string $description,
        int $supervisorTeacherId,
        int $roomId,
        string $defenseDate,
        string $defenseTime,
        string $statut,
        string $anneeAcademique
    ): int {
        $statement = $this->connection->prepare(
            'INSERT INTO defenses (student_id, titre, description, supervisor_teacher_id, room_id, defense_date, defense_time, statut, annee_academique)
             VALUES (:student_id, :titre, :description, :supervisor_teacher_id, :room_id, :defense_date, :defense_time, :statut, :annee_academique)'
        );
        $statement->execute([
            'student_id' => $studentId,
            'titre' => trim($titre),
            'description' => $description !== null && $description !== '' ? trim($description) : null,
            'supervisor_teacher_id' => $supervisorTeacherId,
            'room_id' => $roomId,
            'defense_date' => $defenseDate,
            'defense_time' => $defenseTime,
            'statut' => $statut,
            'annee_academique' => trim($anneeAcademique),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(
        int $id,
        int $studentId,
        string $titre,
        ?string $description,
        int $supervisorTeacherId,
        int $roomId,
        string $defenseDate,
        string $defenseTime,
        string $statut,
        string $anneeAcademique
    ): bool {
        $statement = $this->connection->prepare(
            'UPDATE defenses
             SET student_id = :student_id, titre = :titre, description = :description, supervisor_teacher_id = :supervisor_teacher_id,
                 room_id = :room_id, defense_date = :defense_date, defense_time = :defense_time, statut = :statut,
                 annee_academique = :annee_academique, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'student_id' => $studentId,
            'titre' => trim($titre),
            'description' => $description !== null && $description !== '' ? trim($description) : null,
            'supervisor_teacher_id' => $supervisorTeacherId,
            'room_id' => $roomId,
            'defense_date' => $defenseDate,
            'defense_time' => $defenseTime,
            'statut' => $statut,
            'annee_academique' => trim($anneeAcademique),
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM defenses WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }
}
