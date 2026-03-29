<?php

namespace R301\Controleur;

class ParticipationControleur {
    private static ?ParticipationControleur $instance = null;
    private string $apiUrl = "http://localhost/Projet_R4.01/backend/EndpointParticipation.php";
    private string $token = "";  

    private function __construct() {}

    public static function getInstance(): ParticipationControleur {
        if (self::$instance === null) {
            self::$instance = new ParticipationControleur();
        }
        return self::$instance;
    }

    private function callAPI(string $method, string $url, array $data = null): ?array {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, true);
                if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;

            case "PATCH":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
                if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;

            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;

            default: // GET
                if ($data) $url .= "?" . http_build_query($data);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);

        $result = curl_exec($curl);
        curl_close($curl);

        if (!$result) return null;
        return json_decode($result, true);
    }

    public function getFeuilleDeMatch(int $rencontreId): array {
        $response = $this->callAPI("GET", $this->apiUrl, ['rencontre_id' => $rencontreId]);
        if ($response === null || $response['status_code'] !== 200) {
            return [];
        }
        return $response['data'] ?? [];
    }

    public function lejoueurEstDejaSurLaFeuilleDeMatch(int $rencontreId, int $joueurId): bool {
        $response = $this->callAPI("GET", $this->apiUrl, [
            'rencontre_id' => $rencontreId,
            'joueur_id'    => $joueurId,
            'check'        => 'feuille'
        ]);
        return isset($response['data']) && $response['data'] === true;
    }

    public function assignerUnParticipant(int $joueurId, int $rencontreId, string $poste, string $titulaireOuRemplacant): bool {
        $data = [
            "joueur_id"               => $joueurId,
            "rencontre_id"            => $rencontreId,
            "poste"                   => $poste,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant
        ];
        $response = $this->callAPI("POST", $this->apiUrl, $data);
        return $response !== null && $response['status_code'] === 201;
    }

    public function modifierParticipation(int $participationId, string $poste, string $titulaireOuRemplacant, int $joueurId): bool {
        $data = [
            "joueur_id"               => $joueurId,
            "poste"                   => $poste,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant
        ];
        $response = $this->callAPI("PUT", $this->apiUrl . "?id=" . $participationId, $data);
        return $response !== null && $response['status_code'] === 200;
    }

    public function supprimerLaParticipation(int $participationId): bool {
        $response = $this->callAPI("DELETE", $this->apiUrl . "?id=" . $participationId);
        return $response !== null && $response['status_code'] === 200;
    }

    public function mettreAJourLaPerformance(int $participationId, string $performance): bool {
        $data = ['performance' => $performance];
        $response = $this->callAPI("PATCH", $this->apiUrl . "?id=" . $participationId, $data);
        return $response !== null && $response['status_code'] === 200;
    }

    public function supprimerLaPerformance(int $participationId): bool {
        $data = ['performance' => null];
        $response = $this->callAPI("PATCH", $this->apiUrl . "?id=" . $participationId, $data);
        return $response !== null && $response['status_code'] === 200;
    }
}