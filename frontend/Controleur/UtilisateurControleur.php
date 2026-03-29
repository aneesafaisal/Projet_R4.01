<?php

namespace R301\Controleur;

use DateTime;
use R301\Modele\Joueur\Commentaire\Commentaire;
use R301\Modele\Joueur\Commentaire\CommentaireDAO;
use R301\Modele\Joueur\Joueur;
use R301\Modele\Joueur\JoueurDAO;
use R301\Modele\Joueur\JoueurStatut;
use R301\Modele\Statistiques\StatistiquesEquipe;
use R301\Modele\Statistiques\StatistiquesJoueurs;
use R301\Modele\Utilisateur\UtilisateurDAO;

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

    public function seConnecter(string $username, string $password): bool {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $url = "https://auth.alwaysdata.net/EndpointAuth.php";

        $data = json_encode([
            "login" => $username,
            "password" => $password
        ]);

        $options = [
            "http" => [
                "header"  => "Content-Type: application/json\r\n",
                "method"  => "POST",
                "content" => $data,
                "timeout" => 5
            ]
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            return false;
        }

        $response = json_decode($result, true);

        if (!$response || !isset($response["token"])) {
            return false;
        }

        $jwt = $response["token"];

        $_SESSION['token'] = $jwt;
        $_SESSION['username'] = $username;

        setcookie(
            "token",
            $jwt,
            [
                "expires" => time() + 3600,
                "path" => "/",
                "httponly" => true,
                "secure" => false,
                "samesite" => "Strict"
            ]
        );

        return true;
    }
    
    public function seDeconnecter(): void {
        setcookie("token", "", time() - 3600, "/");
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
    }
}
?>