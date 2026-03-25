<?php
// Page d'inscription utilisateur
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pageTitle = 'Inscription';
$basePath = '../';

// Si deja connecte, rediriger
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    // Validation
    if (empty($email)) {
        $errors[] = 'L\'email est obligatoire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'email n\'est pas valide.';
    }
    
    if (empty($password)) {
        $errors[] = 'Le mot de passe est obligatoire.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caracteres.';
    }
    
    if ($password !== $passwordConfirm) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }
    
    // Verifier si l'email existe deja
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Cette adresse email est deja utilisee.';
        }
    }
    
    // Creer le compte
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
        $stmt->execute([$email, $hash]);
        
        redirect('login.php', 'Compte cree avec succes ! Vous pouvez maintenant vous connecter.', 'success');
    }
}

require_once '../includes/header.php';
?>

<div class="container" style="max-width: 500px; margin-top: 4rem;">
    <?= displayFlashMessage() ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title" style="text-align: center; width: 100%;">Creer un compte</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label class="form-label" for="email">Adresse email *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($email) ?>" placeholder="votre@email.com" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe *</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Minimum 6 caracteres" required>
                    <p class="form-hint">Au moins 6 caracteres</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password_confirm">Confirmer le mot de passe *</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" 
                           placeholder="Confirmez votre mot de passe" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">S'inscrire</button>
            </form>
        </div>
        <div class="card-footer text-center">
            <p>Deja un compte ? <a href="login.php">Connectez-vous</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
