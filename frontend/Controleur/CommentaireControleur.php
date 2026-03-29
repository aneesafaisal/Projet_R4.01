<?php

// Déclaration du namespace pour organiser le code
namespace R301\Controleur;

// Contrôleur gérant les opérations liées aux commentaires
class CommentaireControleur {
    private static ?CommentaireControleur $instance = null;
    private $apiUrl = "https://equipe.alwaysdata.net/EndpointCommentaire.php";
    private string $token;
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->token = $_SESSION['token'] ?? '';
    }

    // Méthode permettant d'obtenir l'instance unique du contrôleur
    public static function getInstance(): CommentaireControleur {
        if (self::$instance == null) {
            self::$instance = new CommentaireControleur();
        }
        return self::$instance;
    }

    // Ajoute un nouveau commentaire pour un joueur donné
    public function ajouterCommentaire(
        string $contenu,
        string $joueurId
    ){
        $data = [
            "contenu" => $contenu,
            "joueur_id" => $joueurId
        ];
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json",
                'content' => json_encode($data),
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($this->apiUrl, false, $context);
        $res = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 201;
    }

    // Récupère la liste des commentaires d’un joueur
    public function listerLesCommentairesDuJoueur(Int $id) : array {
        $options = [
            'http' => [
                'method'        => 'GET',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $url = $this->apiUrl . "?joueur_id=" . $id;
        $response = file_get_contents($url, false, $context);
        $res = json_decode($response, true);
        return $res['data'] ?? [];
    }

    // Supprime un commentaire à partir de son identifiant
    public function supprimerCommentaire(string $commentaireId) : bool {
        $url = $this->apiUrl . "?id=" . $commentaireId;
        $options = [
            'http' => [
                'method'        => 'DELETE',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $res = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 200;
    }
}

?>