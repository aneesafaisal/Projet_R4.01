<?php

// Déclaration du namespace pour organiser le code
namespace R301\Controleur;

// Contrôleur gérant les opérations liées aux joueurs
class JoueurControleur {
    private static ?JoueurControleur $instance = null;
    private string $apiUrl = "https://equipe.alwaysdata.net/EndpointJoueur.php";
    private string $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJsb2dpbiI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiZXhwIjoxNzc0ODY3MzY4fQ.s8ZuuVX_DTgy7GrQelaOKfBu3eJUd-nvMx_t1f94JGQ";

    // Constructeur vide car on n'utilise plus les models
    private function __construct() {}

    // Retourne l’instance unique du contrôleur
    public static function getInstance(): JoueurControleur {
        if (self::$instance === null) {
            self::$instance = new JoueurControleur();
        }
        return self::$instance;
    }

    // Permet d'appeler l'API du backend
    // On a au début utilisé cette Fonction pour les appels a l'API 
    private function callAPI(string $method, string $url, ?array $data = null): ?array {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, true);
                if ($data !== null) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data !== null) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;

            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;

            case "GET":
                if ($data !== null) {
                    $url .= "?" . http_build_query($data);
                }
                break;
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ],
            CURLOPT_TIMEOUT => 10
        ]);

        $result = curl_exec($curl);
        if ($result === false) {
            $error = curl_error($curl);
            curl_close($curl);
            var_dump("CURL ERROR:", $error); // remove later
            return null;
        }
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $decoded = json_decode($result, true);

        // Optional debug
        if ($httpCode >= 400) {
            var_dump("HTTP ERROR:", $httpCode, $decoded);
            return null;
        }

        return $decoded;
    }

    // Ajoute un nouveau joueur
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

    // Récupère un joueur par son identifiant
    public function getJoueurById(int $id): ?array {
        $response = $this->callAPI("GET", $this->apiUrl, ['id' => $id]);

        if ($response === null || $response['status_code'] !== 200) {
            return null;
        }

        return $response['data'];
    }

    // Liste les joueurs actifs pouvant être sélectionnés pour un match
    public function listerLesJoueursSelectionnablesPourUnMatch(int $rencontreId): array {
        $tous = $this->listerTousLesJoueurs();
        $selectionnables = [];
        $participationCtrl = ParticipationControleur::getInstance();

        foreach ($tous as $joueur) {
            if (($joueur['statut'] ?? '') === 'ACTIF' &&
                !$participationCtrl->lejoueurEstDejaSurLaFeuilleDeMatch($rencontreId, $joueur['joueurId'])) {
                
                $selectionnables[] = $joueur;
            }
        }

        return $selectionnables;
    }

    // Récupère tous les joueurs
    public function listerTousLesJoueurs(): array {
        $response = $this->callAPI("GET", $this->apiUrl);
        
        if ($response === null || $response['status_code'] !== 200) {
            return [];
        }

        return $response['data'] ?? [];
    }

    // Modifie les informations d’un joueur
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

    // Supprime un joueur
    public function supprimerJoueur(int $id): bool {
        $response = $this->callAPI("DELETE", $this->apiUrl . "?id=" . $id);

        return $response !== null && $response['status_code'] === 200;
    }

    // on filtre côté frontend
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