<?php

namespace R301\Controleur;

// Contrôleur dédié au calcul des statistiques
class StatistiquesControleur
{
    private static ?StatistiquesControleur $instance = null;
    private string $apiUrl = "https://equipe.alwaysdata.net/EndpointStatistiques.php";
    private string $token = "";

    // Constructeur privé (ajout minimal pour charger le token)
    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->token = $_SESSION['token'] ?? $_SESSION['jwt'] ?? '';
    }

    // Retourne l'instance unique du contrôleur
    public static function getInstance(): StatistiquesControleur
    {
        if (self::$instance == null) {
            self::$instance = new StatistiquesControleur();
        }
        return self::$instance;
    }

    // Fonction générique pour appeler l'API du backend
    private function callAPI()
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
                'header' => "Content-Type: application/json\r\n" .
                    "Authorization: Bearer " . $this->token . "\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($this->apiUrl, false, $context);
        return json_decode($response, true);
    }

    // Récupère les statistiques globales de l'équipe (calcul à partir des données brutes)
    public function getStatistiquesEquipe()
    {
        $res = $this->callAPI();

        if (($res['status_code'] ?? 0) !== 200) {
            return [];
        }

        $rencontres = $res['data']['statistiques_equipe']['rencontres'] ?? [];

        $victoires = 0;
        $nuls = 0;
        $defaites = 0;

        foreach ($rencontres as $r) {
            if (empty($r['resultat']))
                continue;
            $resultat = strtoupper($r['resultat']);
            if ($resultat === 'VICTOIRE')
                $victoires++;
            elseif ($resultat === 'NUL')
                $nuls++;
            elseif ($resultat === 'DEFAITE')
                $defaites++;
        }

        $total = $victoires + $nuls + $defaites;
        $pourcV = $total > 0 ? round(($victoires / $total) * 100) : 0;
        $pourcN = $total > 0 ? round(($nuls / $total) * 100) : 0;
        $pourcD = $total > 0 ? round(($defaites / $total) * 100) : 0;

        return [
            'nbVictoires' => $victoires,
            'nbNuls' => $nuls,
            'nbDefaites' => $defaites,
            'pourcentageDeVictoires' => $pourcV,
            'pourcentageDeNuls' => $pourcN,
            'pourcentageDeDefaites' => $pourcD,
        ];
    }

    // Récupère les statistiques des joueurs (calcul à partir des données brutes)
    public function getStatistiquesJoueurs()
    {
        $res = $this->callAPI();

        if (($res['status_code'] ?? 0) !== 200) {
            return [];
        }

        $participations = $res['data']['statistiques_joueurs']['participations'] ?? [];

        $statsParJoueur = [];
        foreach ($participations as $p) {
            $j = $p['participant'];
            $id = $j['joueurId'];

            if (!isset($statsParJoueur[$id])) {
                $statsParJoueur[$id] = [
                    'joueur' => $j,
                    'posteLePlusPerformant' => $p['poste'] ?? '',
                    'nbRencontresConsecutivesADate' => 0,
                    'nbTitularisations' => 0,
                    'nbRemplacant' => 0,
                    'moyenneDesEvaluations' => 0,
                    'pourcentageDeMatchsGagnes' => 0,
                    'totalMatchs' => 0,
                    'victoires' => 0,
                ];
            }

            $s = &$statsParJoueur[$id];
            $s['totalMatchs']++;

            if (($p['titulaireOuRemplacant'] ?? '') === 'TITULAIRE') {
                $s['nbTitularisations']++;
            } else {
                $s['nbRemplacant']++;
            }

            // Moyenne des évaluations
            if (!empty($p['performance'])) {
                $valeur = match (strtoupper($p['performance'])) {
                    'EXCELLENTE' => 5,
                    'BONNE' => 4,
                    'MOYENNE' => 3,
                    'MAUVAISE' => 2,
                    'CATASTROPHIQUE' => 1,
                    default => 0,
                };
                $s['moyenneDesEvaluations'] = ($s['moyenneDesEvaluations'] * ($s['totalMatchs'] - 1) + $valeur) / $s['totalMatchs'];
            }

            // Pourcentage de matchs gagnés
            if (!empty($p['rencontre']['resultat']) && strtoupper($p['rencontre']['resultat']) === 'VICTOIRE') {
                $s['victoires']++;
            }
            $s['pourcentageDeMatchsGagnes'] = $s['totalMatchs'] > 0 ? round(($s['victoires'] / $s['totalMatchs']) * 100) : 0;
        }

        return array_values($statsParJoueur);
    }
}