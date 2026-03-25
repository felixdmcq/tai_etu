<?php
// Fonctions utilitaires pour l'application

/**
 * Génère un extrait d'un texte
 */
function excerpt($text, $length = 150) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Formate une date en français
 */
function formatDate($date) {
    $timestamp = strtotime($date);
    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 
               'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp) - 1];
    $year = date('Y', $timestamp);
    return "$day $month $year";
}

/**
 * Formate une date relative (il y a X temps)
 */
function timeAgo($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'à l\'instant';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . ' h';
    if ($diff < 604800) return floor($diff / 86400) . ' j';
    if ($diff < 2592000) return floor($diff / 604800) . ' sem';
    return formatDate($date);
}

/**
 * Affiche les étoiles de notation
 */
function displayStars($rating, $max = 5) {
    $html = '<div class="rating">';
    for ($i = 1; $i <= $max; $i++) {
        $filled = $i <= round($rating) ? 'filled' : '';
        $html .= '<span class="star ' . $filled . '">&#9733;</span>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Récupère la note moyenne d'une recette
 */
function getAverageRating($pdo, $recipeId) {
    $stmt = $pdo->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM rating WHERE recipe_id = ?');
    $stmt->execute([$recipeId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Vérifie si une recette est dans les favoris de l'utilisateur
 */
function isFavorite($pdo, $userId, $recipeId) {
    $stmt = $pdo->prepare('SELECT 1 FROM favorite WHERE user_id = ? AND recipe_id = ?');
    $stmt->execute([$userId, $recipeId]);
    return $stmt->fetch() !== false;
}

/**
 * Ajoute une notification
 */
function addNotification($pdo, $userId, $content) {
    $stmt = $pdo->prepare('INSERT INTO notification (user_id, content) VALUES (?, ?)');
    $stmt->execute([$userId, $content]);
}

/**
 * Enregistre un changement de statut
 */
function logStatusChange($pdo, $objectType, $objectId, $oldStatus, $newStatus, $changedBy) {
    $stmt = $pdo->prepare('INSERT INTO status_history (object_type, object_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$objectType, $objectId, $oldStatus, $newStatus, $changedBy]);
}

/**
 * Récupère les tags d'une recette
 */
function getRecipeTags($pdo, $recipeId) {
    $stmt = $pdo->prepare('
        SELECT t.* FROM tag t 
        JOIN recipe_tag rt ON t.id = rt.tag_id 
        WHERE rt.recipe_id = ?
    ');
    $stmt->execute([$recipeId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les ingrédients d'une recette
 */
function getRecipeIngredients($pdo, $recipeId) {
    $stmt = $pdo->prepare('
        SELECT i.name, ri.quantity 
        FROM ingredient i 
        JOIN recipe_ingredient ri ON i.id = ri.ingredient_id 
        WHERE ri.recipe_id = ?
    ');
    $stmt->execute([$recipeId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les étapes d'une recette
 */
function getRecipeSteps($pdo, $recipeId) {
    $stmt = $pdo->prepare('SELECT * FROM step WHERE recipe_id = ? ORDER BY step_number');
    $stmt->execute([$recipeId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère l'auteur d'une recette
 */
function getRecipeAuthor($pdo, $userId) {
    $stmt = $pdo->prepare('SELECT id, email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Compte les commentaires d'une recette
 */
function getCommentCount($pdo, $recipeId) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM comment WHERE recipe_id = ?');
    $stmt->execute([$recipeId]);
    return $stmt->fetchColumn();
}

/**
 * Compte les favoris d'une recette
 */
function getFavoriteCount($pdo, $recipeId) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM favorite WHERE recipe_id = ?');
    $stmt->execute([$recipeId]);
    return $stmt->fetchColumn();
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirection avec message
 */
function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Affiche et efface le message flash
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return '<div class="alert alert-' . $type . '">' . htmlspecialchars($message) . '</div>';
    }
    return '';
}

/**
 * Génère l'initiale pour l'avatar
 */
function getInitial($email) {
    return strtoupper(substr($email, 0, 1));
}
?>
