<?php

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

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

function verifyToken(){
    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])
               || ($_SERVER['HTTP_HOST'] ?? '') === 'localhost';

    if ($isLocal) {
        return true;
    }

    $url = "https://.alwaysdata.net/api_auth/";
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