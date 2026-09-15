<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class Room
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            'SELECT id, nom, batiment, capacite, created_at, updated_at
             FROM rooms
             ORDER BY batiment ASC, nom ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, nom, batiment, capacite, created_at, updated_at
             FROM rooms
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(string $nom, string $batiment, int $capacite): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO rooms (nom, batiment, capacite)
             VALUES (:nom, :batiment, :capacite)'
        );
        $statement->execute([
            'nom' => trim($nom),
            'batiment' => trim($batiment),
            'capacite' => $capacite,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, string $nom, string $batiment, int $capacite): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE rooms
             SET nom = :nom, batiment = :batiment, capacite = :capacite, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'nom' => trim($nom),
            'batiment' => trim($batiment),
            'capacite' => $capacite,
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM rooms WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }
}
