    </main>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>RecetteShare</h4>
                <p>Partagez vos meilleures recettes avec la communauté.</p>
            </div>
            <div class="footer-section">
                <h4>Navigation</h4>
                <a href="<?= isset($basePath) ? $basePath : '' ?>index.php">Accueil</a>
                <a href="<?= isset($basePath) ? $basePath : '' ?>pages/recipes.php">Toutes les recettes</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= isset($basePath) ? $basePath : '' ?>pages/recipe_create.php">Ajouter une recette</a>
                <?php endif; ?>
            </div>
            <div class="footer-section">
                <h4>Mon compte</h4>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= isset($basePath) ? $basePath : '' ?>pages/dashboard.php">Tableau de bord</a>
                    <a href="<?= isset($basePath) ? $basePath : '' ?>pages/favorites.php">Mes favoris</a>
                    <a href="<?= isset($basePath) ? $basePath : '' ?>pages/logout.php">Déconnexion</a>
                <?php else: ?>
                    <a href="<?= isset($basePath) ? $basePath : '' ?>pages/login.php">Connexion</a>
                    <a href="<?= isset($basePath) ? $basePath : '' ?>pages/signup.php">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> RecetteShare - TAI ETU Project</p>
        </div>
    </footer>
</body>
</html>
