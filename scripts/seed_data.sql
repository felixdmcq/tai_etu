-- Script d'insertion de données de démonstration
-- À exécuter après query.sql et add_roles.sql dans phpMyAdmin
-- Créé le 23/03/2026

USE tai_etu_felix_domecq_cazaux;

-- =============================================
-- UTILISATEURS (mot de passe: "password123" hashe avec password_hash)
-- =============================================
INSERT INTO users (email, password) VALUES
('admin@recetteshare.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('marie.dupont@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('jean.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('sophie.bernard@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('pierre.durand@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('claire.moreau@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('thomas.petit@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('emma.leroy@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- =============================================
-- TAGS
-- =============================================
INSERT INTO tag (name) VALUES
('Végétarien'),
('Vegan'),
('Sans gluten'),
('Rapide'),
('Facile'),
('Dessert'),
('Plat principal'),
('Entrée'),
('Français'),
('Italien'),
('Asiatique'),
('Mexicain'),
('Healthy'),
('Comfort food'),
('Été'),
('Hiver'),
('Brunch'),
('Apéritif'),
('Famille'),
('Économique');

-- =============================================
-- INGRÉDIENTS
-- =============================================
INSERT INTO ingredient (name) VALUES
('Oeufs'),
('Farine'),
('Sucre'),
('Beurre'),
('Lait'),
('Sel'),
('Poivre'),
('Huile d\'olive'),
('Ail'),
('Oignon'),
('Tomates'),
('Poulet'),
('Boeuf'),
('Porc'),
('Saumon'),
('Pâtes'),
('Riz'),
('Pommes de terre'),
('Carottes'),
('Courgettes'),
('Poivrons'),
('Champignons'),
('Crème fraîche'),
('Fromage râpé'),
('Parmesan'),
('Mozzarella'),
('Basilic'),
('Persil'),
('Thym'),
('Romarin'),
('Citron'),
('Chocolat'),
('Vanille'),
('Levure'),
('Bicarbonate'),
('Miel'),
('Sauce soja'),
('Gingembre'),
('Curry'),
('Paprika'),
('Cumin'),
('Coriandre'),
('Piment'),
('Avocat'),
('Épinards'),
('Brocoli'),
('Haricots verts'),
('Lentilles'),
('Pois chiches'),
('Noix'),
('Amandes'),
('Fruits rouges'),
('Pommes'),
('Bananes'),
('Mangue');

-- =============================================
-- RECETTES
-- =============================================
INSERT INTO recipe (user_id, title, description, status, created_at) VALUES
-- Recettes de Marie (user_id: 2)
(2, 'Quiche Lorraine traditionnelle', 'Une quiche lorraine crémeuse et savoureuse, parfaite pour un repas en famille ou entre amis. Croûte dorée et garniture fondante garanties!', 'published', '2026-01-15 10:30:00'),
(2, 'Tarte aux pommes grand-mère', 'La recette de ma grand-mère, avec des pommes fondantes et une touche de cannelle. Un dessert réconfortant qui rappelle l\'enfance.', 'published', '2026-01-20 14:00:00'),
(2, 'Soupe à l\'oignon gratinée', 'Une soupe réconfortante pour les soirées d\'hiver, gratinée au fromage avec des croûtons dorés.', 'published', '2026-02-01 18:00:00'),

-- Recettes de Jean (user_id: 3)
(3, 'Risotto aux champignons', 'Un risotto crémeux aux champignons de Paris et cèpes séchés, parfumé au parmesan. Le secret: remuer constamment!', 'published', '2026-01-25 12:00:00'),
(3, 'Pâtes carbonara authentiques', 'La vraie recette italienne sans crème! Avec du guanciale, des oeufs et du pecorino romano.', 'published', '2026-02-05 19:30:00'),
(3, 'Tiramisu classique', 'Le dessert italien par excellence. Mascarpone onctueux, café fort et cacao amer pour un équilibre parfait.', 'published', '2026-02-10 16:00:00'),

-- Recettes de Sophie (user_id: 4)
(4, 'Buddha Bowl végétarien', 'Un bol coloré et nutritif avec quinoa, légumes rôtis, avocat et sauce tahini. Parfait pour un déjeuner sain!', 'published', '2026-02-08 11:00:00'),
(4, 'Curry de pois chiches', 'Un curry végétalien onctueux et parfumé, servi avec du riz basmati. Réconfortant et plein de saveurs!', 'published', '2026-02-15 13:00:00'),
(4, 'Smoothie bowl aux fruits rouges', 'Un petit-déjeuner vitaminé et Instagram-worthy avec açaï, fruits frais et granola croquant.', 'published', '2026-02-20 08:30:00'),
(4, 'Salade de lentilles méditerranéenne', 'Une salade protéinée avec lentilles, tomates séchées, feta et herbes fraîches.', 'draft', '2026-03-01 10:00:00'),

-- Recettes de Pierre (user_id: 5)
(5, 'Boeuf bourguignon', 'Le grand classique de la cuisine française, mijoté pendant des heures pour une viande fondante.', 'published', '2026-02-12 17:00:00'),
(5, 'Poulet rôti aux herbes', 'Un poulet doré et juteux, parfumé au thym et au romarin, avec des pommes de terre rissolées.', 'published', '2026-02-18 12:30:00'),
(5, 'Magret de canard aux cerises', 'Un magret rosé accompagné d\'une sauce aux cerises aigres-douces. Élégant et savoureux!', 'published', '2026-02-25 20:00:00'),

-- Recettes de Claire (user_id: 6)
(6, 'Fondant au chocolat', 'Le coeur coulant parfait! Croustillant à l\'extérieur, fondant au chocolat noir à l\'intérieur.', 'published', '2026-02-14 15:00:00'),
(6, 'Crème brûlée à la vanille', 'Une crème onctueuse à la vanille bourbon avec une croûte de caramel craquante.', 'published', '2026-02-22 16:30:00'),
(6, 'Macarons à la framboise', 'Des macarons français parfaits avec une ganache à la framboise. Croquants et moelleux!', 'published', '2026-03-05 14:00:00'),

-- Recettes de Thomas (user_id: 7)
(7, 'Tacos au poulet mariné', 'Des tacos croustillants garnis de poulet épicé, guacamole maison et pico de gallo frais.', 'published', '2026-02-28 19:00:00'),
(7, 'Pad Thaï au tofu', 'Le classique thaïlandais avec nouilles de riz, tofu croustillant et sauce tamarin.', 'published', '2026-03-08 12:00:00'),
(7, 'Sushi maki maison', 'Apprenez à faire vos propres makis avec saumon, avocat et concombre!', 'published', '2026-03-12 18:00:00'),

-- Recettes d'Emma (user_id: 8)
(8, 'Pancakes moelleux', 'Des pancakes américains ultra moelleux pour un brunch parfait, servis avec sirop d\'érable.', 'published', '2026-03-01 09:00:00'),
(8, 'Overnight oats aux fruits', 'Préparez votre petit-déjeuner la veille! Flocons d\'avoine, lait d\'amande et fruits frais.', 'published', '2026-03-10 07:30:00'),
(8, 'Banana bread aux noix', 'Un cake moelleux aux bananes bien mûres et aux noix croquantes. Zéro gaspillage!', 'published', '2026-03-15 11:00:00'),
(8, 'Crêpes bretonnes', 'La recette authentique des crêpes fines et légères, parfaites pour la Chandeleur!', 'draft', '2026-03-18 10:00:00');

-- =============================================
-- ÉTAPES DES RECETTES
-- =============================================

-- Quiche Lorraine (recipe_id: 1)
INSERT INTO step (recipe_id, step_number, content) VALUES
(1, 1, 'Préchauffez le four à 180°C. Étalez la pâte brisée dans un moule à tarte beurré.'),
(1, 2, 'Faites revenir les lardons dans une poêle sans matière grasse jusqu\'à ce qu\'ils soient dorés.'),
(1, 3, 'Dans un saladier, battez les oeufs avec la crème fraîche, le sel, le poivre et la muscade.'),
(1, 4, 'Répartissez les lardons sur la pâte, puis versez l\'appareil aux oeufs.'),
(1, 5, 'Enfournez pour 35-40 minutes jusqu\'à ce que la quiche soit dorée et gonflée.');

-- Tarte aux pommes (recipe_id: 2)
INSERT INTO step (recipe_id, step_number, content) VALUES
(2, 1, 'Préchauffez le four à 200°C. Étalez la pâte feuilletée dans un moule.'),
(2, 2, 'Pelez et coupez les pommes en fines lamelles.'),
(2, 3, 'Disposez les lamelles de pommes en rosace sur la pâte.'),
(2, 4, 'Saupoudrez de sucre et de cannelle, parsemez de noisettes de beurre.'),
(2, 5, 'Enfournez pour 30-35 minutes. Badigeonnez de confiture d\'abricot à la sortie du four.');

-- Soupe à l'oignon (recipe_id: 3)
INSERT INTO step (recipe_id, step_number, content) VALUES
(3, 1, 'Émincez finement les oignons. Faites-les revenir dans le beurre à feu doux pendant 30 minutes.'),
(3, 2, 'Saupoudrez de farine, mélangez et laissez cuire 2 minutes.'),
(3, 3, 'Versez le bouillon de boeuf chaud progressivement en remuant. Laissez mijoter 20 minutes.'),
(3, 4, 'Répartissez la soupe dans des bols allant au four, ajoutez les croûtons et le gruyère râpé.'),
(3, 5, 'Passez sous le gril du four jusqu\'à ce que le fromage soit doré et bouillonnant.');

-- Risotto aux champignons (recipe_id: 4)
INSERT INTO step (recipe_id, step_number, content) VALUES
(4, 1, 'Faites tremper les cèpes séchés dans de l\'eau chaude pendant 20 minutes.'),
(4, 2, 'Faites revenir l\'oignon émincé dans le beurre, ajoutez le riz et nacrez-le.'),
(4, 3, 'Versez le vin blanc et remuez jusqu\'à absorption complète.'),
(4, 4, 'Ajoutez le bouillon chaud louche par louche, en remuant constamment pendant 18-20 minutes.'),
(4, 5, 'Incorporez les champignons sautés, le parmesan et le beurre. Servez immédiatement.');

-- Pâtes carbonara (recipe_id: 5)
INSERT INTO step (recipe_id, step_number, content) VALUES
(5, 1, 'Faites cuire les pâtes dans une grande quantité d\'eau salée selon les instructions.'),
(5, 2, 'Pendant ce temps, faites revenir le guanciale coupé en dés jusqu\'à ce qu\'il soit croustillant.'),
(5, 3, 'Battez les oeufs entiers avec les jaunes, ajoutez le pecorino râpé et le poivre.'),
(5, 4, 'Égouttez les pâtes en réservant un peu d\'eau de cuisson.'),
(5, 5, 'Hors du feu, versez les pâtes sur le guanciale, ajoutez le mélange d\'oeufs et mélangez rapidement.');

-- Tiramisu (recipe_id: 6)
INSERT INTO step (recipe_id, step_number, content) VALUES
(6, 1, 'Séparez les blancs des jaunes. Battez les jaunes avec le sucre jusqu\'à ce que le mélange blanchisse.'),
(6, 2, 'Ajoutez le mascarpone aux jaunes et mélangez délicatement.'),
(6, 3, 'Montez les blancs en neige ferme et incorporez-les à la préparation.'),
(6, 4, 'Trempez rapidement les biscuits dans le café froid, disposez-les dans le plat.'),
(6, 5, 'Alternez couches de biscuits et de crème. Réfrigérez 4h minimum. Saupoudrez de cacao avant de servir.');

-- Buddha Bowl (recipe_id: 7)
INSERT INTO step (recipe_id, step_number, content) VALUES
(7, 1, 'Faites cuire le quinoa selon les instructions du paquet.'),
(7, 2, 'Coupez les légumes (patate douce, brocoli, carottes) et faites-les rôtir au four à 200°C pendant 25 minutes.'),
(7, 3, 'Préparez la sauce tahini: mélangez tahini, citron, ail, eau et sel.'),
(7, 4, 'Coupez l\'avocat en tranches et préparez les pois chiches (rincés ou rôtis).'),
(7, 5, 'Assemblez le bowl: quinoa, légumes rôtis, avocat, pois chiches, graines. Arrosez de sauce.');

-- Curry de pois chiches (recipe_id: 8)
INSERT INTO step (recipe_id, step_number, content) VALUES
(8, 1, 'Faites revenir l\'oignon, l\'ail et le gingembre émincés dans l\'huile.'),
(8, 2, 'Ajoutez les épices (curry, cumin, curcuma, garam masala) et faites torréfier 1 minute.'),
(8, 3, 'Versez les tomates concassées et le lait de coco, mélangez bien.'),
(8, 4, 'Ajoutez les pois chiches égouttés et laissez mijoter 20 minutes.'),
(8, 5, 'Rectifiez l\'assaisonnement, ajoutez des épinards frais. Servez avec du riz basmati et de la coriandre.');

-- Smoothie bowl (recipe_id: 9)
INSERT INTO step (recipe_id, step_number, content) VALUES
(9, 1, 'Congelez les fruits rouges et la banane la veille.'),
(9, 2, 'Mixez les fruits congelés avec un peu de lait d\'amande jusqu\'à obtenir une texture épaisse.'),
(9, 3, 'Versez dans un bol et lissez la surface.'),
(9, 4, 'Disposez les toppings: granola, fruits frais, noix de coco, graines de chia.'),
(9, 5, 'Ajoutez un filet de miel et servez immédiatement.');

-- Boeuf bourguignon (recipe_id: 11)
INSERT INTO step (recipe_id, step_number, content) VALUES
(11, 1, 'La veille, faites mariner le boeuf coupé en cubes dans le vin rouge avec les aromates.'),
(11, 2, 'Égouttez la viande, séchez-la et faites-la dorer dans une cocotte avec de l\'huile.'),
(11, 3, 'Ajoutez les oignons, les carottes, l\'ail. Saupoudrez de farine et mélangez.'),
(11, 4, 'Versez la marinade et le bouillon. Ajoutez le bouquet garni. Laissez mijoter 2h30.'),
(11, 5, 'Ajoutez les champignons et les lardons sautés 30 minutes avant la fin. Servez avec des pommes de terre.');

-- Poulet rôti (recipe_id: 12)
INSERT INTO step (recipe_id, step_number, content) VALUES
(12, 1, 'Préchauffez le four à 200°C. Préparez un mélange de beurre, thym, romarin et ail.'),
(12, 2, 'Glissez le beurre aux herbes sous la peau du poulet.'),
(12, 3, 'Disposez le poulet dans un plat avec les pommes de terre coupées en quartiers.'),
(12, 4, 'Arrosez d\'huile d\'olive, salez et poivrez généreusement.'),
(12, 5, 'Enfournez pour 1h15-1h30, en arrosant régulièrement du jus de cuisson.');

-- Fondant au chocolat (recipe_id: 14)
INSERT INTO step (recipe_id, step_number, content) VALUES
(14, 1, 'Préchauffez le four à 200°C. Beurrez et farinez 6 ramequins.'),
(14, 2, 'Faites fondre le chocolat et le beurre au bain-marie.'),
(14, 3, 'Battez les oeufs entiers avec le sucre jusqu\'à ce que le mélange mousse.'),
(14, 4, 'Incorporez le chocolat fondu, puis la farine tamisée.'),
(14, 5, 'Versez dans les ramequins et enfournez 10-12 minutes. Le coeur doit rester coulant.');

-- Crème brûlée (recipe_id: 15)
INSERT INTO step (recipe_id, step_number, content) VALUES
(15, 1, 'Préchauffez le four à 100°C. Fendez la gousse de vanille et grattez les graines.'),
(15, 2, 'Faites chauffer la crème avec la vanille. Battez les jaunes avec le sucre.'),
(15, 3, 'Versez la crème chaude sur les jaunes en fouettant.'),
(15, 4, 'Répartissez dans des ramequins. Enfournez au bain-marie pour 50-60 minutes.'),
(15, 5, 'Laissez refroidir puis réfrigérez 4h. Avant de servir, saupoudrez de sucre et caramélisez au chalumeau.');

-- Tacos (recipe_id: 17)
INSERT INTO step (recipe_id, step_number, content) VALUES
(17, 1, 'Faites mariner le poulet dans le jus de citron vert, l\'ail, le cumin et le paprika pendant 1h.'),
(17, 2, 'Préparez le guacamole: écrasez les avocats avec le citron, l\'oignon, la coriandre et le sel.'),
(17, 3, 'Préparez le pico de gallo: mélangez tomates, oignon, coriandre, piment et citron vert.'),
(17, 4, 'Faites griller le poulet mariné et coupez-le en lanières.'),
(17, 5, 'Réchauffez les tortillas, garnissez de poulet, guacamole, pico de gallo et crème acidulée.');

-- Pad Thaï (recipe_id: 18)
INSERT INTO step (recipe_id, step_number, content) VALUES
(18, 1, 'Faites tremper les nouilles de riz dans l\'eau chaude pendant 20 minutes.'),
(18, 2, 'Préparez la sauce: mélangez sauce tamarin, sauce poisson (ou soja), sucre de palme et piment.'),
(18, 3, 'Faites dorer le tofu coupé en cubes dans un wok très chaud.'),
(18, 4, 'Ajoutez l\'ail, les nouilles égouttées et la sauce. Faites sauter 3-4 minutes.'),
(18, 5, 'Poussez les nouilles sur le côté, faites une omelette, mélangez. Servez avec cacahuètes et citron vert.');

-- Pancakes (recipe_id: 20)
INSERT INTO step (recipe_id, step_number, content) VALUES
(20, 1, 'Mélangez la farine, le sucre, la levure et le sel dans un grand bol.'),
(20, 2, 'Dans un autre bol, fouettez le lait, l\'oeuf et le beurre fondu.'),
(20, 3, 'Versez les ingrédients liquides sur les ingrédients secs. Mélangez juste assez.'),
(20, 4, 'Faites chauffer une poêle beurrée. Versez une louche de pâte et faites cuire jusqu\'aux bulles.'),
(20, 5, 'Retournez et faites cuire l\'autre côté. Servez avec sirop d\'érable et fruits frais.');

-- Banana bread (recipe_id: 22)
INSERT INTO step (recipe_id, step_number, content) VALUES
(22, 1, 'Préchauffez le four à 180°C. Beurrez un moule à cake.'),
(22, 2, 'Écrasez les bananes bien mûres à la fourchette.'),
(22, 3, 'Mélangez le beurre fondu et le sucre, ajoutez l\'oeuf et la vanille.'),
(22, 4, 'Incorporez les bananes écrasées, puis la farine et la levure. Ajoutez les noix.'),
(22, 5, 'Versez dans le moule et enfournez pour 55-60 minutes. Laissez refroidir avant de démouler.');

-- =============================================
-- INGRÉDIENTS DES RECETTES
-- =============================================

-- Quiche Lorraine
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(1, 1, '4'),  -- Oeufs
(1, 4, '30g'),  -- Beurre
(1, 23, '20cl'),  -- Crème fraîche
(1, 6, '1 pincée'),  -- Sel
(1, 7, '1 pincée');  -- Poivre

-- Tarte aux pommes
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(2, 53, '6 moyennes'),  -- Pommes
(2, 3, '50g'),  -- Sucre
(2, 4, '40g'),  -- Beurre
(2, 2, '250g pâte');  -- Farine (pâte)

-- Risotto aux champignons
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(4, 17, '300g arborio'),  -- Riz
(4, 22, '300g'),  -- Champignons
(4, 10, '1'),  -- Oignon
(4, 4, '50g'),  -- Beurre
(4, 25, '80g');  -- Parmesan

-- Pâtes carbonara
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(5, 16, '400g spaghetti'),  -- Pâtes
(5, 1, '4 + 2 jaunes'),  -- Oeufs
(5, 25, '100g pecorino'),  -- Parmesan/Pecorino
(5, 7, 'généreusement');  -- Poivre

-- Tiramisu
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(6, 1, '4'),  -- Oeufs
(6, 3, '100g'),  -- Sucre
(6, 33, '1 càc');  -- Vanille

-- Buddha Bowl
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(7, 44, '1'),  -- Avocat
(7, 49, '200g'),  -- Pois chiches
(7, 19, '2');  -- Carottes

-- Curry de pois chiches
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(8, 49, '400g'),  -- Pois chiches
(8, 11, '400g concassées'),  -- Tomates
(8, 39, '2 càs'),  -- Curry
(8, 9, '3 gousses'),  -- Ail
(8, 38, '1 morceau');  -- Gingembre

-- Boeuf bourguignon
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(11, 13, '1.2kg'),  -- Boeuf
(11, 22, '250g'),  -- Champignons
(11, 19, '4'),  -- Carottes
(11, 10, '2');  -- Oignons

-- Poulet rôti
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(12, 12, '1.5kg'),  -- Poulet
(12, 18, '800g'),  -- Pommes de terre
(12, 29, '4 branches'),  -- Thym
(12, 30, '2 branches'),  -- Romarin
(12, 9, '1 tête');  -- Ail

-- Fondant au chocolat
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(14, 32, '200g noir'),  -- Chocolat
(14, 4, '100g'),  -- Beurre
(14, 1, '4'),  -- Oeufs
(14, 3, '80g'),  -- Sucre
(14, 2, '40g');  -- Farine

-- Crème brûlée
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(15, 23, '50cl'),  -- Crème fraîche
(15, 1, '6 jaunes'),  -- Oeufs
(15, 3, '100g'),  -- Sucre
(15, 33, '1 gousse');  -- Vanille

-- Tacos
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(17, 12, '500g'),  -- Poulet
(17, 44, '2'),  -- Avocats
(17, 11, '3'),  -- Tomates
(17, 31, '2'),  -- Citrons
(17, 42, 'fraîche');  -- Coriandre

-- Pad Thaï
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(18, 37, '3 càs'),  -- Sauce soja
(18, 9, '2 gousses'),  -- Ail
(18, 1, '2'),  -- Oeufs
(18, 50, '50g');  -- Noix (cacahuètes)

-- Pancakes
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(20, 2, '200g'),  -- Farine
(20, 5, '25cl'),  -- Lait
(20, 1, '1'),  -- Oeuf
(20, 3, '30g'),  -- Sucre
(20, 34, '1 sachet');  -- Levure

-- Banana bread
INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES
(22, 54, '3 bien mûres'),  -- Bananes
(22, 2, '200g'),  -- Farine
(22, 3, '150g'),  -- Sucre
(22, 50, '80g'),  -- Noix
(22, 4, '80g');  -- Beurre

-- =============================================
-- TAGS DES RECETTES
-- =============================================
INSERT INTO recipe_tag (recipe_id, tag_id) VALUES
-- Quiche Lorraine: Français, Plat principal, Famille
(1, 9), (1, 7), (1, 19),
-- Tarte aux pommes: Dessert, Français, Facile
(2, 6), (2, 9), (2, 5),
-- Soupe à l'oignon: Français, Hiver, Entrée
(3, 9), (3, 16), (3, 8),
-- Risotto: Italien, Plat principal, Végétarien
(4, 10), (4, 7), (4, 1),
-- Carbonara: Italien, Rapide, Plat principal
(5, 10), (5, 4), (5, 7),
-- Tiramisu: Italien, Dessert
(6, 10), (6, 6),
-- Buddha Bowl: Végétarien, Healthy, Facile
(7, 1), (7, 13), (7, 5),
-- Curry pois chiches: Vegan, Healthy, Asiatique
(8, 2), (8, 13), (8, 11),
-- Smoothie bowl: Végétarien, Healthy, Brunch
(9, 1), (9, 13), (9, 17),
-- Boeuf bourguignon: Français, Hiver, Comfort food
(11, 9), (11, 16), (11, 14),
-- Poulet rôti: Français, Famille, Facile
(12, 9), (12, 19), (12, 5),
-- Magret canard: Français, Plat principal
(13, 9), (13, 7),
-- Fondant chocolat: Dessert, Rapide, Facile
(14, 6), (14, 4), (14, 5),
-- Crème brûlée: Dessert, Français
(15, 6), (15, 9),
-- Macarons: Dessert, Français
(16, 6), (16, 9),
-- Tacos: Mexicain, Rapide, Famille
(17, 12), (17, 4), (17, 19),
-- Pad Thaï: Asiatique, Végétarien, Rapide
(18, 11), (18, 1), (18, 4),
-- Sushi: Asiatique, Healthy
(19, 11), (19, 13),
-- Pancakes: Brunch, Facile, Rapide
(20, 17), (20, 5), (20, 4),
-- Overnight oats: Brunch, Healthy, Végétarien
(21, 17), (21, 13), (21, 1),
-- Banana bread: Dessert, Facile, Économique
(22, 6), (22, 5), (22, 20);

-- =============================================
-- NOTES ET AVIS
-- =============================================
INSERT INTO rating (user_id, recipe_id, rating, comment, created_at) VALUES
-- Notes pour la Quiche Lorraine
(3, 1, 5, 'Excellente recette! Ma famille a adoré. La pâte était parfaitement croustillante.', '2026-01-18 12:00:00'),
(4, 1, 4, 'Très bonne quiche, j\'ai ajouté un peu de gruyère râpé sur le dessus.', '2026-01-20 14:30:00'),
(5, 1, 5, 'La meilleure recette de quiche que j\'ai testée! Merci Marie.', '2026-01-22 19:00:00'),

-- Notes pour le Risotto
(2, 4, 5, 'Crémeux à souhait! Le secret c\'est vraiment de remuer sans arrêt.', '2026-01-28 20:00:00'),
(6, 4, 4, 'Délicieux, mais j\'aurais aimé plus de détails sur la quantité de bouillon.', '2026-02-01 13:00:00'),

-- Notes pour les Pâtes carbonara
(2, 5, 5, 'Enfin une vraie carbonara sans crème! Authentique et délicieuse.', '2026-02-08 19:45:00'),
(4, 5, 5, 'J\'ai converti toute ma famille à la vraie recette grâce à vous!', '2026-02-10 12:00:00'),
(8, 5, 4, 'Super recette, un peu technique pour l\'incorporation des oeufs mais le résultat vaut le coup.', '2026-02-12 20:30:00'),

-- Notes pour le Buddha Bowl
(2, 7, 5, 'Coloré, sain et délicieux! Parfait pour mes déjeuners au bureau.', '2026-02-12 11:30:00'),
(3, 7, 4, 'J\'adore le concept, j\'ai varié les légumes selon la saison.', '2026-02-15 12:45:00'),

-- Notes pour le Curry de pois chiches
(5, 8, 5, 'Même mon mari carnivore a adoré! Très parfumé et réconfortant.', '2026-02-18 19:30:00'),
(7, 8, 4, 'Bon curry, j\'ai augmenté les épices pour plus de punch.', '2026-02-20 13:00:00'),

-- Notes pour le Boeuf bourguignon
(2, 11, 5, 'Un classique intemporel! La viande fond dans la bouche après les 2h30 de cuisson.', '2026-02-16 20:00:00'),
(3, 11, 5, 'Recette parfaite pour un dimanche en famille.', '2026-02-19 13:30:00'),
(6, 11, 4, 'Excellent mais demande beaucoup de temps de préparation.', '2026-02-22 19:00:00'),

-- Notes pour le Fondant au chocolat
(2, 14, 5, 'Le coeur coulant parfait! J\'ai chronométré 11 minutes dans mon four.', '2026-02-17 16:00:00'),
(3, 14, 5, 'Impressionnant pour les invités et si simple à faire!', '2026-02-20 20:30:00'),
(5, 14, 4, 'Délicieux, attention au temps de cuisson qui varie selon les fours.', '2026-02-23 15:00:00'),
(7, 14, 5, 'Le dessert préféré de toute la famille maintenant!', '2026-02-25 19:45:00'),

-- Notes pour les Tacos
(2, 17, 4, 'Super marinade pour le poulet! J\'ai ajouté de la sauce piquante.', '2026-03-02 20:00:00'),
(4, 17, 5, 'Meilleur que le restaurant! Le guacamole maison fait toute la différence.', '2026-03-05 19:30:00'),

-- Notes pour le Pad Thaï
(2, 18, 4, 'Bon pad thaï, j\'aurais aimé des conseils sur où trouver la sauce tamarin.', '2026-03-10 13:00:00'),
(5, 18, 5, 'Authentique et savoureux! Mes enfants adorent.', '2026-03-12 19:45:00'),

-- Notes pour les Pancakes
(3, 20, 5, 'Ultra moelleux, la recette parfaite pour le brunch du dimanche!', '2026-03-04 10:30:00'),
(4, 20, 5, 'J\'ai ajouté des pépites de chocolat, un régal!', '2026-03-08 09:00:00'),
(6, 20, 4, 'Très bons pancakes, recette simple et efficace.', '2026-03-11 11:00:00'),

-- Notes pour le Banana bread
(2, 22, 5, 'Le meilleur banana bread que j\'ai fait! Moelleux et parfumé.', '2026-03-17 15:00:00'),
(5, 22, 4, 'Parfait pour utiliser les bananes trop mûres. Ajoutez des pépites de chocolat!', '2026-03-19 11:30:00');

-- =============================================
-- COMMENTAIRES
-- =============================================
INSERT INTO comment (user_id, recipe_id, content, created_at) VALUES
(3, 1, 'Est-ce qu\'on peut remplacer les lardons par du jambon?', '2026-01-19 10:00:00'),
(2, 1, 'Oui bien sûr! Du jambon coupé en dés fonctionne très bien aussi.', '2026-01-19 11:30:00'),
(4, 4, 'Combien de temps se conserve ce risotto au frigo?', '2026-01-30 14:00:00'),
(3, 4, 'Il vaut mieux le manger frais, mais 1-2 jours max au frigo. Ajoutez un peu de bouillon pour réchauffer.', '2026-01-30 15:30:00'),
(5, 5, 'Où trouver du guanciale? C\'est difficile à trouver en France.', '2026-02-09 11:00:00'),
(3, 5, 'En épicerie italienne ou chez un bon charcutier. Sinon la pancetta fonctionne aussi!', '2026-02-09 13:00:00'),
(8, 7, 'J\'adore cette recette! Parfaite pour le meal prep du dimanche.', '2026-02-14 10:00:00'),
(6, 11, 'Peut-on faire cette recette à la cocotte-minute pour gagner du temps?', '2026-02-20 17:00:00'),
(5, 11, 'Oui, comptez environ 45 minutes en cocotte-minute, mais le mijotage long donne vraiment un meilleur résultat.', '2026-02-20 18:30:00'),
(7, 14, 'Quelle marque de chocolat recommandez-vous?', '2026-02-24 14:00:00'),
(6, 14, 'J\'utilise du Valrhona 70% cacao, mais tout bon chocolat pâtissier fonctionne!', '2026-02-24 16:00:00'),
(3, 20, 'Ces pancakes sont incroyables! Toute ma famille est conquise.', '2026-03-06 10:30:00');

-- =============================================
-- FAVORIS
-- =============================================
INSERT INTO favorite (user_id, recipe_id, created_at) VALUES
-- Marie (user_id: 2) aime
(2, 4, '2026-01-28 12:00:00'),  -- Risotto
(2, 5, '2026-02-08 20:00:00'),  -- Carbonara
(2, 11, '2026-02-16 21:00:00'), -- Boeuf bourguignon
(2, 14, '2026-02-17 16:30:00'), -- Fondant chocolat
(2, 20, '2026-03-05 11:00:00'), -- Pancakes

-- Jean (user_id: 3) aime
(3, 1, '2026-01-18 13:00:00'),  -- Quiche Lorraine
(3, 7, '2026-02-15 13:00:00'),  -- Buddha Bowl
(3, 11, '2026-02-19 14:00:00'), -- Boeuf bourguignon
(3, 14, '2026-02-20 21:00:00'), -- Fondant chocolat

-- Sophie (user_id: 4) aime
(4, 1, '2026-01-20 15:00:00'),  -- Quiche Lorraine
(4, 5, '2026-02-10 12:30:00'),  -- Carbonara
(4, 17, '2026-03-05 20:00:00'), -- Tacos
(4, 20, '2026-03-08 09:30:00'), -- Pancakes

-- Pierre (user_id: 5) aime
(5, 1, '2026-01-22 19:30:00'),  -- Quiche Lorraine
(5, 8, '2026-02-18 20:00:00'),  -- Curry pois chiches
(5, 14, '2026-02-23 15:30:00'), -- Fondant chocolat
(5, 18, '2026-03-12 20:00:00'), -- Pad Thai
(5, 22, '2026-03-19 12:00:00'), -- Banana bread

-- Claire (user_id: 6) aime
(6, 4, '2026-02-01 13:30:00'),  -- Risotto
(6, 11, '2026-02-22 19:30:00'), -- Boeuf bourguignon
(6, 20, '2026-03-11 11:30:00'), -- Pancakes

-- Thomas (user_id: 7) aime
(7, 8, '2026-02-20 13:30:00'),  -- Curry pois chiches
(7, 14, '2026-02-25 20:00:00'), -- Fondant chocolat

-- Emma (user_id: 8) aime
(8, 5, '2026-02-12 21:00:00'),  -- Carbonara
(8, 7, '2026-02-14 10:30:00');  -- Buddha Bowl

-- =============================================
-- NOTIFICATIONS
-- =============================================
INSERT INTO notification (user_id, content, is_read, created_at) VALUES
(2, 'Jean Martin a commenté votre recette "Quiche Lorraine traditionnelle"', TRUE, '2026-01-19 10:00:00'),
(2, 'Sophie Bernard a ajouté votre recette "Quiche Lorraine traditionnelle" à ses favoris', TRUE, '2026-01-20 15:00:00'),
(2, 'Nouvelle note 5 étoiles sur votre recette "Tarte aux pommes grand-mère"', TRUE, '2026-01-25 14:00:00'),
(3, 'Marie Dupont a commenté votre recette "Risotto aux champignons"', TRUE, '2026-01-28 12:30:00'),
(3, 'Pierre Durand a demandé des précisions sur votre recette "Pâtes carbonara authentiques"', TRUE, '2026-02-09 11:00:00'),
(4, 'Marie Dupont a ajouté votre recette "Buddha Bowl végétarien" à ses favoris', TRUE, '2026-02-12 11:30:00'),
(4, 'Votre recette "Curry de pois chiches" a atteint 50 vues!', TRUE, '2026-02-20 10:00:00'),
(5, 'Claire Moreau a commenté votre recette "Boeuf bourguignon"', TRUE, '2026-02-20 17:00:00'),
(5, 'Nouvelle note 5 étoiles sur votre recette "Poulet rôti aux herbes"', FALSE, '2026-03-01 12:00:00'),
(6, 'Thomas Petit a demandé une précision sur votre recette "Fondant au chocolat"', TRUE, '2026-02-24 14:00:00'),
(6, 'Votre recette "Fondant au chocolat" est dans le top 5 des recettes les mieux notées!', FALSE, '2026-03-10 09:00:00'),
(7, 'Marie Dupont a commenté votre recette "Tacos au poulet mariné"', FALSE, '2026-03-02 20:30:00'),
(7, 'Sophie Bernard a ajouté votre recette "Tacos au poulet mariné" à ses favoris', FALSE, '2026-03-05 20:00:00'),
(8, 'Jean Martin a noté votre recette "Pancakes moelleux" 5 étoiles!', FALSE, '2026-03-04 10:30:00'),
(8, 'Marie Dupont a ajouté votre recette "Banana bread aux noix" à ses favoris', FALSE, '2026-03-17 15:30:00');

-- =============================================
-- HISTORIQUE DES STATUTS
-- =============================================
INSERT INTO status_history (object_type, object_id, old_status, new_status, changed_by, changed_at) VALUES
('recipe', 1, 'draft', 'published', 2, '2026-01-15 10:30:00'),
('recipe', 2, 'draft', 'published', 2, '2026-01-20 14:00:00'),
('recipe', 3, 'draft', 'published', 2, '2026-02-01 18:00:00'),
('recipe', 4, 'draft', 'published', 3, '2026-01-25 12:00:00'),
('recipe', 5, 'draft', 'published', 3, '2026-02-05 19:30:00'),
('recipe', 6, 'draft', 'published', 3, '2026-02-10 16:00:00'),
('recipe', 7, 'draft', 'published', 4, '2026-02-08 11:00:00'),
('recipe', 8, 'draft', 'published', 4, '2026-02-15 13:00:00'),
('recipe', 9, 'draft', 'published', 4, '2026-02-20 08:30:00'),
('recipe', 11, 'draft', 'published', 5, '2026-02-12 17:00:00'),
('recipe', 12, 'draft', 'published', 5, '2026-02-18 12:30:00'),
('recipe', 13, 'draft', 'published', 5, '2026-02-25 20:00:00'),
('recipe', 14, 'draft', 'published', 6, '2026-02-14 15:00:00'),
('recipe', 15, 'draft', 'published', 6, '2026-02-22 16:30:00'),
('recipe', 16, 'draft', 'published', 6, '2026-03-05 14:00:00'),
('recipe', 17, 'draft', 'published', 7, '2026-02-28 19:00:00'),
('recipe', 18, 'draft', 'published', 7, '2026-03-08 12:00:00'),
('recipe', 19, 'draft', 'published', 7, '2026-03-12 18:00:00'),
('recipe', 20, 'draft', 'published', 8, '2026-03-01 09:00:00'),
('recipe', 21, 'draft', 'published', 8, '2026-03-10 07:30:00'),
('recipe', 22, 'draft', 'published', 8, '2026-03-15 11:00:00');

-- =============================================
-- COLLABORATIONS (invitations)
-- =============================================
INSERT INTO collaboration (recipe_id, user_id, invited_by, status, created_at) VALUES
(1, 3, 2, 'accepted', '2026-01-16 10:00:00'),  -- Jean collabore sur la quiche de Marie
(4, 2, 3, 'accepted', '2026-01-26 14:00:00'),  -- Marie collabore sur le risotto de Jean
(11, 3, 5, 'pending', '2026-02-14 11:00:00'),  -- Jean invité sur le bourguignon de Pierre
(7, 8, 4, 'declined', '2026-02-10 09:00:00'); -- Emma a decline Buddha Bowl de Sophie

-- =============================================
-- MISE A JOUR DES ROLES (a executer apres add_roles.sql)
-- Si la colonne role_id n'existe pas, ces requetes echoueront silencieusement
-- =============================================
UPDATE users SET role_id = 3 WHERE email = 'admin@recetteshare.fr';  -- Admin
UPDATE users SET role_id = 2 WHERE email = 'jean.martin@email.com';  -- Moderateur
UPDATE users SET role_id = 2 WHERE email = 'claire.moreau@email.com';  -- Moderateur
UPDATE users SET role_id = 1 WHERE email IN ('marie.dupont@email.com', 'sophie.bernard@email.com', 'pierre.durand@email.com', 'thomas.petit@email.com', 'emma.leroy@email.com');  -- Users
