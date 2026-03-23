<?php
// Page des favoris
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Mes favoris';
$basePath = '../';

// Verifier l'authentification
if (!isLoggedIn()) {
    redirect('login.php', 'Connectez-vous pour voir vos favoris', 'error');
}

$userId = $_SESSION['user_id'];

// Recuperer les favoris
$stmt = $pdo->prepare('
    SELECT r.*, u.email as author_email, f.created_at as favorited_at,
           (SELECT AVG(rating) FROM rating WHERE recipe_id = r.id) as avg_rating,
           (SELECT COUNT(*) FROM rating WHERE recipe_id = r.id) as rating_count,
           (SELECT COUNT(*) FROM comment WHERE recipe_id = r.id) as comment_count
    FROM favorite f
    JOIN recipe r ON f.recipe_id = r.id
    JOIN users u ON r.user_id = u.id
    WHERE f.user_id = ? AND r.status = "published"
    ORDER BY f.created_at DESC
');
$stmt->execute([$userId]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $recipeId = (int)$_POST['recipe_id'];
    $stmt = $pdo->prepare('DELETE FROM favorite WHERE user_id = ? AND recipe_id = ?');
    $stmt->execute([$userId, $recipeId]);
    redirect('favorites.php', 'Recette retiree des favoris', 'success');
}

require_once '../includes/header.php';
?>

<div class="container">
    <?= displayFlashMessage() ?>
    
    <div class="page-header">
        <h1 class="page-title">Mes favoris</h1>
        <p class="page-subtitle"><?= count($favorites) ?> recette<?= count($favorites) > 1 ? 's' : '' ?> enregistree<?= count($favorites) > 1 ? 's' : '' ?></p>
    </div>
    
    <?php if (empty($favorites)): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">&#9829;</div>
                    <h4 class="empty-state-title">Aucun favori</h4>
                    <p class="empty-state-text">Vous n'avez pas encore ajoute de recettes a vos favoris.</p>
                    <a href="recipes.php" class="btn btn-primary">Decouvrir des recettes</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-4">
            <?php foreach ($favorites as $recipe): ?>
                <?php $tags = getRecipeTags($pdo, $recipe['id']); ?>
                <article class="recipe-card">
                    <a href="recipe.php?id=<?= $recipe['id'] ?>">
                        <div class="recipe-card-placeholder">&#127858;</div>
                    </a>
                    <div class="recipe-card-body">
                        <a href="recipe.php?id=<?= $recipe['id'] ?>">
                            <h3 class="recipe-card-title"><?= htmlspecialchars($recipe['title']) ?></h3>
                        </a>
                        <div class="recipe-card-meta">
                            <span>Par <?= htmlspecialchars(explode('@', $recipe['author_email'])[0]) ?></span>
                        </div>
                        <?php if (!empty($tags)): ?>
                            <div class="recipe-card-tags">
                                <?php foreach (array_slice($tags, 0, 2) as $tag): ?>
                                    <span class="tag"><?= htmlspecialchars($tag['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="recipe-card-footer">
                        <div class="recipe-card-rating">
                            <?php if ($recipe['avg_rating']): ?>
                                <span>&#9733;</span>
                                <span><?= number_format($recipe['avg_rating'], 1) ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Retirer des favoris">&#9829;</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
