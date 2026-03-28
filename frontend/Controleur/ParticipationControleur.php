<?php

namespace R301\Controleur;

class ParticipationControleur {
    private static ?ParticipationControleur $instance = null;
    private string $apiUrl = "http://localhost/Projet_R4.01/backend/EndpointParticipation.php";
    private string $token = "TON_TOKEN_ICI";

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
                if ($data) {
                    $url .= "?" . http_build_query($data);
                }
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

    public function getFeuilleDeMatch(int $rencontreId): array {
        $response = $this->callAPI("GET", $this->apiUrl, ['rencontre_id' => $rencontreId]);
        if ($response === null || $response['status_code'] !== 200) {
            return [];
        }
        return $response['data'] ?? [];
    }
    public function assignerUnParticipant(
        int $joueurId,
        int $rencontreId,
        Poste $poste,
        TitulaireOuRemplacant $titulaireOuRemplacant
    ) : bool {
        $data = [
            "joueur_id" => $joueurId,
            "rencontre_id" => $rencontreId,
            "poste" => $poste->name,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant->name
        ];
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json",
                'content' => json_encode($data),
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($options);
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
            "id" => $participationId,
            "joueur_id" => $joueurId,
            "poste" => $poste->name,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant->name
        ];
        $options = [
            'http' => [
                'method' => 'PUT',
                'header' => "Content-Type: application/json",
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
                'method' => 'DELETE',
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $res = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 200;
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

?>