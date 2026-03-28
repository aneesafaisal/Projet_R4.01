<?php

// Génère un token JWT à partir des en-têtes, du payload et du secret
function generate_jwt($headers, $payload, $secret) {
    // Encodage des en-têtes et du contenu en base64url
	$headers_encoded = base64url_encode(json_encode($headers));
	$payload_encoded = base64url_encode(json_encode($payload));

    // Création de la signature avec HMAC SHA256
	$signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
	$signature_encoded = base64url_encode($signature);

    // Assemblage final du token JWT
	$jwt = "$headers_encoded.$payload_encoded.$signature_encoded";

	return $jwt;
}

// Vérifie si un token JWT est valide
function is_jwt_valid($jwt, $secret) {

    // Vérifie que le token et la clé secrète existent 
    if (!$jwt || !$secret) {
        return false;
    }

    // Sépare les 3 parties du JWT
    $tokenParts = explode('.', $jwt);

    if (count($tokenParts) !== 3) {
        return false;
    }

    // Décodage du header et du payload
    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signature_provided = $tokenParts[2];

    $decoded = json_decode($payload);
    // Vérifie la présence du champ d'expiration
    if (!$decoded || !isset($decoded->exp)) {
        return false;
    }

    // Vérifie si le token est expiré
    $is_token_expired = ($decoded->exp - time()) < 0;

    // Recalcule la signature pour comparer
    $base64_url_header = base64url_encode($header);
    $base64_url_payload = base64url_encode($payload);

    $signature = hash_hmac(
        'SHA256',
        $base64_url_header . "." . $base64_url_payload,
        $secret,
        true
    );

    $base64_url_signature = base64url_encode($signature);

    // Vérifie si la signature correspond
    $is_signature_valid = ($base64_url_signature === $signature_provided);

    // Le token est valide s'il n'est pas expiré et que la signature est correcte
    return !$is_token_expired && $is_signature_valid;
}

// Encode des données en base64url (format utilisé par JWT)
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Récupère le header Authorization depuis différentes sources possibles
function get_authorization_header(){
	$headers = null;

	if (isset($_SERVER['Authorization'])) {
		$headers = trim($_SERVER["Authorization"]);
	} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { 
		$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
	} else if (function_exists('apache_request_headers')) {
		$requestHeaders = apache_request_headers();
		$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
		if (isset($requestHeaders['Authorization'])) {
			$headers = trim($requestHeaders['Authorization']);
		}
	}
	return $headers;
}

// Extrait le token Bearer depuis le header Authorization
function get_bearer_token() {
    $headers = get_authorization_header();
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            if($matches[1]=='null') {
                return null;
            } else {
                return $matches[1];
            }    
        }
    }
    return null;
}

?>
