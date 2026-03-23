<?php
// Tableau de bord utilisateur (exemple)
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - TAI ETU</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <h2>Bienvenue sur votre tableau de bord !</h2>
    <a href="logout.php">Déconnexion</a>
</body>
</html>
