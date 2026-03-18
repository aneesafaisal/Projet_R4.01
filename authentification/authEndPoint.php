<?php 

include_once "authControleur.php";
include_once "jwt_utils.php";

$http_method=$_SERVER['REQUEST_METHOD'];
$postedData = file_get_contents('php://input');
$data = json_decode($postedData,TRUE);

switch ($http_method) {
    case 'OPTIONS':
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        http_response_code(204); // No content
        break;
    case 'POST':
        if ($data == null) {
            
        }
    
    default:
        send_json_response(405, "Méthode non autorisée");
        break;
}

class UtilisateurControleur {
    private static ?UtilisateurControleur $instance = null;
    private readonly UtilisateurDAO $utilisateurs;

    private function __construct() {
        $this->utilisateurs = UtilisateurDAO::getInstance();
    }

    public static function getInstance(): UtilisateurControleur {
        if (self::$instance == null) {
            self::$instance = new UtilisateurControleur();
        }
        return self::$instance;
    }

    public function seConnecter(string $username, string $password): ?string {
    $utilisateur = $this->utilisateurs->getUtilisateur($username);
    if ($utilisateur && $utilisateur->getMotDePasse() === hash('sha256', $password)) {
        return generateJWT(
            ['username' => $username, 'exp' => time() + 3600],
            "votre_secret_jwt"
        );
    }
    return null;
}
}