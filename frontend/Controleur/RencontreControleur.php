<?php

namespace R301\Controleur;

class RencontreControleur {
    private static ?RencontreControleur $instance = null;
    private $apiUrl = "http://127.0.0.1/Projet_R4.01/backend/EndpointRencontre.php";

    private function __construct() {}

    public static function getInstance(): RencontreControleur {
        if (self::$instance == null) {
            self::$instance = new RencontreControleur();
        }
        return self::$instance;
    }

    public function listerToutesLesRencontres(): array {
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

    public function getRencontreById(int $id): ?array {
        $options = [
            'http' => [
                'method'        => 'GET',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $url      = $this->apiUrl . "?id=" . $id;
        $response = file_get_contents($url, false, $context);
        $res = json_decode($response, true);
        return $res['data'] ?? null;
    }

    public function ajouterRencontre(
        string $dateHeure,
        string $equipeAdverse,
        string $adresse,
        string $lieu
    ): bool {
        $data = [
            'dateHeure'     => $dateHeure,
            'equipeAdverse' => $equipeAdverse,
            'adresse'       => $adresse,
            'lieu'          => $lieu
        ];
        $options = [
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json",
                'content'       => json_encode($data),
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($this->apiUrl, false, $context);
        $res = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 201;
    }

    public function modifierRencontre(
        int $id,
        string $dateHeure,
        string $equipeAdverse,
        string $adresse,
        string $lieu
    ): bool {
        $data = [
            'dateHeure'     => $dateHeure,
            'equipeAdverse' => $equipeAdverse,
            'adresse'       => $adresse,
            'lieu'          => $lieu
        ];
        $options = [
            'http' => [
                'method'        => 'PUT',
                'header'        => "Content-Type: application/json",
                'content'       => json_encode($data),
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $url      = $this->apiUrl . "?id=" . $id;
        $response = file_get_contents($url, false, $context);
        $res = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 200;
    }

    public function enregistrerResultat(
        int $id,
        string $resultat
    ): bool {
        $data = [
            'resultat' => $resultat
        ];
        $options = [
            'http' => [
                'method'        => 'PUT',
                'header'        => "Content-Type: application/json",
                'content'       => json_encode($data),
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $url      = $this->apiUrl . "?id=" . $id . "&action=resultat";
        $response = file_get_contents($url, false, $context);
        $res = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 200;
    }

    public function supprimerRencontre(int $id): bool {
        $options = [
            'http' => [
                'method'        => 'DELETE',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $url      = $this->apiUrl . "?id=" . $id;
        $response = file_get_contents($url, false, $context);
        $res = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 200;
    }
}

?>