<?php

// Endpoint pour gérer les requêtes liées aux statistiques de l'équipe et des joueurs, en utilisant le contrôleur des statistiques pour récupérer les données et en répondant avec des codes de statut HTTP appropriés
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Fonction pour récupérer le token d'authentification à partir de l'en-tête Authorization de la requête HTTP, en prenant en compte les différentes façons dont les en-têtes peuvent être envoyés par les clients et les serveurs
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

// Fonction pour extraire le token Bearer depuis l'en-tête Authorization, en vérifiant que le format de l'en-tête est correct et en gérant le cas où le token serait explicitement défini comme "null"
function get_bearer_token() {
    $headers = get_authorization_header();

    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            if($matches[1]=='null') 
                return null;
            else
                return $matches[1];
        }
    }
    return null;
}

// Fonction pour vérifier la validité du token d'authentification en envoyant une requête à un service d'authentification externe, en gérant le cas où l'application est exécutée en local pour permettre un accès sans token, et en vérifiant que le token n'est pas expiré et que sa signature est valide
function verifyToken(){
    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])
               || ($_SERVER['HTTP_HOST'] ?? '') === 'localhost';

    if ($isLocal) {
        return true;
    }

    $url = "https://auth.alwaysdata.net/EndpointAuth.php";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.get_bearer_token()));
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $response = curl_exec($ch);
    $response = json_decode($response, true);
    
    curl_close($ch);  
    if ($response['status_code'] != 200) {
        return false;
    } else {
        return true;
    }
}