<?php
require_once 'Psr4AutoloaderClass.php';

use R301\Psr4AutoloaderClass;
use R301\Controleur\RencontreControleur;
use R301\Modele\Rencontre\RencontreLieu;

$loader = new Psr4AutoloaderClass();
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

//
// Token a ajouter ici
//

$http_method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($http_method) {

        case 'GET':
            if (isset($_GET['id'])) {
                $id=htmlspecialchars($_GET['id']);
                $rencontre = $controleur->getRenconterById($id);
                if ($rencontre === null) {
                    deliver_response(404, "Rencontre non trouvée");
                }
                deliver_response(200, "La requête a réussi", $rencontre);
            }

            $rencontres = $controleur->listerToutesLesRencontres();
            deliver_response(200, "La requête a réussi", $rencontres);
            break;

        case 'POST': // CREATE
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['dateHeure'], $data['equipeAdverse'], $data['adresse'], $data['lieu'])) {
                deliver_response(400, "JSON invalide ou champs manquants");
            }

            $dateHeure = new DateTime($data['dateHeure']);
            $lieu = RencontreLieu::fromName(strtoupper($data['lieu']));

            $success = $controleur->ajouterRencontre(
                $dateHeure,
                $data['equipeAdverse'],
                $data['adresse'],
                $lieu
            );

            deliver_response(
                $success ? 201 : 400,
                $success ? "Rencontre créée avec succès" : "Erreur lors de la création (date déjà passée ou autre)"
            );
            break;

        case 'PUT': // UPDATE (détails avant le match)
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id=htmlspecialchars($_GET['id']);

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['dateHeure'], $data['equipeAdverse'], $data['adresse'], $data['lieu'])) {
                deliver_response(400, "JSON invalide ou champs manquants");
            }

            $rencontre = $controleur->getRenconterById($id);
            if ($rencontre === null) {
                deliver_response(404, "Rencontre non trouvée");
            }

            $dateHeure = new DateTime($data['dateHeure']);
            $lieu = RencontreLieu::fromName(strtoupper($data['lieu']));

            $success = $controleur->modifierRencontre(
                $id, $dateHeure, $data['equipeAdverse'], $data['adresse'], $lieu
            );

            deliver_response(
                $success ? 200 : 400,
                $success ? "Rencontre mise à jour" : "Erreur lors de la mise à jour (match déjà passé ou date invalide)"
            );
            break;

        case 'PATCH': // ENREGISTRER RÉSULTAT (action spécifique)
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id=htmlspecialchars($_GET['id']);

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['resultat'])) {
                deliver_response(400, "JSON invalide ou champ 'resultat' manquant");
            }

            $rencontre = $controleur->getRenconterById($id);
            if ($rencontre === null) {
                deliver_response(404, "Rencontre non trouvée");
            }

            $success = $controleur->enregistrerResultat($id, $data['resultat']);

            deliver_response(
                $success ? 200 : 400,
                $success ? "Résultat enregistré avec succès" : "Erreur (match non passé ou déjà enregistré)"
            );
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id=htmlspecialchars($_GET['id']);

            $rencontre = $controleur->getRenconterById($id);
            if ($rencontre === null) {
                deliver_response(404, "Rencontre non trouvée");
            }

            $success = $controleur->supprimerRencontre($id);

            deliver_response(
                $success ? 200 : 400,
                $success ? "Rencontre supprimée avec succès" : "Impossible de supprimer (résultat déjà enregistré)"
            );
            break;

        default:
            deliver_response(405, "Méthode HTTP non autorisée");
    }
} catch (Throwable $e) {
    deliver_response(500, "Erreur serveur : " . $e->getMessage());
}