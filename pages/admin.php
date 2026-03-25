<?php
// Panel d'administration
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Administration';
$basePath = '../';

// Verifier l'authentification et les droits admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php', 'Acces non autorise', 'error');
}

// Onglet actif
$tab = $_GET['tab'] ?? 'overview';

// Traitement des actions
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Gestion des utilisateurs
    if ($action === 'delete_user' && isset($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];
        if ($userId != $_SESSION['user_id']) {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $message = 'Utilisateur supprime';
        } else {
            $message = 'Vous ne pouvez pas supprimer votre propre compte';
            $messageType = 'error';
        }
    }
    
    // Gestion des tags
    if ($action === 'add_tag' && isset($_POST['tag_name'])) {
        $tagName = trim($_POST['tag_name']);
        if ($tagName) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO tag (name) VALUES (?)');
            $stmt->execute([$tagName]);
            $message = 'Tag ajoute';
        }
    }
    
    if ($action === 'delete_tag' && isset($_POST['tag_id'])) {
        $tagId = (int)$_POST['tag_id'];
        $stmt = $pdo->prepare('DELETE FROM tag WHERE id = ?');
        $stmt->execute([$tagId]);
        $message = 'Tag supprime';
    }
    
    // Gestion des recettes
    if ($action === 'change_recipe_status' && isset($_POST['recipe_id'], $_POST['new_status'])) {
        $recipeId = (int)$_POST['recipe_id'];
        $newStatus = $_POST['new_status'];
        
        if (in_array($newStatus, ['draft', 'published', 'archived'])) {
            $stmt = $pdo->prepare('SELECT status FROM recipe WHERE id = ?');
            $stmt->execute([$recipeId]);
            $oldStatus = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare('UPDATE recipe SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $recipeId]);
            
            logStatusChange($pdo, 'recipe', $recipeId, $oldStatus, $newStatus, $_SESSION['user_id']);
            $message = 'Statut de la recette mis a jour';
        }
    }
    
    if ($action === 'delete_recipe' && isset($_POST['recipe_id'])) {
        $recipeId = (int)$_POST['recipe_id'];
        $stmt = $pdo->prepare('DELETE FROM recipe WHERE id = ?');
        $stmt->execute([$recipeId]);
        $message = 'Recette supprimee';
    }
    
    // Gestion des signalements
    if ($action === 'update_report' && isset($_POST['report_id'], $_POST['new_status'])) {
        $reportId = (int)$_POST['report_id'];
        $newStatus = $_POST['new_status'];
        
        if (in_array($newStatus, ['open', 'reviewed', 'closed'])) {
            $stmt = $pdo->prepare('SELECT status FROM report WHERE id = ?');
            $stmt->execute([$reportId]);
            $oldStatus = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare('UPDATE report SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $reportId]);
            
            logStatusChange($pdo, 'report', $reportId, $oldStatus, $newStatus, $_SESSION['user_id']);
            $message = 'Signalement mis a jour';
        }
    }
}

// Statistiques generales
$stats = [
    'users' => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'recipes' => $pdo->query('SELECT COUNT(*) FROM recipe')->fetchColumn(),
    'recipes_published' => $pdo->query('SELECT COUNT(*) FROM recipe WHERE status = "published"')->fetchColumn(),
    'comments' => $pdo->query('SELECT COUNT(*) FROM comment')->fetchColumn(),
    'ratings' => $pdo->query('SELECT COUNT(*) FROM rating')->fetchColumn(),
    'reports_open' => $pdo->query('SELECT COUNT(*) FROM report WHERE status = "open"')->fetchColumn(),
    'tags' => $pdo->query('SELECT COUNT(*) FROM tag')->fetchColumn(),
];

// Donnees selon l'onglet
$users = [];
$recipes = [];
$reports = [];
$tags = [];

if ($tab === 'users') {
    $users = $pdo->query('
        SELECT u.*, 
               (SELECT COUNT(*) FROM recipe WHERE user_id = u.id) as recipe_count,
               (SELECT COUNT(*) FROM comment WHERE user_id = u.id) as comment_count
        FROM users u
        ORDER BY u.id DESC
    ')->fetchAll(PDO::FETCH_ASSOC);
}

if ($tab === 'recipes') {
    $recipes = $pdo->query('
        SELECT r.*, u.email as author_email,
               (SELECT AVG(rating) FROM rating WHERE recipe_id = r.id) as avg_rating,
               (SELECT COUNT(*) FROM comment WHERE recipe_id = r.id) as comment_count,
               (SELECT COUNT(*) FROM report WHERE recipe_id = r.id AND status = "open") as report_count
        FROM recipe r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC
    ')->fetchAll(PDO::FETCH_ASSOC);
}

if ($tab === 'reports') {
    $reports = $pdo->query('
        SELECT rp.*, u.email as reporter_email, 
               r.title as recipe_title, r.id as recipe_id,
               c.content as comment_content
        FROM report rp
        JOIN users u ON rp.user_id = u.id
        LEFT JOIN recipe r ON rp.recipe_id = r.id
        LEFT JOIN comment c ON rp.comment_id = c.id
        ORDER BY rp.status ASC, rp.created_at DESC
    ')->fetchAll(PDO::FETCH_ASSOC);
}

if ($tab === 'tags') {
    $tags = $pdo->query('
        SELECT t.*, COUNT(rt.recipe_id) as recipe_count
        FROM tag t
        LEFT JOIN recipe_tag rt ON t.id = rt.tag_id
        GROUP BY t.id
        ORDER BY t.name ASC
    ')->fetchAll(PDO::FETCH_ASSOC);
}

require_once '../includes/header.php';
?>

<div class="container">
    <?= displayFlashMessage() ?>
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="page-header">
        <h1 class="page-title">Administration</h1>
        <p class="page-subtitle">Gestion de la plateforme RecetteShare</p>
    </div>
    
    <!-- Navigation onglets -->
    <div class="d-flex gap-1 mb-3" style="border-bottom: 2px solid var(--border-color); padding-bottom: 1rem;">
        <a href="admin.php?tab=overview" class="btn <?= $tab === 'overview' ? 'btn-primary' : 'btn-secondary' ?>">Vue d'ensemble</a>
        <a href="admin.php?tab=users" class="btn <?= $tab === 'users' ? 'btn-primary' : 'btn-secondary' ?>">Utilisateurs (<?= $stats['users'] ?>)</a>
        <a href="admin.php?tab=recipes" class="btn <?= $tab === 'recipes' ? 'btn-primary' : 'btn-secondary' ?>">Recettes (<?= $stats['recipes'] ?>)</a>
        <a href="admin.php?tab=reports" class="btn <?= $tab === 'reports' ? 'btn-primary' : 'btn-secondary' ?>">
            Signalements 
            <?php if ($stats['reports_open'] > 0): ?>
                <span class="badge badge-open"><?= $stats['reports_open'] ?></span>
            <?php endif; ?>
        </a>
        <a href="admin.php?tab=tags" class="btn <?= $tab === 'tags' ? 'btn-primary' : 'btn-secondary' ?>">Tags (<?= $stats['tags'] ?>)</a>
    </div>
    
    <!-- Contenu selon l'onglet -->
    <?php if ($tab === 'overview'): ?>
        <!-- Vue d'ensemble -->
        <div class="dashboard-grid">
            <div class="dashboard-stat">
                <div class="dashboard-stat-icon" style="background: var(--primary-color);">&#128100;</div>
                <div class="dashboard-stat-content">
                    <div class="dashboard-stat-value"><?= $stats['users'] ?></div>
                    <div class="dashboard-stat-label">Utilisateurs</div>
                </div>
            </div>
            <div class="dashboard-stat">
                <div class="dashboard-stat-icon" style="background: var(--success-color);">&#127858;</div>
                <div class="dashboard-stat-content">
                    <div class="dashboard-stat-value"><?= $stats['recipes_published'] ?></div>
                    <div class="dashboard-stat-label">Recettes publiees</div>
                </div>
            </div>
            <div class="dashboard-stat">
                <div class="dashboard-stat-icon" style="background: #3498db;">&#128172;</div>
                <div class="dashboard-stat-content">
                    <div class="dashboard-stat-value"><?= $stats['comments'] ?></div>
                    <div class="dashboard-stat-label">Commentaires</div>
                </div>
            </div>
            <div class="dashboard-stat">
                <div class="dashboard-stat-icon" style="background: var(--warning-color);">&#9733;</div>
                <div class="dashboard-stat-content">
                    <div class="dashboard-stat-value"><?= $stats['ratings'] ?></div>
                    <div class="dashboard-stat-label">Notes</div>
                </div>
            </div>
        </div>
        
        <?php if ($stats['reports_open'] > 0): ?>
            <div class="alert alert-warning">
                <strong>Attention :</strong> Il y a <?= $stats['reports_open'] ?> signalement(s) en attente de traitement.
                <a href="admin.php?tab=reports">Voir les signalements</a>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-2 mt-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recettes recentes</h3>
                </div>
                <div class="card-body">
                    <?php
                    $recentRecipes = $pdo->query('SELECT r.*, u.email FROM recipe r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($recentRecipes)):
                    ?>
                        <p class="text-muted">Aucune recette</p>
                    <?php else: ?>
                        <ul style="list-style: none;">
                            <?php foreach ($recentRecipes as $r): ?>
                                <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                                    <a href="recipe.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></a>
                                    <span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span>
                                    <br><small class="text-muted">par <?= htmlspecialchars(explode('@', $r['email'])[0]) ?> - <?= timeAgo($r['created_at']) ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Derniers utilisateurs</h3>
                </div>
                <div class="card-body">
                    <?php
                    $recentUsers = $pdo->query('SELECT * FROM users ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($recentUsers)):
                    ?>
                        <p class="text-muted">Aucun utilisateur</p>
                    <?php else: ?>
                        <ul style="list-style: none;">
                            <?php foreach ($recentUsers as $u): ?>
                                <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                                    <?= htmlspecialchars($u['email']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    
    <?php elseif ($tab === 'users'): ?>
        <!-- Gestion des utilisateurs -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Utilisateurs</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Recettes</th>
                            <th>Commentaires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= $user['recipe_count'] ?></td>
                                <td><?= $user['comment_count'] ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet utilisateur et tout son contenu ?')">Supprimer</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">(Vous)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    
    <?php elseif ($tab === 'recipes'): ?>
        <!-- Gestion des recettes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recettes</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th>Note</th>
                            <th>Signalements</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipes as $recipe): ?>
                            <tr>
                                <td>
                                    <a href="recipe.php?id=<?= $recipe['id'] ?>"><?= htmlspecialchars(excerpt($recipe['title'], 30)) ?></a>
                                </td>
                                <td><?= htmlspecialchars(explode('@', $recipe['author_email'])[0]) ?></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="change_recipe_status">
                                        <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                        <select name="new_status" class="form-control" style="width: auto; padding: 0.25rem;" onchange="this.form.submit()">
                                            <option value="draft" <?= $recipe['status'] === 'draft' ? 'selected' : '' ?>>draft</option>
                                            <option value="published" <?= $recipe['status'] === 'published' ? 'selected' : '' ?>>published</option>
                                            <option value="archived" <?= $recipe['status'] === 'archived' ? 'selected' : '' ?>>archived</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($recipe['avg_rating']): ?>
                                        <?= number_format($recipe['avg_rating'], 1) ?>/5
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($recipe['report_count'] > 0): ?>
                                        <span class="badge badge-open"><?= $recipe['report_count'] ?></span>
                                    <?php else: ?>
                                        0
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= timeAgo($recipe['created_at']) ?></td>
                                <td>
                                    <a href="recipe.php?id=<?= $recipe['id'] ?>" class="btn btn-secondary btn-sm">Voir</a>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_recipe">
                                        <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette recette ?')">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    
    <?php elseif ($tab === 'reports'): ?>
        <!-- Gestion des signalements -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Signalements</h3>
            </div>
            <?php if (empty($reports)): ?>
                <div class="card-body text-center text-muted">
                    Aucun signalement
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Contenu signale</th>
                                <th>Raison</th>
                                <th>Par</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr class="<?= $report['status'] === 'open' ? 'alert-warning' : '' ?>">
                                    <td><?= $report['id'] ?></td>
                                    <td><?= $report['recipe_id'] ? 'Recette' : 'Commentaire' ?></td>
                                    <td>
                                        <?php if ($report['recipe_id']): ?>
                                            <a href="recipe.php?id=<?= $report['recipe_id'] ?>"><?= htmlspecialchars(excerpt($report['recipe_title'] ?? 'Supprimee', 30)) ?></a>
                                        <?php else: ?>
                                            <?= htmlspecialchars(excerpt($report['comment_content'] ?? 'Supprime', 30)) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($report['reason']) ?></td>
                                    <td><?= htmlspecialchars(explode('@', $report['reporter_email'])[0]) ?></td>
                                    <td>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="update_report">
                                            <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                            <select name="new_status" class="form-control" style="width: auto; padding: 0.25rem;" onchange="this.form.submit()">
                                                <option value="open" <?= $report['status'] === 'open' ? 'selected' : '' ?>>open</option>
                                                <option value="reviewed" <?= $report['status'] === 'reviewed' ? 'selected' : '' ?>>reviewed</option>
                                                <option value="closed" <?= $report['status'] === 'closed' ? 'selected' : '' ?>>closed</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-muted"><?= timeAgo($report['created_at']) ?></td>
                                    <td>
                                        <?php if ($report['recipe_id']): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_recipe">
                                                <input type="hidden" name="recipe_id" value="<?= $report['recipe_id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette recette ?')">Suppr. recette</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    
    <?php elseif ($tab === 'tags'): ?>
        <!-- Gestion des tags -->
        <div class="grid grid-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ajouter un tag</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="add_tag">
                        <div class="form-row">
                            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                                <input type="text" name="tag_name" class="form-control" placeholder="Nom du tag" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tags existants</h3>
                </div>
                <?php if (empty($tags)): ?>
                    <div class="card-body text-center text-muted">
                        Aucun tag
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Recettes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tags as $tag): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tag['name']) ?></td>
                                        <td><?= $tag['recipe_count'] ?></td>
                                        <td>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_tag">
                                                <input type="hidden" name="tag_id" value="<?= $tag['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce tag ?')">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
