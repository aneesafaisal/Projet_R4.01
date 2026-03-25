<?php
 /**
 * Extraire le JWT
 * @param mixed $data
 * @return string
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
/**
 * Obtenir le header Authorization
 * @return string|null
 */
function get_authorization_header(){
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
/**
 * Obtenir le bearer token
 */
function get_bearer_token() {
    $headers = get_authorization_header();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            if($matches[1]=='null') //$matches[1] est de type string et peut contenir 'null'
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
    // Specify the URL and data
    $url = "https://.alwaysdata.net/api_auth/";
    // // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '.get_bearer_token()));
    # Return response instead of printing.
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    # Send request.
    $response = curl_exec($ch);
    $response = json_decode($response, true);
    
    // Close cURL session
    curl_close($ch);  
    // return get_bearer_token();
    // Display the response
    if ($response['status_code'] != 200) {
        return false;
    } else {
        return true;
    }
}