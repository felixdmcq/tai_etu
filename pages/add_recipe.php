<?php
// Page d'ajout de recette
include '../includes/db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Créer le dossier d'upload si besoin
$uploadDir = '../uploads/recipes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $user_id = $_SESSION['user_id'];
    $photoName = null;

    // Gestion de l'upload
    if (!empty($_FILES['photo']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['photo']['type'], $allowedTypes) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photoName = uniqid('recette_', true) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoName);
        } else {
            $error = 'Format de photo non supporté (jpg, png, gif, webp)';
        }
    }

    if ($title && !$error) {
        $stmt = $pdo->prepare('INSERT INTO recipe (user_id, title, description, photo) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user_id, $title, $description, $photoName]);
        header('Location: dashboard.php');
        exit;
    } elseif (!$title) {
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
    <form method="post" action="add_recipe.php" enctype="multipart/form-data">
        <label>Titre : <input type="text" name="title" required></label><br>
        <label>Description :<br><textarea name="description"></textarea></label><br>
        <label>Photo : <input type="file" name="photo" accept="image/*"></label><br>
        <button type="submit">Ajouter</button>
    </form>
    <a href="dashboard.php">Retour au tableau de bord</a>
</body>
</html>
