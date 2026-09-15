<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/database.php';

final class Filiere
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            'SELECT id, nom, code, description, created_at, updated_at
             FROM filiers
             ORDER BY nom ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, nom, code, description, created_at, updated_at
             FROM filiers
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $result = $statement->fetch();

        return $result === false ? null : $result;
    }

    public function findByCode(string $code): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, nom, code, description, created_at, updated_at
             FROM filiers
             WHERE code = :code
             LIMIT 1'
        );
        $statement->execute(['code' => strtoupper(trim($code))]);

        $result = $statement->fetch();

        return $result === false ? null : $result;
    }

    public function create(string $nom, string $code, ?string $description): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO filiers (nom, code, description)
             VALUES (:nom, :code, :description)'
        );
        $statement->execute([
            'nom' => trim($nom),
            'code' => strtoupper(trim($code)),
            'description' => $description !== null ? trim($description) : null,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, string $nom, string $code, ?string $description): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE filiers
             SET nom = :nom, code = :code, description = :description, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'nom' => trim($nom),
            'code' => strtoupper(trim($code)),
            'description' => $description !== null ? trim($description) : null,
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM filiers WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }
}
