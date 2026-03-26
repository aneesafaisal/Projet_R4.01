<?php
require_once 'Psr4AutoloaderClass.php';
require_once 'token.php';

use R301\Controleur\RencontreControleur;
use R301\Modele\Rencontre\RencontreLieu;

$loader = new R301\Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

$controleur = RencontreControleur::getInstance();

function deliver_response(int $status_code, string $status_message, $data = null): void
{
    http_response_code($status_code);
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    echo json_encode(['status_code' => $status_code, 'status_message' => $status_message, 'data' => $data]);
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
                $rencontre = $controleur->getRenconterById($id);
                if ($rencontre === null) {
                    deliver_response(404, "Rencontre non trouvée");
                }
                deliver_response(200, "La requête a réussi", $rencontre);
            }
            deliver_response(200, "La requête a réussi", $controleur->listerToutesLesRencontres());
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['dateHeure'], $data['equipeAdverse'], $data['adresse'], $data['lieu'])) {
                deliver_response(400, "JSON invalide ou champs manquants");
            }
            $success = $controleur->ajouterRencontre(
                new DateTime($data['dateHeure']),
                $data['equipeAdverse'],
                $data['adresse'],
                RencontreLieu::fromName(strtoupper($data['lieu']))
            );
            if ($success) {
                deliver_response(201, "Rencontre créée avec succès");
            } else {
                deliver_response(400, "Erreur lors de la création");
            }
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id = (int)$_GET['id'];
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['dateHeure'], $data['equipeAdverse'], $data['adresse'], $data['lieu'])) {
                deliver_response(400, "JSON invalide ou champs manquants");
            }
            $rencontre = $controleur->getRenconterById($id);
            if ($rencontre === null) {
                deliver_response(404, "Rencontre non trouvée");
            }
            $success = $controleur->modifierRencontre(
                $id,
                new DateTime($data['dateHeure']),
                $data['equipeAdverse'],
                $data['adresse'],
                RencontreLieu::fromName(strtoupper($data['lieu']))
            );
            if ($success) {
                deliver_response(200, "Rencontre mise à jour");
            } else {
                deliver_response(400, "Erreur lors de la mise à jour");
            }
            break;

        case 'PATCH':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id = (int)$_GET['id'];
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['resultat'])) {
                deliver_response(400, "Champ 'resultat' manquant");
            }
            $rencontre = $controleur->getRenconterById($id);
            if ($rencontre === null) {
                deliver_response(404, "Rencontre non trouvée");
            }
            $success = $controleur->enregistrerResultat($id, $data['resultat']);
            if ($success) {
                deliver_response(200, "Résultat enregistré avec succès");
            } else {
                deliver_response(400, "Erreur lors de l'enregistrement du résultat");
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id = (int)$_GET['id'];
            $rencontre = $controleur->getRenconterById($id);
            if ($rencontre === null) {
                deliver_response(404, "Rencontre non trouvée");
            }
            $success = $controleur->supprimerRencontre($id);
            if ($success) {
                deliver_response(200, "Rencontre supprimée avec succès");
            } else {
                deliver_response(400, "Impossible de supprimer la rencontre");
            }
            break;

        default:
            deliver_response(405, "Méthode HTTP non autorisée");
    }
} catch (Throwable $e) {
    deliver_response(500, "Erreur serveur : " . $e->getMessage());
}
?>