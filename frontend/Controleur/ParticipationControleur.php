<?php

// Déclaration du namespace
namespace R301\Controleur;

// Contrôleur gérant les participations des joueurs aux matchs
class ParticipationControleur {
    private static ?ParticipationControleur $instance = null;
    private string $apiUrl = "https://equipe.alwaysdata.net/EndpointParticipation.php";
    private string $token;
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->token = $_SESSION['token'] ?? '';
    }

    // Retourne l’instance unique
    public static function getInstance(): ParticipationControleur {
        if (self::$instance === null) {
            self::$instance = new ParticipationControleur();
        }
        return self::$instance;
    }

    // Permet d'appeler l'API du backend
    // On a au début utilisé cette Fonction pour les appels a l'API 
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

    // Vérifie si un joueur est déjà présent sur la feuille de match
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

    // Liste toutes les participations
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

    // Récupère la feuille de match d’une rencontre
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
    ) : bool {
        $data = [
            "joueur_id"               => $joueurId,
            "rencontre_id"            => $rencontreId,
            "poste"                   => $poste,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant
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
        $res      = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 201;
    }

    // Modifie une participation existante
    public function modifierParticipation(
        int $participationId,
        string $poste,
        string $titulaireOuRemplacant,
        int $joueurId
    ) : bool {
        $data = [
            "joueur_id"               => $joueurId,
            "poste"                   => $poste,
            "titulaire_ou_remplacant" => $titulaireOuRemplacant
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
        $url      = $this->apiUrl . "?id=" . $participationId;
        $response = file_get_contents($url, false, $context);
        $res      = json_decode($response, true);
        return isset($res['status_code']) && $res['status_code'] === 200;
    }

    // Supprime une participation
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

    // Met à jour la performance d’un joueur 
    public function mettreAJourLaPerformance(int $participationId, string $performance): bool {
    $data = ['performance' => $performance];
    $options = [
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json\r\n",
            'content'       => json_encode($data),
            'ignore_errors' => true
        ]
    ];
    $context  = stream_context_create($options);
    $url      = $this->apiUrl . "?id=" . $participationId;
    $response = file_get_contents($url, false, $context);
    $res      = json_decode($response, true);
    return isset($res['status_code']) && $res['status_code'] === 200;
}

// Supprime la performance d’un joueur
public function supprimerLaPerformance(int $participationId): bool {
    $options = [
        'http' => [
            'method'        => 'DELETE',
            'ignore_errors' => true
        ]
    ];
    $context  = stream_context_create($options);
    $url      = $this->apiUrl . "?id=" . $participationId . "&action=delete_performance";
    $response = file_get_contents($url, false, $context);
    $res      = json_decode($response, true);
    return isset($res['status_code']) && $res['status_code'] === 200;
}
}

?>