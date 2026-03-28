<?php
// Configuration des paramètres de connexion à la base de données
$host = "localhost";
$dbname = "r401_auth";
$username = "root";
$password = "";
/*$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');*/

try {
    // Création de la connexion PDO avec encodage UTF-8
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Activation du mode d'erreur pour afficher les exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Gestion des erreurs de connexion : arrêt du script avec un message
    die("Erreur de connexion : " . $e->getMessage());
}
?>
