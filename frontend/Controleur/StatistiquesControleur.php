<?php

namespace R301\Controleur;

class StatistiquesControleur {
    private static ?StatistiquesControleur $instance = null;
    private $apiUrl = "http://localhost/Projet_R4.01/backend/EndpointStatistiques.php";

    private function __construct() {}

    public static function getInstance(): StatistiquesControleur {
        if (self::$instance == null) {
            self::$instance = new StatistiquesControleur();
        }
        return self::$instance;
    }

    public function getStatistiquesEquipe() {
        $options = [
            'http' => [
                'method'        => 'GET',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($this->apiUrl, false, $context);
        $res = json_decode($response);
        if ($res->status_code !== 200) {       
            return [];
        }
        return $res->data->statistiques_equipe;
        
    }

    public function getStatistiquesJoueurs() {
        $options = [
            'http' => [
                'method'        => 'GET',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($this->apiUrl, false, $context);
        $res = json_decode($response);
        if ($res->status_code !== 200) {       
            return [];
        }
        return $res->data->statistiques_joueurs;
    }
}

?>