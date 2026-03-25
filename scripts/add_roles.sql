

-- Ajouter la colonne role_id (nullable pour ON DELETE SET NULL)
ALTER TABLE users ADD COLUMN role_id INT NULL DEFAULT 1;

-- Insérer les rôles de base avec des id fixes si non existants
INSERT IGNORE INTO role (id, name) VALUES 
	(1, 'user'),
	(2, 'moderator'),
	(3, 'admin');

-- Ajouter la contrainte de clé étrangère (ON DELETE SET NULL)
ALTER TABLE users ADD CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE SET NULL;



UPDATE users SET role_id = 1 WHERE role_id IS NULL;
