<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class TeacherDashboard
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function findTeacher(int $userId, string $email): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, nom, prenom, email, specialite
             FROM teachers
             WHERE user_id = :user_id OR LOWER(email) = LOWER(:email)
             ORDER BY CASE WHEN user_id = :user_id_order THEN 0 ELSE 1 END
             LIMIT 1'
        );
        $statement->execute([
            'user_id' => $userId,
            'email' => strtolower(trim($email)),
            'user_id_order' => $userId,
        ]);

        $teacher = $statement->fetch();

        return $teacher === false ? null : $teacher;
    }

    public function counts(int $teacherId): array
    {
        $statement = $this->connection->prepare(
            'SELECT
                (SELECT COUNT(*) FROM defenses WHERE supervisor_teacher_id = :teacher_id_defenses) AS supervised_defenses,
                (SELECT COUNT(DISTINCT student_id) FROM defenses WHERE supervisor_teacher_id = :teacher_id_students) AS supervised_students,
                (SELECT COUNT(*) FROM defense_jury WHERE teacher_id = :teacher_id_jury) AS jury_assignments'
        );
        $statement->execute([
            'teacher_id_defenses' => $teacherId,
            'teacher_id_students' => $teacherId,
            'teacher_id_jury' => $teacherId,
        ]);

        return $statement->fetch() ?: [];
    }

    public function upcomingDefenses(int $teacherId, int $limit = 10): array
    {
        $limit = max(1, min($limit, 20));
        $statement = $this->connection->prepare(
            'SELECT d.id, d.titre, d.defense_date, d.defense_time, d.statut,
                    s.nom AS student_nom, s.prenom AS student_prenom, s.matricule,
                    r.nom AS room_nom, r.batiment
             FROM defenses d
             INNER JOIN students s ON s.id = d.student_id
             INNER JOIN rooms r ON r.id = d.room_id
             WHERE d.supervisor_teacher_id = :teacher_id
               AND d.defense_date >= CURRENT_DATE
             ORDER BY d.defense_date ASC, d.defense_time ASC
             LIMIT ' . $limit
        );
        $statement->execute(['teacher_id' => $teacherId]);

        return $statement->fetchAll();
    }

    public function juries(int $teacherId, int $limit = 10): array
    {
        $limit = max(1, min($limit, 20));
        $statement = $this->connection->prepare(
            'SELECT d.titre, d.defense_date, d.defense_time, dj.role_in_jury,
                    s.nom AS student_nom, s.prenom AS student_prenom,
                    r.nom AS room_nom
             FROM defense_jury dj
             INNER JOIN defenses d ON d.id = dj.defense_id
             INNER JOIN students s ON s.id = d.student_id
             INNER JOIN rooms r ON r.id = d.room_id
             WHERE dj.teacher_id = :teacher_id
             ORDER BY d.defense_date ASC, d.defense_time ASC
             LIMIT ' . $limit
        );
        $statement->execute(['teacher_id' => $teacherId]);

        return $statement->fetchAll();
    }
}
