<?php
// Page de connexion utilisateur
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Connexion';
$basePath = '../';

// Si deja connecte, rediriger
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $stmt = $pdo->prepare('SELECT id, email, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            
            // Verifier si admin (email contient 'admin')
            if (strpos($user['email'], 'admin') !== false) {
                $_SESSION['is_admin'] = true;
            }
            
            redirect('dashboard.php', 'Bienvenue !', 'success');
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}

require_once '../includes/header.php';
?>

<div class="container" style="max-width: 500px; margin-top: 4rem;">
    <?= displayFlashMessage() ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title" style="text-align: center; width: 100%;">Connexion</h2>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($email) ?>" placeholder="votre@email.com" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Votre mot de passe" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
            </form>
        </div>
        <div class="card-footer text-center">
            <p>Pas encore de compte ? <a href="signup.php">Inscrivez-vous</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
