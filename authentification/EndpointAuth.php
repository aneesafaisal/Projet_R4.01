<?php
// Inclusion des fichiers nécessaires : connexion à la base et fonctions JWT
require_once("connectionDB.php");
require_once("jwt_utils.php");

// Définition du type de réponse en JSON
header("Content-Type: application/json");

// Récupération de la clé secrète JWT depuis les variables d'environnement
$secret = getenv('JWT_SECRET') ?: 'asdfghjklzxcvbnm123456789';

// Vérifie que la clé secrète existe
if (!$secret) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur serveur"]);
    exit;
}

// Vérifie que la méthode HTTP utilisée est POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Méthode non autorisée"]);
    exit;
}

// Récupération et décodage des données JSON envoyées dans la requête
$input = json_decode(file_get_contents("php://input"), true);

// Vérification des données reçues
if (!$input || !isset($input["login"], $input["password"])) {
    http_response_code(400);
    echo json_encode(["message" => "Données invalides ou incomplètes"]);
    exit;
}

// Extraction des informations de connexion
$login = $input["login"];
$password = $input["password"];

try {

    // Préparation et exécution de la requête pour récupérer l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM user WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification de l'utilisateur et du mot de passe
    if (!$user || !password_verify($password, $user["password"])) {
        http_response_code(401);
        echo json_encode(["message" => "Identifiants invalides"]);
        exit;
    }

    // Création des en-têtes du JWT
    $headers = ["alg" => "HS256", "typ" => "JWT"];

    // Création du payload (données contenues dans le token)
    $payload = [
        "login" => $user["login"],
        "role"  => $user["role"],
        // expiration dans 1 heure
        "exp"   => time() + 3600
    ];

    // Génération du token JWT
    $jwt = generate_jwt($headers, $payload, $secret);

    // Retour du token au format JSON
    echo json_encode(["token" => $jwt]);

} catch (Exception $e) {
    // Gestion des erreurs serveur
    http_response_code(500);
    echo json_encode(["message" => "Erreur serveur", "debug" => $e->getMessage()]);
}




?>