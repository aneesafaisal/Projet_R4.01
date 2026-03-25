<?php 

include_once "authControleur.php";
include_once "jwt_utils.php";

$http_method=$_SERVER['REQUEST_METHOD'];
$postedData = file_get_contents('php://input');
$data = json_decode($postedData,TRUE);
$clefSecrete = "asdfghjklzxcvbnm123456789";

switch ($http_method) {

    case 'OPTIONS':
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        http_response_code(204); 
        break;

    case 'POST':
        if ($data == null) {
            delivrer_reponse("Bad Request", 400, "Bad Request");
        } else if (array_key_exists('login', $data) && array_key_exists('password', $data)) {
            generateToken();
        } else {
            delivrer_reponse("Bad Request", 400, "Bad Request");
        }
        break;

    case 'GET':
        if ($data == null || empty($data)) {
            delivrer_reponse("Bad Request", 400, "Bad Request");
        } else {
            verifyToken();
        }
        break;
    default:
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        http_response_code(405);
        header("Allow: POST");
        break;
}

function generateToken() {
    global $clefSecrete; 
    $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
    $postedData = file_get_contents('php://input');
    $data = json_decode($postedData,TRUE);

    if (empty($data['login']) || empty($data['password'])) {
        delivrer_reponse("Bad Request", 400, "Bad Request");
    } else {
        if (estValide($data['login'], $data['password'])) {
            $username = $data['login'];
            $payload = array('username'=>$username, 'exp'=>(time()+600),'role'=>getRole($username));
            $jwt = generate_jwt($headers,$payload,$clefSecrete);
            delivrer_reponse("Success",200,"Authentification OK", $jwt);
        } else {
            delivrer_reponse("Unauthorized", 401, "Unauthorized");
        }
    }
}


function verifyToken() {
    global $clefSecrete;
    $bearer_token = '';
    $bearer_token = get_bearer_token();
    if (empty($bearer_token)) {
        delivrer_reponse("Invalid token", 401, "Invalid token",$bearer_token);
    } else {
        if (is_jwt_valid($bearer_token, $clefSecrete)) {
            delivrer_reponse("Success", 200, "Token is valid");
        } else {
            delivrer_reponse("Error", 401, "Invalid token");
        }
    }
}
