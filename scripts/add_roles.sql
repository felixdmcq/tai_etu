-- Migration: Ajouter role_id à la table users
-- Date: 2026-03-25

ALTER TABLE users ADD COLUMN role_id INT DEFAULT 1;
ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE SET DEFAULT;

-- Insérer les rôles de base s'ils n'existent pas
INSERT IGNORE INTO role (id, name) VALUES 
(1, 'user'),
(2, 'moderator'),
(3, 'admin');

-- Les utilisateurs existants sont maintenant des 'user' par défaut
-- Pour promouvoir un admin, il faut modifier son role_id à 3
