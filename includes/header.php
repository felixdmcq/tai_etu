<?php
// Header commun pour toutes les pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour vérifier si l'utilisateur est connecté
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

// Fonction pour vérifier si l'utilisateur est admin
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
}

// Récupérer les infos utilisateur si connecté
$currentUser = null;
if (isLoggedIn() && isset($pdo)) {
    $stmt = $pdo->prepare('SELECT id, email FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Compter les notifications non lues
$unreadNotifications = 0;
if (isLoggedIn() && isset($pdo)) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notification WHERE user_id = ? AND is_read = FALSE');
    $stmt->execute([$_SESSION['user_id']]);
    $unreadNotifications = $stmt->fetchColumn();
}

// Déterminer la page active
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>RecetteShare</title>
    <link rel="stylesheet" href="<?= isset($basePath) ? $basePath : '' ?>assets/style.css">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="<?= isset($basePath) ? $basePath : '' ?>index.php" class="logo">
                <span class="logo-icon">&#127858;</span>
                RecetteShare
            </a>
            <div class="nav-links">
                <a href="<?= isset($basePath) ? $basePath : '' ?>index.php" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Accueil</a>
                <a href="<?= isset($basePath) ? $basePath : '' ?>pages/recipes.php" class="<?= $currentPage === 'recipes' ? 'active' : '' ?>">Recettes</a>
                <?php if (isLoggedIn()): ?>
                    <!-- <a href="<?= isset($basePath) ? $basePath : '' ?>pages/recipe_create.php" class="<?= $currentPage === 'recipe_create' ? 'active' : '' ?>">Ajouter</a> -->
                    <!-- <a href="<?= isset($basePath) ? $basePath : '' ?>pages/index.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">Tableau de bord</a> -->
                    <!-- <a href="<?= isset($basePath) ? $basePath : '' ?>pages/notifications.php" class="<?= $currentPage === 'notifications' ? 'active' : '' ?>">
                        Notifications
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="badge badge-open"><?= $unreadNotifications ?></span>
                        <?php endif; ?>
                    </a> -->
                    <?php if (isAdmin()): ?>
                        <a href="<?= isset($basePath) ? $basePath : '' ?>pages/admin.php" class="<?= $currentPage === 'admin' ? 'active' : '' ?>">Admin</a>
                    <?php endif; ?>
                    <a href="<?= isset($basePath) ? $basePath : '' ?>pages/logout.php" class="btn btn-secondary btn-sm">Déconnexion</a>
                <?php else: ?>
                    <a href="<?= isset($basePath) ? $basePath : '' ?>pages/login.php" class="btn btn-secondary btn-sm">Connexion</a>
                    <a href="<?= isset($basePath) ? $basePath : '' ?>pages/signup.php" class="btn btn-primary btn-sm">Inscription</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>
