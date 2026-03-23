<?php
// Page d'accueil - RecetteShare
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Accueil';
$basePath = '';

// Récupérer les recettes publiées récentes
$stmt = $pdo->query('
    SELECT r.*, u.email as author_email,
           (SELECT AVG(rating) FROM rating WHERE recipe_id = r.id) as avg_rating,
           (SELECT COUNT(*) FROM rating WHERE recipe_id = r.id) as rating_count,
           (SELECT COUNT(*) FROM comment WHERE recipe_id = r.id) as comment_count
    FROM recipe r
    JOIN users u ON r.user_id = u.id
    WHERE r.status = "published"
    ORDER BY r.created_at DESC
    LIMIT 8
');
$recentRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les recettes les mieux notées
$stmt = $pdo->query('
    SELECT r.*, u.email as author_email,
           (SELECT AVG(rating) FROM rating WHERE recipe_id = r.id) as avg_rating,
           (SELECT COUNT(*) FROM rating WHERE recipe_id = r.id) as rating_count
    FROM recipe r
    JOIN users u ON r.user_id = u.id
    WHERE r.status = "published"
    HAVING avg_rating IS NOT NULL
    ORDER BY avg_rating DESC
    LIMIT 4
');
$topRatedRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les tags populaires
$stmt = $pdo->query('
    SELECT t.*, COUNT(rt.recipe_id) as recipe_count
    FROM tag t
    JOIN recipe_tag rt ON t.id = rt.tag_id
    JOIN recipe r ON rt.recipe_id = r.id
    WHERE r.status = "published"
    GROUP BY t.id
    ORDER BY recipe_count DESC
    LIMIT 10
');
$popularTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats globales
$totalRecipes = $pdo->query('SELECT COUNT(*) FROM recipe WHERE status = "published"')->fetchColumn();
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

require_once 'includes/header.php';
?>

<div class="container">
    <?= displayFlashMessage() ?>
    
    <!-- Hero Section -->
    <section class="hero" style="text-align: center; padding: 4rem 2rem; background: linear-gradient(135deg, var(--primary-color), #d35400); border-radius: var(--radius); color: white; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Partagez vos meilleures recettes</h1>
        <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem;">Rejoignez notre communauté de <?= $totalUsers ?> passionnes et decouvrez <?= $totalRecipes ?> recettes</p>
        <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
            <a href="pages/recipes.php" class="btn btn-secondary">Parcourir les recettes</a>
            <?php if (!isLoggedIn()): ?>
                <a href="pages/signup.php" class="btn" style="background: white; color: var(--primary-color);">Creer un compte</a>
            <?php else: ?>
                <a href="pages/recipe_create.php" class="btn" style="background: white; color: var(--primary-color);">Ajouter une recette</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Tags populaires -->
    <?php if (!empty($popularTags)): ?>
    <section class="mb-4">
        <h2 class="page-title mb-2">Categories populaires</h2>
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
            <?php foreach ($popularTags as $tag): ?>
                <a href="pages/recipes.php?tag=<?= $tag['id'] ?>" class="tag tag-primary" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                    <?= htmlspecialchars($tag['name']) ?> (<?= $tag['recipe_count'] ?>)
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Recettes récentes -->
    <section class="mb-4">
        <div class="d-flex justify-between align-center mb-2">
            <h2 class="page-title">Recettes recentes</h2>
            <a href="pages/recipes.php" class="btn btn-secondary btn-sm">Voir tout</a>
        </div>
        
        <?php if (empty($recentRecipes)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#127858;</div>
                <h3 class="empty-state-title">Aucune recette pour le moment</h3>
                <p class="empty-state-text">Soyez le premier a partager une recette avec la communaute !</p>
                <?php if (isLoggedIn()): ?>
                    <a href="pages/recipe_create.php" class="btn btn-primary">Ajouter une recette</a>
                <?php else: ?>
                    <a href="pages/signup.php" class="btn btn-primary">Creer un compte</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-4">
                <?php foreach ($recentRecipes as $recipe): ?>
                    <?php $tags = getRecipeTags($pdo, $recipe['id']); ?>
                    <article class="recipe-card">
                        <a href="pages/recipe.php?id=<?= $recipe['id'] ?>">
                            <div class="recipe-card-placeholder">&#127858;</div>
                        </a>
                        <div class="recipe-card-body">
                            <a href="pages/recipe.php?id=<?= $recipe['id'] ?>">
                                <h3 class="recipe-card-title"><?= htmlspecialchars($recipe['title']) ?></h3>
                            </a>
                            <div class="recipe-card-meta">
                                <span>Par <?= htmlspecialchars(explode('@', $recipe['author_email'])[0]) ?></span>
                                <span><?= timeAgo($recipe['created_at']) ?></span>
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
                                    <span class="text-muted">(<?= $recipe['rating_count'] ?>)</span>
                                <?php else: ?>
                                    <span class="text-muted">Pas encore note</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-muted"><?= $recipe['comment_count'] ?> com.</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Meilleures recettes -->
    <?php if (!empty($topRatedRecipes)): ?>
    <section class="mb-4">
        <div class="d-flex justify-between align-center mb-2">
            <h2 class="page-title">Recettes les mieux notees</h2>
            <a href="pages/recipes.php?sort=rating" class="btn btn-secondary btn-sm">Voir tout</a>
        </div>
        
        <div class="grid grid-4">
            <?php foreach ($topRatedRecipes as $recipe): ?>
                <article class="recipe-card">
                    <a href="pages/recipe.php?id=<?= $recipe['id'] ?>">
                        <div class="recipe-card-placeholder">&#9733;</div>
                    </a>
                    <div class="recipe-card-body">
                        <a href="pages/recipe.php?id=<?= $recipe['id'] ?>">
                            <h3 class="recipe-card-title"><?= htmlspecialchars($recipe['title']) ?></h3>
                        </a>
                        <div class="recipe-card-meta">
                            <span>Par <?= htmlspecialchars(explode('@', $recipe['author_email'])[0]) ?></span>
                        </div>
                    </div>
                    <div class="recipe-card-footer">
                        <div class="recipe-card-rating">
                            <span>&#9733;</span>
                            <span><?= number_format($recipe['avg_rating'], 1) ?></span>
                            <span class="text-muted">(<?= $recipe['rating_count'] ?>)</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Call to action -->
    <?php if (!isLoggedIn()): ?>
    <section style="text-align: center; padding: 3rem 2rem; background: var(--bg-white); border-radius: var(--radius); box-shadow: var(--shadow);">
        <h2 style="margin-bottom: 1rem;">Rejoignez la communaute</h2>
        <p class="text-muted" style="margin-bottom: 1.5rem;">Creez un compte pour partager vos recettes, ajouter des favoris et interagir avec les autres membres.</p>
        <a href="pages/signup.php" class="btn btn-primary">S'inscrire gratuitement</a>
    </section>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
