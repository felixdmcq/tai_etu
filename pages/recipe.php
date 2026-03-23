<?php
// Page de detail d'une recette
require_once '../includes/db.php';
require_once '../includes/functions.php';

$basePath = '../';

// Verifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('recipes.php', 'Recette non trouvee', 'error');
}

$recipeId = (int)$_GET['id'];

// Recuperer la recette
$stmt = $pdo->prepare('
    SELECT r.*, u.email as author_email, u.id as author_id
    FROM recipe r
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ?
');
$stmt->execute([$recipeId]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    redirect('recipes.php', 'Recette non trouvee', 'error');
}

// Verifier les permissions (publie ou proprietaire)
$isOwner = isLoggedIn() && $_SESSION['user_id'] == $recipe['author_id'];
if ($recipe['status'] !== 'published' && !$isOwner && !isAdmin()) {
    redirect('recipes.php', 'Cette recette n\'est pas disponible', 'error');
}

$pageTitle = $recipe['title'];

// Recuperer les donnees associees
$ingredients = getRecipeIngredients($pdo, $recipeId);
$steps = getRecipeSteps($pdo, $recipeId);
$tags = getRecipeTags($pdo, $recipeId);
$ratingData = getAverageRating($pdo, $recipeId);
$commentCount = getCommentCount($pdo, $recipeId);
$favoriteCount = getFavoriteCount($pdo, $recipeId);

// Verifier si l'utilisateur a mis en favori
$isFav = isLoggedIn() ? isFavorite($pdo, $_SESSION['user_id'], $recipeId) : false;

// Verifier si l'utilisateur a deja note
$userRating = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare('SELECT rating FROM rating WHERE user_id = ? AND recipe_id = ?');
    $stmt->execute([$_SESSION['user_id'], $recipeId]);
    $userRating = $stmt->fetchColumn();
}

// Recuperer les commentaires
$stmt = $pdo->prepare('
    SELECT c.*, u.email as author_email
    FROM comment c
    JOIN users u ON c.user_id = u.id
    WHERE c.recipe_id = ?
    ORDER BY c.created_at DESC
');
$stmt->execute([$recipeId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement des actions POST
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $action = $_POST['action'] ?? '';
    
    // Ajouter/Retirer des favoris
    if ($action === 'toggle_favorite') {
        if ($isFav) {
            $stmt = $pdo->prepare('DELETE FROM favorite WHERE user_id = ? AND recipe_id = ?');
            $stmt->execute([$_SESSION['user_id'], $recipeId]);
            $isFav = false;
            $favoriteCount--;
            $message = 'Recette retiree des favoris';
        } else {
            $stmt = $pdo->prepare('INSERT INTO favorite (user_id, recipe_id) VALUES (?, ?)');
            $stmt->execute([$_SESSION['user_id'], $recipeId]);
            $isFav = true;
            $favoriteCount++;
            $message = 'Recette ajoutee aux favoris';
            
            // Notification au proprietaire
            if ($recipe['author_id'] != $_SESSION['user_id']) {
                addNotification($pdo, $recipe['author_id'], 'Quelqu\'un a ajoute votre recette "' . $recipe['title'] . '" a ses favoris');
            }
        }
    }
    
    // Ajouter une note
    if ($action === 'rate' && isset($_POST['rating'])) {
        $newRating = max(1, min(5, (int)$_POST['rating']));
        
        if ($userRating) {
            $stmt = $pdo->prepare('UPDATE rating SET rating = ? WHERE user_id = ? AND recipe_id = ?');
            $stmt->execute([$newRating, $_SESSION['user_id'], $recipeId]);
            $message = 'Note mise a jour';
        } else {
            $stmt = $pdo->prepare('INSERT INTO rating (user_id, recipe_id, rating) VALUES (?, ?, ?)');
            $stmt->execute([$_SESSION['user_id'], $recipeId, $newRating]);
            $message = 'Merci pour votre note !';
            
            // Notification au proprietaire
            if ($recipe['author_id'] != $_SESSION['user_id']) {
                addNotification($pdo, $recipe['author_id'], 'Quelqu\'un a note votre recette "' . $recipe['title'] . '"');
            }
        }
        $userRating = $newRating;
        $ratingData = getAverageRating($pdo, $recipeId);
    }
    
    // Ajouter un commentaire
    if ($action === 'comment' && isset($_POST['content']) && trim($_POST['content'])) {
        $content = trim($_POST['content']);
        $stmt = $pdo->prepare('INSERT INTO comment (user_id, recipe_id, content) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $recipeId, $content]);
        $message = 'Commentaire ajoute';
        
        // Notification au proprietaire
        if ($recipe['author_id'] != $_SESSION['user_id']) {
            addNotification($pdo, $recipe['author_id'], 'Nouveau commentaire sur votre recette "' . $recipe['title'] . '"');
        }
        
        // Rafraichir les commentaires
        $stmt = $pdo->prepare('
            SELECT c.*, u.email as author_email
            FROM comment c
            JOIN users u ON c.user_id = u.id
            WHERE c.recipe_id = ?
            ORDER BY c.created_at DESC
        ');
        $stmt->execute([$recipeId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $commentCount = count($comments);
    }
    
    // Supprimer un commentaire
    if ($action === 'delete_comment' && isset($_POST['comment_id'])) {
        $commentId = (int)$_POST['comment_id'];
        
        // Verifier que l'utilisateur est le proprietaire du commentaire ou admin
        $stmt = $pdo->prepare('SELECT user_id FROM comment WHERE id = ?');
        $stmt->execute([$commentId]);
        $commentOwnerId = $stmt->fetchColumn();
        
        if ($commentOwnerId == $_SESSION['user_id'] || isAdmin()) {
            $stmt = $pdo->prepare('DELETE FROM comment WHERE id = ?');
            $stmt->execute([$commentId]);
            $message = 'Commentaire supprime';
            
            // Rafraichir les commentaires
            $stmt = $pdo->prepare('
                SELECT c.*, u.email as author_email
                FROM comment c
                JOIN users u ON c.user_id = u.id
                WHERE c.recipe_id = ?
                ORDER BY c.created_at DESC
            ');
            $stmt->execute([$recipeId]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $commentCount = count($comments);
        }
    }
    
    // Signaler la recette
    if ($action === 'report' && isset($_POST['reason']) && trim($_POST['reason'])) {
        $reason = trim($_POST['reason']);
        $stmt = $pdo->prepare('INSERT INTO report (user_id, recipe_id, reason) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $recipeId, $reason]);
        $message = 'Signalement envoye. Merci de votre vigilance.';
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <?= displayFlashMessage() ?>
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <!-- Navigation -->
    <div class="mb-2">
        <a href="recipes.php" class="btn btn-secondary btn-sm">&larr; Retour aux recettes</a>
    </div>
    
    <!-- Status badge si pas publie -->
    <?php if ($recipe['status'] !== 'published'): ?>
        <div class="alert alert-warning mb-2">
            Cette recette est en statut <strong><?= $recipe['status'] ?></strong> et n'est visible que par vous.
        </div>
    <?php endif; ?>
    
    <div class="recipe-detail">
        <!-- Contenu principal -->
        <div class="recipe-main">
            <div class="recipe-header">
                <h1 class="recipe-title"><?= htmlspecialchars($recipe['title']) ?></h1>
                
                <div class="recipe-meta">
                    <span class="recipe-meta-item">
                        Par <strong><?= htmlspecialchars(explode('@', $recipe['author_email'])[0]) ?></strong>
                    </span>
                    <span class="recipe-meta-item">
                        <?= formatDate($recipe['created_at']) ?>
                    </span>
                    <?php if ($ratingData['count'] > 0): ?>
                        <span class="recipe-meta-item">
                            <?= number_format($ratingData['avg_rating'], 1) ?>/5 (<?= $ratingData['count'] ?> avis)
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($tags)): ?>
                    <div class="recipe-card-tags">
                        <?php foreach ($tags as $tag): ?>
                            <a href="recipes.php?tag=<?= $tag['id'] ?>" class="tag tag-primary"><?= htmlspecialchars($tag['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="recipe-image-placeholder">&#127858;</div>
            
            <?php if ($recipe['description']): ?>
                <p class="recipe-description"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
            <?php endif; ?>
            
            <!-- Ingredients -->
            <?php if (!empty($ingredients)): ?>
                <section class="recipe-section">
                    <h2 class="recipe-section-title">Ingredients</h2>
                    <ul class="ingredients-list">
                        <?php foreach ($ingredients as $ing): ?>
                            <li>
                                <span class="ingredient-name"><?= htmlspecialchars($ing['name']) ?></span>
                                <?php if ($ing['quantity']): ?>
                                    <span class="ingredient-quantity"><?= htmlspecialchars($ing['quantity']) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
            
            <!-- Etapes -->
            <?php if (!empty($steps)): ?>
                <section class="recipe-section">
                    <h2 class="recipe-section-title">Preparation</h2>
                    <ol class="steps-list">
                        <?php foreach ($steps as $step): ?>
                            <li><?= nl2br(htmlspecialchars($step['content'])) ?></li>
                        <?php endforeach; ?>
                    </ol>
                </section>
            <?php endif; ?>
            
            <!-- Section commentaires -->
            <section class="comments-section card">
                <div class="card-header">
                    <h3 class="card-title">Commentaires (<?= $commentCount ?>)</h3>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <form method="post" class="comment-form">
                        <input type="hidden" name="action" value="comment">
                        <div class="form-group mb-2">
                            <textarea name="content" class="form-control" placeholder="Ajouter un commentaire..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Publier</button>
                    </form>
                <?php else: ?>
                    <div class="card-body">
                        <p class="text-muted"><a href="login.php">Connectez-vous</a> pour laisser un commentaire.</p>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($comments)): ?>
                    <div class="card-body text-center text-muted">
                        Aucun commentaire pour le moment. Soyez le premier !
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-avatar"><?= getInitial($comment['author_email']) ?></div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author"><?= htmlspecialchars(explode('@', $comment['author_email'])[0]) ?></span>
                                    <span class="comment-date"><?= timeAgo($comment['created_at']) ?></span>
                                </div>
                                <p class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                <?php if (isLoggedIn() && ($comment['user_id'] == $_SESSION['user_id'] || isAdmin())): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_comment">
                                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
        
        <!-- Sidebar -->
        <aside class="recipe-sidebar">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Actions</h4>
                </div>
                <div class="card-body">
                    <div class="recipe-actions">
                        <?php if (isLoggedIn()): ?>
                            <form method="post">
                                <input type="hidden" name="action" value="toggle_favorite">
                                <button type="submit" class="btn <?= $isFav ? 'btn-danger' : 'btn-secondary' ?>" style="width: 100%;">
                                    <?= $isFav ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>
                                </button>
                            </form>
                            
                            <?php if ($isOwner): ?>
                                <a href="recipe_edit.php?id=<?= $recipeId ?>" class="btn btn-primary" style="width: 100%;">Modifier</a>
                                <a href="recipe_delete.php?id=<?= $recipeId ?>" class="btn btn-danger" style="width: 100%;" onclick="return confirm('Supprimer cette recette ?')">Supprimer</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary" style="width: 100%;">Connectez-vous pour interagir</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Statistiques -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Statistiques</h4>
                </div>
                <div class="card-body">
                    <div class="recipe-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?= $ratingData['count'] > 0 ? number_format($ratingData['avg_rating'], 1) : '-' ?></div>
                            <div class="stat-label">Note moyenne</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $ratingData['count'] ?></div>
                            <div class="stat-label">Avis</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $favoriteCount ?></div>
                            <div class="stat-label">Favoris</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $commentCount ?></div>
                            <div class="stat-label">Commentaires</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Noter la recette -->
            <?php if (isLoggedIn() && !$isOwner): ?>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Noter cette recette</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="rate">
                            <div class="rating-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $userRating == $i ? 'checked' : '' ?>>
                                    <label for="star<?= $i ?>">&#9733;</label>
                                <?php endfor; ?>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-2" style="width: 100%;">
                                <?= $userRating ? 'Mettre a jour' : 'Noter' ?>
                            </button>
                        </form>
                        <?php if ($userRating): ?>
                            <p class="text-muted text-center mt-1">Votre note : <?= $userRating ?>/5</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Signaler -->
            <?php if (isLoggedIn() && !$isOwner): ?>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Signaler</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="report">
                            <div class="form-group">
                                <select name="reason" class="form-control" required>
                                    <option value="">Choisir une raison</option>
                                    <option value="Contenu inapproprie">Contenu inapproprie</option>
                                    <option value="Spam">Spam</option>
                                    <option value="Droits d'auteur">Droits d'auteur</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">Signaler</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
