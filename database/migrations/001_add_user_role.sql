-- Migration for databases created before users.role was added.
-- Run once against the auth_native database.

USE auth_native;

ALTER TABLE users
    ADD COLUMN role ENUM('admin', 'enseignant', 'etudiant') NOT NULL DEFAULT 'etudiant'
    AFTER password_hash;
