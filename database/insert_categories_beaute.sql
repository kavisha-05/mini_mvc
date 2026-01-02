-- Insertion des catégories de produits de beauté
-- Exécutez ce script dans votre base de données MySQL/MariaDB

-- Supprime les anciennes catégories d'exemple si nécessaire (optionnel)
-- DELETE FROM categorie WHERE nom IN ('Électronique', 'Vêtements', 'Alimentation', 'Maison & Jardin');

-- Insertion des nouvelles catégories de beauté
INSERT INTO categorie (nom, description) VALUES
('Essences', 'Essences hydratantes et régénérantes pour la peau'),
('Sérums', 'Sérums concentrés pour des soins ciblés'),
('Nettoyants', 'Produits de nettoyage et démaquillants'),
('Toners', 'Toniques et lotions pour équilibrer le pH de la peau'),
('Protection solaire', 'Crèmes et sprays de protection solaire'),
('Masques', 'Masques visage pour des soins intensifs'),
('Exfoliants', 'Produits exfoliants pour éliminer les cellules mortes'),
('Soins yeux', 'Crèmes et sérums spécialisés pour le contour des yeux'),
('Soins lèvres', 'Baumes et soins pour les lèvres')
ON DUPLICATE KEY UPDATE nom=nom;

-- Vérification
SELECT * FROM categorie ORDER BY nom ASC;

