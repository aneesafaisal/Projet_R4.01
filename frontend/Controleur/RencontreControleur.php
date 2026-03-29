<?php

// Déclaration du namespace
namespace R301\Controleur;

// Contrôleur gérant les opérations liées aux rencontres (matchs)
class RencontreControleur {
    private static ?RencontreControleur $instance = null;
    private string $apiUrl = "https://equipe.alwaysdata.net/EndpointRencontre.php";
    private string $token = "";

    // Constructeur vide car on n'utilise plus les models
    private function __construct() {}

    // Retourne l'instance unique du contrôleur
    public static function getInstance(): RencontreControleur {
        if (self::$instance === null) {
            self::$instance = new RencontreControleur();
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
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;

            case "PATCH":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
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

    // Ajoute une nouvelle rencontre
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

        $response = $this->callAPI("POST", $this->apiUrl, $data);
        return $response !== null && $response['status_code'] === 201;
    }

    // Enregistre le résultat d’une rencontre
    public function enregistrerResultat(
        int $id,
        string $resultat
    ): bool {
        $data = ['resultat' => $resultat];
        $response = $this->callAPI("PATCH", $this->apiUrl . "?id=" . $id, $data);
        return $response !== null && $response['status_code'] === 200;
    }

    // Récupère une rencontre par son identifiant
    public function getRencontreById(int $id): ?array {
        $response = $this->callAPI("GET", $this->apiUrl, ['id' => $id]);

        if ($response === null || $response['status_code'] !== 200) {
            return null;
        }

        return $response['data'];
    }

    // Liste toutes les rencontres
    public function listerToutesLesRencontres(): array {
        $response = $this->callAPI("GET", $this->apiUrl);
        
        if ($response === null || $response['status_code'] !== 200) {
            return [];
        }

        return $response['data'] ?? [];
    }

    // Modifie une rencontre existante
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

        $response = $this->callAPI("PUT", $this->apiUrl . "?id=" . $id, $data);
        return $response !== null && $response['status_code'] === 200;
    }

    // Supprime une rencontre (uniquement si aucun résultat n’est enregistré)
    public function supprimerRencontre(int $id): bool {
        $response = $this->callAPI("DELETE", $this->apiUrl . "?id=" . $id);
        return $response !== null && $response['status_code'] === 200;
    }
}