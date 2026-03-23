<?php
// Tableau de bord utilisateur (exemple)
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include '../includes/db.php';
$user_id = $_SESSION['user_id'];
// Récupérer les recettes de l'utilisateur connecté
$stmt = $pdo->prepare('SELECT * FROM recipe WHERE user_id = ?');
$stmt->execute([$user_id]);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - TAI ETU</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Bienvenue sur votre tableau de bord !</h2>
    <a href="logout.php">Déconnexion</a>
    <a href="add_recipe.php">Ajouter une recette</a>
    <h3>Vos recettes :</h3>
    <?php if ($recipes): ?>
        <ul>
        <?php foreach ($recipes as $recipe): ?>
            <li><?= htmlspecialchars($recipe['title']) ?> (<?= htmlspecialchars($recipe['status']) ?>)</li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Vous n'avez pas encore de recette.</p>
    <?php endif; ?>
</body>
</html>
