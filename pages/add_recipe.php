<?php
// Page d'ajout de recette
include '../includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $user_id = $_SESSION['user_id'];
    if ($title) {
        $stmt = $pdo->prepare('INSERT INTO recipe (user_id, title, description) VALUES (?, ?, ?)');
        $stmt->execute([$user_id, $title, $description]);
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Le titre est obligatoire.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une recette - TAI ETU</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Ajouter une recette</h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="add_recipe.php">
        <label>Titre : <input type="text" name="title" required></label><br>
        <label>Description :<br><textarea name="description"></textarea></label><br>
        <button type="submit">Ajouter</button>
    </form>
    <a href="dashboard.php">Retour au tableau de bord</a>
</body>
</html>
