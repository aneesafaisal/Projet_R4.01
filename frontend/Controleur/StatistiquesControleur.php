<?php

namespace R301\Controleur;

// Contrôleur dédié au calcul des statistiques
class StatistiquesControleur {
    private static ?StatistiquesControleur $instance = null;
    private string $apiUrl = "https://equipe.alwaysdata.net/EndpointStatistiques.php";
    
    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
    }

    // Retourne l'instance unique du contrôleur
    public static function getInstance(): StatistiquesControleur {
        if (self::$instance == null) {
            self::$instance = new StatistiquesControleur();
        }
        return self::$instance;
    }

    // Permet d'appeler l'API du backend pour récupérer les statistiques
    private function callAPI() {
        $options = [
            'http' => [
                'method'        => 'GET',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($this->apiUrl, false, $context);
        return json_decode($response);
    }

    // Récupère les statistiques globales de l'équipe
    public function getStatistiquesEquipe() {
        $res = $this->callAPI();
        if ($res->status_code !== 200) {
            return [];
        }
        return $res->data->statistiques_equipe;
    }

    // Récupère les statistiques des joueurs
    public function getStatistiquesJoueurs() {
        $res = $this->callAPI();
        if ($res->status_code !== 200) {
            return [];
        }
        return $res->data->statistiques_joueurs;
    }
}

?>