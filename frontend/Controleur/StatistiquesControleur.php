<?php

// Déclaration du namespace
namespace R301\Controleur;

// Contrôleur dédié au calcul des statistiques
class StatistiquesControleur {
    private static ?StatistiquesControleur $instance = null;
    private string $apiUrl = "https://equipe.alwaysdata.net/EndpointStatistiques.php";
    private string $token;
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->token = $_SESSION['token'] ?? '';
    }

    // Retourne l'instance unique du contrôleu
    public static function getInstance(): StatistiquesControleur {
        if (self::$instance == null) {
            self::$instance = new StatistiquesControleur();
        }
        return self::$instance;
    }

    // Calcule et retourne les statistiques globales de l’équipe
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

    // Calcule et retourne les statistiques des joueurs
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