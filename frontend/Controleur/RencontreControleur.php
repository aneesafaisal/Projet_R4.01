<?php

namespace R301\Controleur;

class RencontreControleur {
    private static $instance = null;
    private string $apiUrl = "https://equipe.alwaysdata.net/EndpointRencontre.php";

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Retourne l'instance unique du contrôleur
    public static function getInstance(): RencontreControleur {
        if (self::$instance === null) {
            self::$instance = new RencontreControleur();
        }
        return self::$instance;
    }

    // Permet d'appeler l'API du backend pour les opérations liées aux rencontres
    private function callAPI(string $method, string $url, $data = null, bool $withToken = false) {
        $headers = ["Content-Type: application/json"];
        if ($withToken) {
            $headers[] = "Authorization: Bearer " . ($_SESSION['token'] ?? '');
        }
        $options = [
            'http' => [
                'method'        => $method,
                'header'        => implode("\r\n", $headers) . "\r\n",
                'content'       => $data ? json_encode($data) : null,
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true);
    }

    // Methode GET pour récupérer une rencontre par son id, public, pas de token
    public function getRencontreById(int $id) {
        $res = $this->callAPI("GET", $this->apiUrl . "?id=" . $id);
        if ($res === null || $res['status_code'] !== 200) {
            return null;
        }
        return $res['data'];
    }

    // Methode GET pour lister toutes les rencontres, public, pas de token
    public function listerToutesLesRencontres(): array {
        $res = $this->callAPI("GET", $this->apiUrl);
        if ($res === null || $res['status_code'] !== 200) {
            return [];
        }
        if (isset($res['data'])) {
            return $res['data'];
        }
        return [];
    }

    // Methode POST pour ajouter une rencontre, protégé par token   
    public function ajouterRencontre(string $dateHeure, string $equipeAdverse, string $adresse, string $lieu): bool {
        $res = $this->callAPI("POST", $this->apiUrl, [
            'dateHeure'     => $dateHeure,
            'equipeAdverse' => $equipeAdverse,
            'adresse'       => $adresse,
            'lieu'          => $lieu
        ], true);
        return $res !== null && $res['status_code'] === 201;
    }

    // Methode PUT pour modifier une rencontre, protégé par token
    public function modifierRencontre(int $id, string $dateHeure, string $equipeAdverse, string $adresse, string $lieu): bool {
        $res = $this->callAPI("PUT", $this->apiUrl . "?id=" . $id, [
            'dateHeure'     => $dateHeure,
            'equipeAdverse' => $equipeAdverse,
            'adresse'       => $adresse,
            'lieu'          => $lieu
        ], true);
        return $res !== null && $res['status_code'] === 200;
    }

    // Methode PATCH pour enregistrer le résultat d'une rencontre, protégé par token
    public function enregistrerResultat(int $id, string $resultat): bool {
        $res = $this->callAPI("PATCH", $this->apiUrl . "?id=" . $id, [
            'resultat' => $resultat
        ], true);
        return $res !== null && $res['status_code'] === 200;
    }

    // Methode DELETE pour supprimer une rencontre, protégé par token
    public function supprimerRencontre(int $id): bool {
        $res = $this->callAPI("DELETE", $this->apiUrl . "?id=" . $id, null, true);
        return $res !== null && $res['status_code'] === 200;
    }
}