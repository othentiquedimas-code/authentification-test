<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class User
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, password_hash, role, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, password_hash, role, created_at, updated_at
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function create(string $name, string $email, string $passwordHash, string $role = 'etudiant'): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (name, email, password_hash, role)
             VALUES (:name, :email, :password_hash, :role)'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
        ]);

        return (int) $this->connection->lastInsertId();
    }
}