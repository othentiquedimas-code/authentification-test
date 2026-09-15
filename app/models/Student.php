<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class Student
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            'SELECT s.id, s.user_id, s.nom, s.prenom, s.matricule, s.email, s.telephone, s.filiere_id, f.nom AS filiere_nom, s.niveau, s.annee_academique, s.created_at, s.updated_at
             FROM students s
             INNER JOIN filiers f ON f.id = s.filiere_id
             ORDER BY s.nom ASC, s.prenom ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT s.id, s.user_id, s.nom, s.prenom, s.matricule, s.email, s.telephone, s.filiere_id, f.nom AS filiere_nom, s.niveau, s.annee_academique, s.created_at, s.updated_at
             FROM students s
             INNER JOIN filiers f ON f.id = s.filiere_id
             WHERE s.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function findByMatricule(string $matricule): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, user_id, nom, prenom, matricule, email, telephone, filiere_id, niveau, annee_academique
             FROM students
             WHERE matricule = :matricule
             LIMIT 1'
        );
        $statement->execute(['matricule' => strtoupper(trim($matricule))]);

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(string $nom, string $prenom, string $matricule, string $email, ?string $telephone, int $filiereId, string $niveau, string $anneeAcademique): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO students (nom, prenom, matricule, email, telephone, filiere_id, niveau, annee_academique)
             VALUES (:nom, :prenom, :matricule, :email, :telephone, :filiere_id, :niveau, :annee_academique)'
        );
        $statement->execute([
            'nom' => trim($nom),
            'prenom' => trim($prenom),
            'matricule' => strtoupper(trim($matricule)),
            'email' => strtolower(trim($email)),
            'telephone' => $telephone !== null && $telephone !== '' ? trim($telephone) : null,
            'filiere_id' => $filiereId,
            'niveau' => trim($niveau),
            'annee_academique' => trim($anneeAcademique),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, string $nom, string $prenom, string $matricule, string $email, ?string $telephone, int $filiereId, string $niveau, string $anneeAcademique): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE students
             SET nom = :nom, prenom = :prenom, matricule = :matricule, email = :email, telephone = :telephone, filiere_id = :filiere_id, niveau = :niveau, annee_academique = :annee_academique, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'nom' => trim($nom),
            'prenom' => trim($prenom),
            'matricule' => strtoupper(trim($matricule)),
            'email' => strtolower(trim($email)),
            'telephone' => $telephone !== null && $telephone !== '' ? trim($telephone) : null,
            'filiere_id' => $filiereId,
            'niveau' => trim($niveau),
            'annee_academique' => trim($anneeAcademique),
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM students WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }
}
