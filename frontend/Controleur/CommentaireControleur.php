<?php

namespace R301\Controleur;

use DateTime;
use R301\Modele\Joueur\Commentaire\Commentaire;
use R301\Modele\Joueur\Commentaire\CommentaireDAO;
use R301\Modele\Joueur\Joueur;
use R301\Modele\Joueur\JoueurDAO;
use R301\Modele\Joueur\JoueurStatut;

class CommentaireControleur {
    private static ?CommentaireControleur $instance = null;
    private string $apiUrl = "http://localhost/Projet_R4.01/BACKEND/EndpointCommentaire.php";

    private function __construct() {
        #$this->commentaires = CommentaireDAO::getInstance();
    }

    private function callAPI(string $method, string $url, array $data = null) {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case "PUT":
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            default: // GET
                if ($data) {
                    $url .= "?" . http_build_query($data);
                }
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
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
    ) : bool {
        $data = [
            "contenu" => $contenu,
            "joueurId" => $joueurId
        ];
        $response = $this->callAPI("POST", $this->apiUrl, $data);
        return $response != null;
    }
    
    public function modifierCommentaire(
        string $commentaireId,
        string $contenu
    ) : bool {
        $data = [
            "commentaireId" => $commentaireId,
            "contenu" => $contenu
        ];
        $response = $this->callAPI("PUT", $this->apiUrl, $data);
        return $response != null;
    }

    public function listerLesCommentairesDuJoueur(Joueur $joueur) : array {
        $data = [
            "joueurId" => $joueur->getJoueurId()
        ];
        $response = $this->callAPI("GET", $this->apiUrl, $data);
        return $response != null;
    }

    public function supprimerCommentaire(string $commentaireId) : bool {
        $response = $this->callAPI("DELETE", $this->apiUrl . "/" . $commentaireId);
        return $response != null;
    }
}

?>