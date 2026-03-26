<?php

namespace R301\Controleur;

class JoueurControleur {
    private static ?JoueurControleur $instance = null;
    private string $apiUrl = "http://localhost/Projet_R4.01/BACKEND/Joueur";

    // Remplace par ta constante ou ta session token
    private string $token = "TON_TOKEN_ICI";

    private function __construct() {}

    public static function getInstance(): JoueurControleur {
        if (self::$instance === null) {
            self::$instance = new JoueurControleur();
        }
        return self::$instance;
    }

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

    public function listerTousLesJoueurs(): array {
        $response = $this->callAPI("GET", $this->apiUrl);

        if ($response === null || $response['status_code'] !== 200) {
            return [];
        }

        return $response['data'] ?? [];
    }

    public function getJoueurById(int $id): ?array {
        $response = $this->callAPI("GET", $this->apiUrl, ['id' => $id]);

        if ($response === null || $response['status_code'] !== 200) {
            return null;
        }

        return $response['data'];
    }

    public function ajouterJoueur(
        string $nom,
        string $prenom,
        string $numeroDeLicence,
        string $dateDeNaissance,
        int $tailleEnCm,
        int $poidsEnKg,
        string $statut
    ): bool {
        $data = [
            'nom'             => $nom,
            'prenom'          => $prenom,
            'numeroDeLicence' => $numeroDeLicence,
            'dateDeNaissance' => $dateDeNaissance, // format "Y-m-d"
            'tailleEnCm'      => $tailleEnCm,
            'poidsEnKg'       => $poidsEnKg,
            'statut'          => $statut
        ];

        $response = $this->callAPI("POST", $this->apiUrl, $data);

        return $response !== null && $response['status_code'] === 201;
    }

    public function modifierJoueur(
        int $id,
        string $nom,
        string $prenom,
        string $numeroDeLicence,
        string $dateDeNaissance,
        int $tailleEnCm,
        int $poidsEnKg,
        string $statut
    ): bool {
        $data = [
            'nom'             => $nom,
            'prenom'          => $prenom,
            'numeroDeLicence' => $numeroDeLicence,
            'dateDeNaissance' => $dateDeNaissance,
            'tailleEnCm'      => $tailleEnCm,
            'poidsEnKg'       => $poidsEnKg,
            'statut'          => $statut
        ];

        $response = $this->callAPI("PUT", $this->apiUrl . "?id=" . $id, $data);

        return $response !== null && $response['status_code'] === 200;
    }

    public function supprimerJoueur(int $id): bool {
        $response = $this->callAPI("DELETE", $this->apiUrl . "?id=" . $id);

        return $response !== null && $response['status_code'] === 200;
    }

    // Le backend n'a pas d'endpoint de filtre : on filtre côté frontend
    public function rechercherLesJoueurs(string $recherche, string $statut): array {
        $tous = $this->listerTousLesJoueurs();
        $resultats = [];

        foreach ($tous as $joueur) {
            $conserver = true;

            if ($recherche !== "") {
                $nomContient    = str_contains(strtolower($joueur['nom']),    strtolower($recherche));
                $prenomContient = str_contains(strtolower($joueur['prenom']), strtolower($recherche));
                $conserver = $nomContient || $prenomContient;
            }

            if ($conserver && $statut !== "") {
                $conserver = $joueur['statut'] === $statut;
            }

            if ($conserver) {
                $resultats[] = $joueur;
            }
        }

        return $resultats;
    }
}