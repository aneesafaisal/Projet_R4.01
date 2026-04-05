<?php

namespace R301\Controleur;

class ParticipationControleur {
    private static ?ParticipationControleur $instance = null;
    private string $apiUrl = "https://equipe.alwaysdata.net/EndpointParticipation.php";
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance(): ParticipationControleur {
        if (self::$instance === null) {
            self::$instance = new ParticipationControleur();
        }
        return self::$instance;
    }

    private function callAPI(string $method, string $url, ?array $data = null): ?array {
        $token = $_SESSION['token'] ?? '';
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
                if ($data) {
                    $url .= "?" . http_build_query($data);
                }
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);

        $result = curl_exec($curl);
        curl_close($curl);

        if (!$result) return null;
        return json_decode($result, true);
    }

    // Vérifie si un joueur est déjà présent sur la feuille de match
    public function lejoueurEstDejaSurLaFeuilleDeMatch(int $rencontreId, int $joueurId): bool {
        $response = $this->callAPI("GET", $this->apiUrl, [
            'rencontre_id' => $rencontreId,
            'joueur_id'    => $joueurId,
            'check'        => 'feuille'
        ]);
        return isset($response['data']) && $response['data'] === true;
    }

    // Liste toutes les participations
    public function listerToutesLesParticipations(): array {
        $response = $this->callAPI("GET", $this->apiUrl);
        return $response['data'] ?? [];
    }

    // Récupère la feuille de match d'une rencontre
    public function getFeuilleDeMatch(int $rencontreId): array {
        $response = $this->callAPI("GET", $this->apiUrl, ['rencontre_id' => $rencontreId]);
        if ($response === null || $response['status_code'] !== 200) {
            return [];
        }
        return $response['data'] ?? [];
    }

    // Assigne un joueur à une rencontre avec un poste et un statut
    public function assignerUnParticipant(
        int $joueurId,
        int $rencontreId,
        string $poste,
        string $titulaireOuRemplacant
    ): bool {
        $response = $this->callAPI("POST", $this->apiUrl, [
            "joueur_id"               => $joueurId,
            "rencontre_id"            => $rencontreId,
            "poste"                   => $poste,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant
        ]);
        return isset($response['status_code']) && $response['status_code'] === 201;
    }

    // Modifie une participation existante
    public function modifierParticipation(
        int $participationId,
        string $poste,
        string $titulaireOuRemplacant,
        int $joueurId
    ): bool {
        $response = $this->callAPI("PUT", $this->apiUrl . "?id=" . $participationId, [
            "joueur_id"               => $joueurId,
            "poste"                   => $poste,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant
        ]);
        return isset($response['status_code']) && $response['status_code'] === 200;
    }

    // Supprime une participation
    public function supprimerLaParticipation(int $participationId): bool {
        $response = $this->callAPI("DELETE", $this->apiUrl . "?id=" . $participationId);
        return isset($response['status_code']) && $response['status_code'] === 200;
    }

    // Met à jour la performance d'un joueur
    public function mettreAJourLaPerformance(int $participationId, string $performance): bool {
        $response = $this->callAPI("POST", $this->apiUrl . "?id=" . $participationId, [
            'performance' => $performance
        ]);
        return isset($response['status_code']) && $response['status_code'] === 200;
    }

    // Supprime la performance d'un joueur
    public function supprimerLaPerformance(int $participationId): bool {
        $response = $this->callAPI("DELETE", $this->apiUrl . "?id=" . $participationId . "&action=delete_performance");
        return isset($response['status_code']) && $response['status_code'] === 200;
    }
}

?>