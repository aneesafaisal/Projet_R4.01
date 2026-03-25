<?php
require_once 'Psr4AutoloaderClass.php';
require_once 'token.php'; 

use R301\Psr4AutoloaderClass;
use R301\Controleur\JoueurControleur;

$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

$controleur = JoueurControleur::getInstance();

function deliver_response(int $status_code, string $status_message, $data = null): void
{
    http_response_code($status_code);
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id']; 
                $joueur = $controleur->getJoueurById($id);
                if ($joueur === null) {
                    deliver_response(404, "Joueur non trouvé");
                }
                deliver_response(200, "La requête a réussi", $joueur);
            }

            $joueurs = $controleur->listerTousLesJoueurs();
            deliver_response(200, "La requête a réussi", $joueurs);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !is_array($data)) {
                deliver_response(400, "JSON invalide");
            }

            if (!isset($data['nom'], $data['prenom'], $data['numeroDeLicence'], $data['dateDeNaissance'], $data['tailleEnCm'], $data['poidsEnKg'], $data['statut'])) {
                deliver_response(400, "Champs manquants");
            }

            try {
                $date = new DateTime($data['dateDeNaissance']);
            } catch (Exception $e) {
                deliver_response(400, "Format de date invalide (YYYY-MM-DD attendu)");
            }

            $success = $controleur->ajouterJoueur(
                $data['nom'], $data['prenom'], $data['numeroDeLicence'], $date,
                (int)$data['tailleEnCm'], (int)$data['poidsEnKg'], $data['statut']
            );

            deliver_response($success ? 201 : 400, $success ? "Joueur créé" : "Erreur lors de la création");
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }

            $id = (int)$_GET['id']; 

            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !is_array($data)) {
                deliver_response(400, "JSON invalide");
            }

            if (!isset($data['nom'], $data['prenom'], $data['numeroDeLicence'], $data['dateDeNaissance'], $data['tailleEnCm'], $data['poidsEnKg'], $data['statut'])) {
                deliver_response(400, "Champs manquants");
            }

            $joueurExistant = $controleur->getJoueurById($id);
            if ($joueurExistant === null) {
                deliver_response(404, "Joueur non trouvé");
            }

            try {
                $date = new DateTime($data['dateDeNaissance']);
            } catch (Exception $e) {
                deliver_response(400, "Format de date invalide (YYYY-MM-DD attendu)");
            }

            $success = $controleur->modifierJoueur(
                $id, $data['nom'], $data['prenom'], $data['numeroDeLicence'],
                $date, (int)$data['tailleEnCm'], (int)$data['poidsEnKg'], $data['statut']
            );

            deliver_response($success ? 200 : 400, $success ? "Joueur mis à jour" : "Erreur lors de la mise à jour");
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }

            $id = (int)$_GET['id']; 

            $success = $controleur->supprimerJoueur($id);

            deliver_response($success ? 200 : 404, $success ? "Joueur supprimé avec succès" : "Joueur non trouvé");
            break;

        default:
            deliver_response(405, "Méthode HTTP non autorisée");
    }
} catch (Throwable $e) {
    deliver_response(500, "Erreur serveur : " . $e->getMessage());
}
?>