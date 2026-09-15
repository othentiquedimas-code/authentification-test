-- Add account approval status to existing installations.

USE auth_native;

ALTER TABLE users
    ADD COLUMN account_status ENUM('en_attente', 'actif', 'rejete') NOT NULL DEFAULT 'en_attente'
    AFTER role;

UPDATE users
SET account_status = 'actif'
WHERE email IN (
    'othentiquedimas@gmail.com',
    'enseignant.demo@unisoutenance.test',
    'etudiant.demo@unisoutenance.test'
);
