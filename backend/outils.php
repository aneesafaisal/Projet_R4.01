<?php

require_once 'jwt_utils.php';

// Convertit récursivement un objet ou tableau en un format compatible JSON
// Gère les cas spéciaux : DateTime, énumérations (UnitEnum), objets et tableaux imbriqués
function convertir_en_json($data)
{
    if ($data instanceof DateTime) {
        return $data->format('Y-m-d');
    }
    if ($data instanceof \UnitEnum) {
        return $data->name;
    }
    if (is_object($data)) {
        $vars = get_object_vars($data);
        foreach ($vars as $key => $value) {
            $vars[$key] = convertir_en_json($value);
        }
        return $vars;
    }
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = convertir_en_json($value);
        }
        return $data;
    }
    return $data;
}

// Envoie une réponse HTTP au client au format JSON avec le code de statut, le message et les données
function deliver_response(int $status_code, string $status_message, $data = null): void
{
    http_response_code($status_code);
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    $data = convertir_en_json($data);

    echo json_encode([
        'status_code' => $status_code,
        'status_message' => $status_message,
        'data' => $data
    ]);
    exit;
}

// Récupère et valide l'utilisateur authentifié à partir du token JWT présent dans l'en-tête Authorization
// Retourne le payload décodé du token (contenant login et role) ou interrompt la requête avec une erreur
function getUser()
{
    $token = get_bearer_token();
    $secret = getenv('JWT_SECRET');

    if (!$token) {
        deliver_response(401, "Token introuvable");
        exit;
    }
    if (!$secret) {
        deliver_response(500, "Erreur serveur");
        exit;
    }
    if (!(is_jwt_valid($token, $secret))) {
        deliver_response(401, "Token invalide ou expiré");
        exit;
    }

    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        deliver_response(401, "Token invalide");
        exit;
    }

    $payload = json_decode(base64url_decode($parts[1]));
    return $payload;
}