<?php
// Suppression d'une recette
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Verifier l'authentification
if (!isLoggedIn()) {
    redirect('login.php', 'Connectez-vous pour supprimer une recette', 'error');
}

// Verifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php', 'Recette non trouvee', 'error');
}

$recipeId = (int)$_GET['id'];

// Recuperer la recette
$stmt = $pdo->prepare('SELECT * FROM recipe WHERE id = ?');
$stmt->execute([$recipeId]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    redirect('index.php', 'Recette non trouvee', 'error');
}

// Verifier que l'utilisateur est le proprietaire ou admin
if ($recipe['user_id'] != $_SESSION['user_id'] && !isAdmin()) {
    redirect('index.php', 'Vous n\'avez pas la permission de supprimer cette recette', 'error');
}

// Supprimer la recette (les cascades s'occupent du reste)
try {
    $stmt = $pdo->prepare('DELETE FROM recipe WHERE id = ?');
    $stmt->execute([$recipeId]);
    
    redirect('index.php', 'Recette supprimee avec succes', 'success');
} catch (Exception $e) {
    redirect('index.php', 'Erreur lors de la suppression', 'error');
}
?>
