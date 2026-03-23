<?php
// Tableau de bord utilisateur
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Tableau de bord';
$basePath = '../';

// Verifier l'authentification
if (!isLoggedIn()) {
    redirect('login.php', 'Connectez-vous pour acceder au tableau de bord', 'error');
}

$userId = $_SESSION['user_id'];

// Recuperer les informations utilisateur
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Statistiques
$stmt = $pdo->prepare('SELECT COUNT(*) FROM recipe WHERE user_id = ?');
$stmt->execute([$userId]);
$totalRecipes = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM recipe WHERE user_id = ? AND status = "published"');
$stmt->execute([$userId]);
$publishedRecipes = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM favorite WHERE user_id = ?');
$stmt->execute([$userId]);
$totalFavorites = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM comment WHERE user_id = ?');
$stmt->execute([$userId]);
$totalComments = $stmt->fetchColumn();

// Mes recettes avec leurs stats
$stmt = $pdo->prepare('
    SELECT r.*,
           (SELECT AVG(rating) FROM rating WHERE recipe_id = r.id) as avg_rating,
           (SELECT COUNT(*) FROM rating WHERE recipe_id = r.id) as rating_count,
           (SELECT COUNT(*) FROM comment WHERE recipe_id = r.id) as comment_count,
           (SELECT COUNT(*) FROM favorite WHERE recipe_id = r.id) as favorite_count
    FROM recipe r
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
');
$stmt->execute([$userId]);
$myRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Notifications recentes non lues
$stmt = $pdo->prepare('
    SELECT * FROM notification 
    WHERE user_id = ? AND is_read = FALSE 
    ORDER BY created_at DESC 
    LIMIT 5
');
$stmt->execute([$userId]);
$recentNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Invitations de collaboration en attente
$stmt = $pdo->prepare('
    SELECT c.*, r.title as recipe_title, u.email as inviter_email
    FROM collaboration c
    JOIN recipe r ON c.recipe_id = r.id
    JOIN users u ON c.invited_by = u.id
    WHERE c.user_id = ? AND c.status = "pending"
    ORDER BY c.created_at DESC
');
$stmt->execute([$userId]);
$pendingInvitations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Repondre a une invitation
    if ($action === 'respond_invitation' && isset($_POST['collaboration_id'], $_POST['response'])) {
        $collabId = (int)$_POST['collaboration_id'];
        $response = $_POST['response'] === 'accept' ? 'accepted' : 'declined';
        
        $stmt = $pdo->prepare('UPDATE collaboration SET status = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$response, $collabId, $userId]);
        
        redirect('dashboard.php', 'Reponse enregistree', 'success');
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <?= displayFlashMessage() ?>
    
    <div class="page-header">
        <h1 class="page-title">Tableau de bord</h1>
        <p class="page-subtitle">Bienvenue, <?= htmlspecialchars(explode('@', $user['email'])[0]) ?></p>
    </div>
    
    <!-- Statistiques -->
    <div class="dashboard-grid">
        <div class="dashboard-stat">
            <div class="dashboard-stat-icon recipes">&#127858;</div>
            <div class="dashboard-stat-content">
                <div class="dashboard-stat-value"><?= $totalRecipes ?></div>
                <div class="dashboard-stat-label">Mes recettes</div>
            </div>
        </div>
        <div class="dashboard-stat">
            <div class="dashboard-stat-icon" style="background: var(--success-color);">&#10004;</div>
            <div class="dashboard-stat-content">
                <div class="dashboard-stat-value"><?= $publishedRecipes ?></div>
                <div class="dashboard-stat-label">Publiees</div>
            </div>
        </div>
        <div class="dashboard-stat">
            <div class="dashboard-stat-icon favorites">&#9829;</div>
            <div class="dashboard-stat-content">
                <div class="dashboard-stat-value"><?= $totalFavorites ?></div>
                <div class="dashboard-stat-label">Favoris</div>
            </div>
        </div>
        <div class="dashboard-stat">
            <div class="dashboard-stat-icon comments">&#128172;</div>
            <div class="dashboard-stat-content">
                <div class="dashboard-stat-value"><?= $totalComments ?></div>
                <div class="dashboard-stat-label">Commentaires</div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-3">
        <!-- Mes recettes -->
        <div style="grid-column: span 2;">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Mes recettes</h3>
                    <a href="recipe_create.php" class="btn btn-primary btn-sm">+ Nouvelle recette</a>
                </div>
                
                <?php if (empty($myRecipes)): ?>
                    <div class="card-body">
                        <div class="empty-state">
                            <div class="empty-state-icon">&#127858;</div>
                            <h4 class="empty-state-title">Aucune recette</h4>
                            <p class="empty-state-text">Vous n'avez pas encore cree de recette.</p>
                            <a href="recipe_create.php" class="btn btn-primary">Creer ma premiere recette</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Statut</th>
                                    <th>Note</th>
                                    <th>Stats</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myRecipes as $recipe): ?>
                                    <tr>
                                        <td>
                                            <a href="recipe.php?id=<?= $recipe['id'] ?>">
                                                <?= htmlspecialchars(excerpt($recipe['title'], 40)) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $recipe['status'] ?>">
                                                <?= $recipe['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($recipe['avg_rating']): ?>
                                                <?= number_format($recipe['avg_rating'], 1) ?>/5
                                                <span class="text-muted">(<?= $recipe['rating_count'] ?>)</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span title="Favoris"><?= $recipe['favorite_count'] ?> fav</span>
                                            <span class="text-muted">|</span>
                                            <span title="Commentaires"><?= $recipe['comment_count'] ?> com</span>
                                        </td>
                                        <td class="text-muted"><?= timeAgo($recipe['created_at']) ?></td>
                                        <td>
                                            <a href="recipe.php?id=<?= $recipe['id'] ?>" class="btn btn-secondary btn-sm">Voir</a>
                                            <a href="recipe_edit.php?id=<?= $recipe['id'] ?>" class="btn btn-primary btn-sm">Modifier</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div>
            <!-- Notifications -->
            <div class="card mb-2">
                <div class="card-header">
                    <h4 class="card-title">Notifications</h4>
                    <?php if (count($recentNotifications) > 0): ?>
                        <a href="notifications.php" class="btn btn-secondary btn-sm">Voir tout</a>
                    <?php endif; ?>
                </div>
                <?php if (empty($recentNotifications)): ?>
                    <div class="card-body text-center text-muted">
                        Aucune nouvelle notification
                    </div>
                <?php else: ?>
                    <?php foreach ($recentNotifications as $notif): ?>
                        <div class="notification-item unread">
                            <div class="notification-content">
                                <p class="notification-text"><?= htmlspecialchars($notif['content']) ?></p>
                                <span class="notification-time"><?= timeAgo($notif['created_at']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Invitations de collaboration -->
            <?php if (!empty($pendingInvitations)): ?>
                <div class="card mb-2">
                    <div class="card-header">
                        <h4 class="card-title">Invitations</h4>
                    </div>
                    <?php foreach ($pendingInvitations as $invite): ?>
                        <div class="card-body" style="border-bottom: 1px solid var(--border-color);">
                            <p><strong><?= htmlspecialchars(explode('@', $invite['inviter_email'])[0]) ?></strong> vous invite a collaborer sur :</p>
                            <p class="text-primary"><?= htmlspecialchars($invite['recipe_title']) ?></p>
                            <form method="post" class="d-flex gap-1 mt-1">
                                <input type="hidden" name="action" value="respond_invitation">
                                <input type="hidden" name="collaboration_id" value="<?= $invite['id'] ?>">
                                <button type="submit" name="response" value="accept" class="btn btn-success btn-sm">Accepter</button>
                                <button type="submit" name="response" value="decline" class="btn btn-danger btn-sm">Refuser</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Actions rapides -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Actions rapides</h4>
                </div>
                <div class="card-body">
                    <div class="recipe-actions">
                        <a href="recipe_create.php" class="btn btn-primary" style="width: 100%;">Ajouter une recette</a>
                        <a href="favorites.php" class="btn btn-secondary" style="width: 100%;">Mes favoris</a>
                        <a href="recipes.php" class="btn btn-secondary" style="width: 100%;">Decouvrir des recettes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
