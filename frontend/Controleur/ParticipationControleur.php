<?php

namespace R301\Controleur;

use R301\Modele\Participation\Poste;
use R301\Modele\Participation\TitulaireOuRemplacant;

class ParticipationControleur {
    private static ?ParticipationControleur $instance = null;
    private $apiUrl = "http://localhost/Projet_R4.01/BACKEND/EndpointParticipation.php";

    private function __construct() {
        #$this->participations = ParticipationDAO::getInstance();
        #$this->joueurs = JoueurControleur::getInstance();
        #$this->rencontres = RencontreControleur::getInstance();
    }

    public static function getInstance(): ParticipationControleur {
        if (self::$instance == null) {
            self::$instance = new ParticipationControleur();
        }
        return self::$instance;
    }

    public function lejoueurEstDejaSurLaFeuilleDeMatch(int $rencontreId, int $joueurId) : bool {
        $options = [
            'http' => [
                'method'        => 'GET',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $url = $this->apiUrl . "?rencontre_id=" . $rencontreId . "&joueur_id=" . $joueurId . "&check=feuille";
        $response = file_get_contents($url, false, $context);
        $res = json_decode($response, true);
        return isset($res['data']) && $res['data'] === true;
    }

    public function listerToutesLesParticipations() : array {
        $options = [
            'http' => [
                'method'        => 'GET',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($this->apiUrl, false, $context);
        $res = json_decode($response, true);
        return $res['data'] ?? [];
    }

    public function getFeuilleDeMatch(int $rencontreId) : array {
        $options = [
            'http' => [
                'method'        => 'GET',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $url = $this->apiUrl . "?rencontre_id=" . $rencontreId;
        $response = file_get_contents($url, false, $context);
        $res = json_decode($response, true);
        return $res['data'] ?? [];
    }

    public function assignerUnParticipant(
        int $joueurId,
        int $rencontreId,
        Poste $poste,
        TitulaireOuRemplacant $titulaireOuRemplacant
    ) : bool {
        $data = [
            "joueur_id"              => $joueurId,
            "rencontre_id"           => $rencontreId,
            "poste"                  => $poste->name,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant->name
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

    public function modifierParticipation(
        int $participationId,
        Poste $poste,
        TitulaireOuRemplacant $titulaireOuRemplacant,
        int $joueurId
    ) : bool {
        $data = [
            "id"                     => $participationId,
            "joueur_id"              => $joueurId,
            "poste"                  => $poste->name,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant->name
        ];
        $options = [
            'http' => [
                'method'  => 'PUT',
                'header'  => "Content-Type: application/json",
                'content' => json_encode($data),
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($this->apiUrl, false, $context);
        $res = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 200;
    }

    public function supprimerLaParticipation(int $participationId) : bool {
        $url = $this->apiUrl . "?id=" . $participationId;
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

    public function mettreAJourLaPerformance(
        int $participationId,
        string $performance
    ) : bool {
        $data = [
            "id"          => $participationId,
            "performance" => $performance
        ];
        $options = [
            'http' => [
                'method'  => 'PUT',
                'header'  => "Content-Type: application/json",
                'content' => json_encode($data),
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($this->apiUrl . "?action=performance", false, $context);
        $res = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 200;
    }

    public function supprimerLaPerformance(int $participationId) : bool {
        $url = $this->apiUrl . "?id=" . $participationId . "&action=performance";
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