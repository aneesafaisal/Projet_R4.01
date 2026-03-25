<?php

namespace R301\Controleur;

use DateTime;

class JoueurControleur {
    private static ?JoueurControleur $instance = null;
    private readonly ParticipationControleur $participationControleur;
    private string $apiUrl = "http://localhost/Projet_R4.01/BACKEND/Joueur";

    private function __construct() {
        #$this->participationControleur = ParticipationControleur::getInstance();
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

    public static function getInstance(): JoueurControleur {
        if (self::$instance == null) {
            self::$instance = new JoueurControleur();
        }
        return self::$instance;
    }

    public function ajouterJoueur(
        string $nom,
        string $prenom,
        string $numeroDeLicence,
        DateTime $dateDeNaissance,
        int $tailleEnCm,
        int $poidsEnKg,
        string $statut
    ) : bool {
        $response = $this->callAPI("POST", $this->apiUrl, $data);
        return $response != null;
    }

    //Joueur
    public function getJoueurById(int $joueurId)  {  
        return $this->callAPI("GET", $this->apiUrl . "/" . $joueurId);
    }

    // array
    public function listerLesJoueursSelectionnablesPourUnMatch(int $rencontreId)   { 
        return $this->callAPI("GET", $this->apiUrl);
    }

    //array
    public function listerTousLesJoueurs()  { 
       return $this->callAPI("GET", $this->apiUrl);
    }

    public function modifierJoueur(
        int $joueurId,
        string $nom,
        string $prenom,
        string $numeroDeLicence,
        DateTime $dateDeNaissance,
        int $tailleEnCm,
        int $poidsEnKg,
        string $statut
    ) : bool {
        $response = $this->callAPI("PUT", $this->apiUrl . "/" . $joueurId, $data);
        return $response != null;
    }

    public function rechercherLesJoueurs(string $recherche, string $statut)   { #array
        return $this->callAPI("GET", $this->apiUrl, [
        "recherche" => $recherche,
        "statut" => $statut
        ]);
    }

    public function supprimerJoueur(int $joueurId) : bool {
        $response = $this->callAPI("DELETE", $this->apiUrl . "/" . $joueurId);
        return $response != null;
}
}

?>