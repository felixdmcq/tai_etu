<?php
// db.php : connexion à la base de données MySQL
session_start();

$host = 'localhost'; // ou '127.0.0.1' pour local
$dbname = 'tai_etu_felix_domecq_cazaux_v2';
$user = 'tai_etu_felix_domecq_cazaux_v2';
$pass = 'BPV6CRM97N_v2';

// Pour déploiement sur devweb.estia.fr, décommentez la ligne suivante :
// $host = 'mysql-devweb.estia.fr';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}
?>
