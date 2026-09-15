<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class Teacher
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            'SELECT id, user_id, nom, prenom, email, telephone, specialite, created_at, updated_at
             FROM teachers
             ORDER BY nom ASC, prenom ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, user_id, nom, prenom, email, telephone, specialite, created_at, updated_at
             FROM teachers
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, user_id, nom, prenom, email, telephone, specialite, created_at, updated_at
             FROM teachers
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => strtolower(trim($email))]);

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(string $nom, string $prenom, string $email, ?string $telephone, string $specialite): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO teachers (nom, prenom, email, telephone, specialite)
             VALUES (:nom, :prenom, :email, :telephone, :specialite)'
        );
        $statement->execute([
            'nom' => trim($nom),
            'prenom' => trim($prenom),
            'email' => strtolower(trim($email)),
            'telephone' => $telephone !== null && $telephone !== '' ? trim($telephone) : null,
            'specialite' => trim($specialite),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, string $nom, string $prenom, string $email, ?string $telephone, string $specialite): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE teachers
             SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, specialite = :specialite, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'nom' => trim($nom),
            'prenom' => trim($prenom),
            'email' => strtolower(trim($email)),
            'telephone' => $telephone !== null && $telephone !== '' ? trim($telephone) : null,
            'specialite' => trim($specialite),
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM teachers WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }
}
