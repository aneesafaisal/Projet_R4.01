<?php

namespace R301\Controleur;
use R301\Modele\Joueur\Joueur;

class CommentaireControleur {
    private static ?CommentaireControleur $instance = null;
    private $apiUrl = "https://equipe.alwaysdata.net/EndpointCommentaire.php";

    private function __construct() {
        #$this->commentaires = CommentaireDAO::getInstance();
    }

    public static function getInstance(): CommentaireControleur {
        if (self::$instance == null) {
            self::$instance = new CommentaireControleur();
        }
        return self::$instance;
    }

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