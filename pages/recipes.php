<?php
// Catalogue des recettes avec recherche et filtres
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Toutes les recettes';
$basePath = '../';

// Paramètres de recherche et filtres
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$tagFilter = isset($_GET['tag']) ? (int)$_GET['tag'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Construction de la requête
$whereConditions = ['r.status = "published"'];
$params = [];

if ($search) {
    $whereConditions[] = '(r.title LIKE ? OR r.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($tagFilter) {
    $whereConditions[] = 'r.id IN (SELECT recipe_id FROM recipe_tag WHERE tag_id = ?)';
    $params[] = $tagFilter;
}

$whereClause = implode(' AND ', $whereConditions);

// Tri
$orderClause = 'r.created_at DESC';
switch ($sort) {
    case 'rating':
        $orderClause = 'avg_rating DESC, r.created_at DESC';
        break;
    case 'popular':
        $orderClause = 'favorite_count DESC, r.created_at DESC';
        break;
    case 'oldest':
        $orderClause = 'r.created_at ASC';
        break;
}

// Compter le total
$countStmt = $pdo->prepare("SELECT COUNT(DISTINCT r.id) FROM recipe r WHERE $whereClause");
$countStmt->execute($params);
$totalRecipes = $countStmt->fetchColumn();
$totalPages = ceil($totalRecipes / $perPage);

// Récupérer les recettes
$sql = "
    SELECT r.*, u.email as author_email,
           (SELECT AVG(rating) FROM rating WHERE recipe_id = r.id) as avg_rating,
           (SELECT COUNT(*) FROM rating WHERE recipe_id = r.id) as rating_count,
           (SELECT COUNT(*) FROM comment WHERE recipe_id = r.id) as comment_count,
           (SELECT COUNT(*) FROM favorite WHERE recipe_id = r.id) as favorite_count
    FROM recipe r
    JOIN users u ON r.user_id = u.id
    WHERE $whereClause
    ORDER BY $orderClause
    LIMIT $perPage OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les tags pour le filtre
$allTags = $pdo->query('
    SELECT t.*, COUNT(rt.recipe_id) as recipe_count
    FROM tag t
    JOIN recipe_tag rt ON t.id = rt.tag_id
    JOIN recipe r ON rt.recipe_id = r.id
    WHERE r.status = "published"
    GROUP BY t.id
    ORDER BY recipe_count DESC
')->fetchAll(PDO::FETCH_ASSOC);

// Tag sélectionné
$selectedTag = null;
if ($tagFilter) {
    $stmt = $pdo->prepare('SELECT * FROM tag WHERE id = ?');
    $stmt->execute([$tagFilter]);
    $selectedTag = $stmt->fetch(PDO::FETCH_ASSOC);
}

require_once '../includes/header.php';
?>

<div class="container">
    <?= displayFlashMessage() ?>
    
    <div class="page-header">
        <h1 class="page-title">
            <?php if ($selectedTag): ?>
                Recettes : <?= htmlspecialchars($selectedTag['name']) ?>
            <?php elseif ($search): ?>
                Resultats pour "<?= htmlspecialchars($search) ?>"
            <?php else: ?>
                Toutes les recettes
            <?php endif; ?>
        </h1>
        <p class="page-subtitle"><?= $totalRecipes ?> recette<?= $totalRecipes > 1 ? 's' : '' ?> trouvee<?= $totalRecipes > 1 ? 's' : '' ?></p>
    </div>

    <!-- Barre de recherche et filtres -->
    <form method="get" class="search-bar">
        <div class="search-input-wrapper">
            <span class="search-icon">&#128269;</span>
            <input type="text" name="q" class="form-control" placeholder="Rechercher une recette..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="filters">
            <select name="tag" class="form-control filter-select">
                <option value="">Toutes les categories</option>
                <?php foreach ($allTags as $tag): ?>
                    <option value="<?= $tag['id'] ?>" <?= $tagFilter == $tag['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tag['name']) ?> (<?= $tag['recipe_count'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="sort" class="form-control filter-select">
                <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Plus recentes</option>
                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Plus anciennes</option>
                <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Mieux notees</option>
                <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Plus populaires</option>
            </select>
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <?php if ($search || $tagFilter || $sort !== 'recent'): ?>
                <a href="recipes.php" class="btn btn-secondary">Reinitialiser</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Liste des recettes -->
    <?php if (empty($recipes)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128269;</div>
            <h3 class="empty-state-title">Aucune recette trouvee</h3>
            <p class="empty-state-text">
                <?php if ($search || $tagFilter): ?>
                    Essayez de modifier vos criteres de recherche.
                <?php else: ?>
                    Soyez le premier a partager une recette !
                <?php endif; ?>
            </p>
            <?php if (isLoggedIn()): ?>
                <a href="recipe_create.php" class="btn btn-primary">Ajouter une recette</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid grid-4">
            <?php foreach ($recipes as $recipe): ?>
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
                            <span><?= timeAgo($recipe['created_at']) ?></span>
                        </div>
                        <?php if (!empty($tags)): ?>
                            <div class="recipe-card-tags">
                                <?php foreach (array_slice($tags, 0, 2) as $tag): ?>
                                    <a href="recipes.php?tag=<?= $tag['id'] ?>" class="tag"><?= htmlspecialchars($tag['name']) ?></a>
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Precedent</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Suivant &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
