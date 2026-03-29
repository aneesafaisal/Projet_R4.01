<?php

// Génère un token JWT signé à partir des en-têtes, du payload et du secret
function generate_jwt($headers, $payload, $secret)
{
    $headers_encoded = base64url_encode(json_encode($headers));

    $payload_encoded = base64url_encode(json_encode($payload));

    $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
    $signature_encoded = base64url_encode($signature);

    $jwt = "$headers_encoded.$payload_encoded.$signature_encoded";

    return $jwt;
}

// Vérifie la validité d'un token JWT en contrôlant sa structure, sa signature et son expiration
function is_jwt_valid($jwt, $secret)
{

    if (!$jwt || !$secret) {
        return false;
    }

    $tokenParts = explode('.', $jwt);

    if (count($tokenParts) !== 3) {
        return false;
    }

    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signature_provided = $tokenParts[2];

    $decoded = json_decode($payload);

    if (!$decoded || !isset($decoded->exp)) {
        return false;
    }

    $is_token_expired = ($decoded->exp - time()) < 0;

    $base64_url_header = base64url_encode($header);
    $base64_url_payload = base64url_encode($payload);

    $signature = hash_hmac(
        'SHA256',
        $base64_url_header . "." . $base64_url_payload,
        $secret,
        true
    );

    $base64_url_signature = base64url_encode($signature);

    $is_signature_valid = ($base64_url_signature === $signature_provided);

    return !$is_token_expired && $is_signature_valid;
}

// Décode une chaîne encodée en base64url en base64 standard
function base64url_decode($data)
{
    return base64_decode(strtr($data, '-_', '+/'));
}
// Encode une chaîne en base64url (variante URL-safe du base64, sans padding)
function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Récupère l'en-tête Authorization de la requête HTTP en gérant les différents serveurs (Apache, Nginx, FastCGI)
function get_authorization_header()
{
    $headers = null;

    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    return $headers;
}

// Extrait le token Bearer depuis l'en-tête Authorization
// Retourne null si aucun token n'est trouvé ou si le token vaut explicitement "null"
function get_bearer_token()
{
    $headers = get_authorization_header();

    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            if ($matches[1] == 'null') //$matches[1] est de type string et peut contenir 'null'
                return null;
            else
                return $matches[1];
        }
    }
    return null;
}

?>