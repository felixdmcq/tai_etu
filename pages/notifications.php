<?php
// Centre de notifications
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Notifications';
$basePath = '../';

// Verifier l'authentification
if (!isLoggedIn()) {
    redirect('login.php', 'Connectez-vous pour voir vos notifications', 'error');
}

$userId = $_SESSION['user_id'];

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'mark_read' && isset($_POST['notification_id'])) {
        $notifId = (int)$_POST['notification_id'];
        $stmt = $pdo->prepare('UPDATE notification SET is_read = TRUE WHERE id = ? AND user_id = ?');
        $stmt->execute([$notifId, $userId]);
    }
    
    if ($action === 'mark_all_read') {
        $stmt = $pdo->prepare('UPDATE notification SET is_read = TRUE WHERE user_id = ?');
        $stmt->execute([$userId]);
        redirect('notifications.php', 'Toutes les notifications ont ete marquees comme lues', 'success');
    }
    
    if ($action === 'delete' && isset($_POST['notification_id'])) {
        $notifId = (int)$_POST['notification_id'];
        $stmt = $pdo->prepare('DELETE FROM notification WHERE id = ? AND user_id = ?');
        $stmt->execute([$notifId, $userId]);
    }
    
    if ($action === 'delete_all') {
        $stmt = $pdo->prepare('DELETE FROM notification WHERE user_id = ?');
        $stmt->execute([$userId]);
        redirect('notifications.php', 'Toutes les notifications ont ete supprimees', 'success');
    }
}

// Recuperer les notifications
$stmt = $pdo->prepare('
    SELECT * FROM notification 
    WHERE user_id = ? 
    ORDER BY is_read ASC, created_at DESC
');
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter les non lues
$unreadCount = 0;
foreach ($notifications as $n) {
    if (!$n['is_read']) $unreadCount++;
}

require_once '../includes/header.php';
?>

<div class="container">
    <?= displayFlashMessage() ?>
    
    <div class="page-header d-flex justify-between align-center">
        <div>
            <h1 class="page-title">Notifications</h1>
            <p class="page-subtitle"><?= $unreadCount ?> non lue<?= $unreadCount > 1 ? 's' : '' ?></p>
        </div>
        <?php if (!empty($notifications)): ?>
            <div class="d-flex gap-1">
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn btn-secondary btn-sm">Tout marquer comme lu</button>
                </form>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="delete_all">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer toutes les notifications ?')">Tout supprimer</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <?php if (empty($notifications)): ?>
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">&#128276;</div>
                    <h4 class="empty-state-title">Aucune notification</h4>
                    <p class="empty-state-text">Vous n'avez pas encore de notifications.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notification-item <?= !$notif['is_read'] ? 'unread' : '' ?>">
                    <div class="notification-icon">&#128276;</div>
                    <div class="notification-content">
                        <p class="notification-text"><?= htmlspecialchars($notif['content']) ?></p>
                        <span class="notification-time"><?= formatDate($notif['created_at']) ?> - <?= timeAgo($notif['created_at']) ?></span>
                    </div>
                    <div class="d-flex gap-1">
                        <?php if (!$notif['is_read']): ?>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                                <button type="submit" class="btn btn-secondary btn-sm" title="Marquer comme lu">&#10004;</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">X</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
