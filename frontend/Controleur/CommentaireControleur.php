<?php

namespace R301\Controleur;

class CommentaireControleur
{
    private static $instance = null;
    private string $apiUrl = "https://equipe.alwaysdata.net/EndpointCommentaire.php";

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Retourne l'instance unique du contrôleur
    public static function getInstance(): CommentaireControleur
    {
        if (self::$instance === null) {
            self::$instance = new CommentaireControleur();
        }
        return self::$instance;
    }

    // Permet d'appeler l'API du backend pour les opérations liées aux commentaires
    private function callAPI(string $method, string $url, $data = null, bool $withToken = false)
    {
        $headers = ["Content-Type: application/json"];

        if ($withToken) {
            $headers[] = "Authorization: Bearer " . ($_SESSION['token'] ?? '');
        }

        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers) . "\r\n",
                'content' => $data ? json_encode($data) : null,
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true);
    }

    // Lister les commentaires d'un joueur
    public function listerLesCommentairesDuJoueur(int $id): array
    {
        $res = $this->callAPI("GET", $this->apiUrl . "?joueur_id=" . $id);
        return $res['data'] ?? [];
    }

    // Ajouter un commentaire
    public function ajouterCommentaire(string $contenu, string $joueurId): bool
    {
        $res = $this->callAPI("POST", $this->apiUrl, [
            "contenu" => $contenu,
            "joueur_id" => $joueurId
        ], true);
        return isset($res['status_code']) && $res['status_code'] === 201;
    }

    // Supprimer un commentaire
    public function supprimerCommentaire(string $commentaireId): bool
    {
        $res = $this->callAPI("DELETE", $this->apiUrl . "?id=" . $commentaireId, null, true);
        return isset($res['status_code']) && $res['status_code'] === 200;
    }
}

?>