<?php
require_once 'Psr4AutoloaderClass.php';
require_once 'token.php'; 

use backend\Psr4AutoloaderClass;
use R301\Controleur\StatistiquesControleur; 

$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

$controleur = StatistiquesControleur::getInstance();

function deliver_response(int $status_code, string $status_message, $data = null)
{
    http_response_code($status_code);
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    $response = [
        'status_code'    => $status_code,
        'status_message' => $status_message,
        'data'           => $data
    ];

    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    deliver_response(204, "Méthode OPTIONS autorisée");
}


if (!verifyToken()) {
    deliver_response(401, "Token invalide ou manquant");
}

$http_method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($http_method) {

        case 'GET':
            $statistiquesEquipe = $controleur->getStatistiquesEquipe();
            $statistiquesJoueurs = $controleur->getStatistiquesJoueurs();

            $data = [
                'statistiques_equipe'  => $statistiquesEquipe,
                'statistiques_joueurs' => $statistiquesJoueurs
            ];
            deliver_response(200, "La requête a réussi", $data);
            break;

        default:
            deliver_response(405, "Méthode HTTP non autorisée");
    }
} catch (Throwable $e) {
    deliver_response(500, "Erreur serveur : " . $e->getMessage());
}
?>