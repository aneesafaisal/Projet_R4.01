<?php
require_once 'Psr4AutoloaderClass.php';

use R301\Psr4AutoloaderClass;
use R301\Controleur\CommentaireControleur;
use R301\Controleur\JoueurControleur;

$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

$controleur = CommentaireControleur::getInstance();

function deliver_response(int $status_code, string $status_message, $data = null): void
{
    http_response_code($status_code);
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    $response = [
        'status_code'    => $status_code,
        'status_message' => $status_message,
        'data'           => $data
    ];

    echo json_encode($response);
    exit;
}

//
// Token a ajouter ici
//

/// Identification du type de méthode HTTP envoyée par le client
$http_method = $_SERVER['REQUEST_METHOD'];
try {
    switch ($http_method){
        case "OPTIONS" :
            deliver_response("204", "Méthode OPTIONS autorisée - Requête CORS acceptée");
            break;

        case "GET" :
            if(!isset($_GET['joueur_id']))
            {
                deliver_response(400, "joueur_id manquant (paramètre obligatoire pour lister les commentaires)");
                break;
            }

            $joueurId = htmlspecialchars($_GET['joueur_id']);

            // On récupère l'objet Joueur (nécessaire pour la méthode du contrôleur)
            $joueurControleur = JoueurControleur::getInstance();
            $joueur = $joueurControleur->getJoueurById($joueurId);

            if ($joueur === null) {
                deliver_response(404, "Joueur non trouvé");
            } else {
                $commentaires = $controleur->listerLesCommentairesDuJoueur($joueur);
                deliver_response(200, "La requête a réussi", $commentaires);
            }
            break;

        case "POST":
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData, true);

            if (!$data || !is_array($data)) {
                deliver_response(400, "JSON invalide");
                break;
            }

            if (!isset($data['contenu'], $data['joueur_id'])) {
                deliver_response(400, "Champs manquants (contenu et joueur_id obligatoires)");
                break;
            }

            $success = $controleur->ajouterCommentaire(
                $data['contenu'],
                $data['joueur_id']
            );

            deliver_response(
                $success ? 201 : 400,
                $success ? "Commentaire créé" : "Erreur lors de la création du commentaire"
            );
            break;

        case "DELETE":
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
                break;
            }

            $id = htmlspecialchars($_GET['id']);

            $delete = $controleur->supprimerCommentaire($id);

            if ($delete === true) {
                deliver_response(200, "La requête a réussi.");
            } else {
                deliver_response(404, "Commentaire non trouvé");
            }
            break;

        default:
            deliver_response(405, "Méthode HTTP non autorisée");
    }
} catch (Throwable $e) {
    deliver_response(500, "Erreur serveur : " . $e->getMessage());
}
?>