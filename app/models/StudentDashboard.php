<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class StudentDashboard
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function findStudent(int $userId, string $email): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT s.id, s.nom, s.prenom, s.matricule, s.email, s.telephone,
                    s.niveau, s.annee_academique, f.nom AS filiere_nom, f.code AS filiere_code
             FROM students s
             INNER JOIN filiers f ON f.id = s.filiere_id
             WHERE s.user_id = :user_id OR LOWER(s.email) = LOWER(:email)
             ORDER BY CASE WHEN s.user_id = :user_id_order THEN 0 ELSE 1 END
             LIMIT 1'
        );
        $statement->execute([
            'user_id' => $userId,
            'email' => strtolower(trim($email)),
            'user_id_order' => $userId,
        ]);

        $student = $statement->fetch();

        return $student === false ? null : $student;
    }

    public function defense(int $studentId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT d.id, d.titre, d.description, d.defense_date, d.defense_time,
                    d.statut, d.annee_academique,
                    t.nom AS teacher_nom, t.prenom AS teacher_prenom, t.email AS teacher_email,
                    t.specialite, r.nom AS room_nom, r.batiment
             FROM defenses d
             INNER JOIN teachers t ON t.id = d.supervisor_teacher_id
             INNER JOIN rooms r ON r.id = d.room_id
             WHERE d.student_id = :student_id
             ORDER BY d.defense_date DESC, d.defense_time DESC
             LIMIT 1'
        );
        $statement->execute(['student_id' => $studentId]);

        $defense = $statement->fetch();

        return $defense === false ? null : $defense;
    }

    public function jury(int $defenseId): array
    {
        $statement = $this->connection->prepare(
            'SELECT t.nom, t.prenom, t.email, t.specialite, dj.role_in_jury
             FROM defense_jury dj
             INNER JOIN teachers t ON t.id = dj.teacher_id
             WHERE dj.defense_id = :defense_id
             ORDER BY dj.role_in_jury ASC, t.nom ASC'
        );
        $statement->execute(['defense_id' => $defenseId]);

        return $statement->fetchAll();
    }
}
