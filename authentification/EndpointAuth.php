<?php
require_once("connectionDB.php");
require_once("jwt_utils.php");

header("Content-Type: application/json");

$secret = getenv('JWT_SECRET') ?: 'asdfghjklzxcvbnm123456789';

if (!$secret) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur serveur"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Méthode non autorisée"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["login"], $input["password"])) {
    http_response_code(400);
    echo json_encode(["message" => "Données invalides ou incomplètes"]);
    exit;
}

$login = $input["login"];
$password = $input["password"];

try {

    $stmt = $pdo->prepare("SELECT * FROM user WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["password"])) {
        http_response_code(401);
        echo json_encode(["message" => "Identifiants invalides"]);
        exit;
    }

    $headers = ["alg" => "HS256", "typ" => "JWT"];

    $payload = [
        "login" => $user["login"],
        "role"  => $user["role"],
        "exp"   => time() + 3600
    ];

    $jwt = generate_jwt($headers, $payload, $secret);

    echo json_encode(["token" => $jwt]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur serveur", "debug" => $e->getMessage()]);
}




?>