<?php
// Page de gestion des utilisateurs et rôles - Admin uniquement
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Gestion des utilisateurs et rôles';
$basePath = '../';

// Récupérer le rôle de l'utilisateur courant
$currentUserId = $_SESSION['user_id'] ?? null;
if ($currentUserId) {
    $currentUserRole = getUserRole($pdo, $currentUserId); // doit retourner 'admin', 'moderator', 'user', etc.
} else {
    $currentUserRole = null;
}

// Vérifier l'authentification et les droits admin
if (!isLoggedIn() || !($currentUserRole === 'admin' || $currentUserRole === 'moderator')) {
    redirect('../index.php', 'Acces non autorise', 'error');
}

$message = '';
$messageType = 'success';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Changer le rôle d'un utilisateur
    if ($action === 'change_role' && isset($_POST['user_id'], $_POST['role_id'])) {
        $userId = (int)$_POST['user_id'];
        $roleId = (int)$_POST['role_id'];
        
        // Ne pas modifier son propre compte
        if ($userId == $_SESSION['user_id']) {
            $message = 'Vous ne pouvez pas modifier votre propre rôle';
            $messageType = 'error';
        } else {
            // Vérifier que le rôle existe
            $stmt = $pdo->prepare('SELECT id FROM role WHERE id = ?');
            $stmt->execute([$roleId]);
            if ($stmt->fetch()) {
                changeUserRole($pdo, $userId, $roleId);
                $message = 'Rôle de l\'utilisateur mis à jour';
                $messageType = 'success';
                
                // Log de l'action
                logStatusChange($pdo, 'user_role', $userId, 'previous', "role_$roleId", $_SESSION['user_id']);
            } else {
                $message = 'Rôle invalide';
                $messageType = 'error';
            }
        }
    }
}

// Récupérer tous les utilisateurs avec leurs rôles
$users = $pdo->query('
    SELECT u.id, u.email, r.id as role_id, r.name as role,
           (SELECT COUNT(*) FROM recipe WHERE user_id = u.id) as recipe_count,
           (SELECT COUNT(*) FROM comment WHERE user_id = u.id) as comment_count,
           (SELECT COUNT(*) FROM rating WHERE user_id = u.id) as rating_count,
           u.id as is_current
    FROM users u
    LEFT JOIN role r ON u.role_id = r.id
    ORDER BY r.name DESC, u.email ASC
')->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les rôles disponibles
$roles = getAllRoles($pdo);

// Compter les utilisateurs par rôle
$roleStats = [];
foreach ($roles as $role) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role_id = ?');
    $stmt->execute([$role['id']]);
    $roleStats[$role['id']] = [
        'name' => $role['name'],
        'count' => $stmt->fetchColumn()
    ];
}

require_once '../includes/header.php';
?>

<div class="container">
    <?= displayFlashMessage() ?>
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="page-header">
        <h1 class="page-title">Gestion des utilisateurs et rôles</h1>
        <p class="page-subtitle">Administrez les utilisateurs et leurs permissions</p>
    </div>
    
    <!-- Statistiques des rôles -->
    <div class="dashboard-grid">
        <?php foreach ($roleStats as $roleId => $stat): ?>
                <a href="moderation.php" class="btn btn-warning" style="float: right; margin-top: -2.5rem;">🔎 Aller à la modération</a>
            <div class="dashboard-stat">
                <div class="dashboard-stat-icon" style="background: var(--primary-color);">👤</div>
                <div class="dashboard-stat-content">
                    <div class="dashboard-stat-value"><?= $stat['count'] ?></div>
                    <div class="dashboard-stat-label">
                        <?= ucfirst(htmlspecialchars($stat['name'])) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Tableau des utilisateurs -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3 class="card-title">Utilisateurs (<?= count($users) ?>)</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Rôle actuel</th>
                        <th>Recettes</th>
                        <th>Commentaires</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="<?= $user['id'] == $_SESSION['user_id'] ? 'table-highlight' : '' ?>">
                            <td>
                                <strong><?= htmlspecialchars($user['email']) ?></strong>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge badge-primary" style="margin-left: 0.5rem;">Vous</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'moderator' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst(htmlspecialchars($user['role'] ?? 'user')) ?>
                                </span>
                            </td>
                            <td><?= $user['recipe_count'] ?></td>
                            <td><?= $user['comment_count'] ?></td>
                            <td><?= $user['rating_count'] ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <div class="d-flex gap-2">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <select name="role_id" class="form-control" style="width: auto; padding: 0.25rem;" onchange="if(confirm('Êtes-vous sûr de vouloir changer le rôle de cet utilisateur ?')) this.form.submit()">
                                                <option value="">-- Changer le rôle --</option>
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                                        <?= ucfirst(htmlspecialchars($role['name'])) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Informations sur les rôles -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3 class="card-title">À propos des rôles</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div>
                    <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">👤 User</h4>
                    <p class="text-muted">Utilisateur standard. Peut créer, modifier et supprimer ses propres recettes. Peut commenter et noter les recettes d'autres utilisateurs.</p>
                </div>
                <div>
                    <h4 style="color: var(--warning-color); margin-bottom: 0.5rem;">🛡️ Moderator</h4>
                    <p class="text-muted">Modérateur. Dispose des permissions utilisateur + peut signaler/modérer le contenu inapproprié et gérer les signalements.</p>
                </div>
                <div>
                    <h4 style="color: var(--danger-color); margin-bottom: 0.5rem;">👑 Admin</h4>
                    <p class="text-muted">Administrateur. Accès complet au panel d'administration, gestion des utilisateurs et de leurs rôles, modération complète.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 2rem; margin-bottom: 2rem;">
        <a href="admin.php" class="btn btn-secondary">← Retour à l'administration</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
