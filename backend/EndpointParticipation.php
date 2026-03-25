<?php
require_once 'Psr4AutoloaderClass.php';

use backend\Psr4AutoloaderClass;
use R301\Controleur\ParticipationControleur;
use R301\Modele\Participation\Poste;
use R301\Modele\Participation\TitulaireOuRemplacant;

$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

$controleur = ParticipationControleur::getInstance();

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



$http_method = $_SERVER['REQUEST_METHOD'];
if 
try {
    switch ($http_method) {

        case 'GET':
            if (isset($_GET['id'])) {
                $id=(int)$_GET['id'];
                $participation = $controleur->getFeuilleDeMatch($id);
                if (empty($participation -> getParticipations())) {
                    deliver_response(404, "Participation non trouvée");
                }
                deliver_response(200, "La requête a réussi", $participation);
            }

            $participation = $controleur->listerToutesLesParticipations();
            deliver_response(200, "La requête a réussi", $participation);
            break;

        case 'POST': 
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['joueur_id'], $data['rencontre_id'], $data['poste'], $data['titulaire_ou_remplacant'])) {
                deliver_response(400, "JSON invalide ou champs manquants");
            }

            $joueurId = (int)$data['joueur_id'];
            $rencontreId = (int)$data['rencontre_id'];
            $poste = Poste::fromName(strtoupper($data['poste']));
            $titulaireOuRemplacant = TitulaireOuRemplacant::fromName(strtoupper($data['titulaire_ou_remplacant']));
            
            $success = $controleur->assignerUnParticipant(
                $joueurId,
                $rencontreId,
                $poste,
                $titulaireOuRemplacant
            );

            if ($success){
                deliver_response(201,  "Participation créée avec succès");
            } else {
                deliver_response(400, "Erreur lors de la création (poste déjà occupé ou joueur déjà sur la feuille de match)");
            }
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id=(int)$_GET['id'];

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['joueur_id'], $data['poste'], $data['titulaire_ou_remplacant'])) {
                deliver_response(400, "JSON invalide ou champs manquants");
            }

            $participation = $controleur->getFeuilleDeMatch($id);
            if (empty($participation -> getParticipations())) {
                deliver_response(404, "Participation non trouvée");
            }

            $poste = Poste::fromName(strtoupper($data['poste']));
            $titulaireOuRemplacant = TitulaireOuRemplacant::fromName(strtoupper($data['titulaire_ou_remplacant']));
            $joueurId = (int)$data['joueur_id'];

            $success = $controleur->modifierParticipation(
                $id, $poste, $titulaireOuRemplacant, $joueurId
            );

            if ($success){
                deliver_response(200,  "Participation mise à jour");
            } else {
                deliver_response(400, "Erreur lors de la mise à jour (poste déjà occupé ou joueur déjà sur la feuille de match)");
            }
            break;

        case 'PATCH': // On l'utilise pour enregistrer la note de performance
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id=(int)$_GET['id'];

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['performance'])) {
                deliver_response(400, "JSON invalide ou champ 'performance' manquant");
        }

            $participation = $controleur->getFeuilleDeMatch($id);
            if (empty($participation -> getParticipations())) {
                deliver_response(404, "Participation non trouvée");
            }

            $success = $controleur->mettreAJourLaPerformance($id, $data['performance']);

            if ($success){
                deliver_response(200, "Note de performance mise à jour avec succès");
            } else {
                deliver_response(400, "Erreur lors de la mise à jour (match non encore passé ou note invalide)");
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id=(int)$_GET['id'];

            $participation = $controleur->getFeuilleDeMatch($id);
            if (empty($participation -> getParticipations())) {
                deliver_response(404, "Participation non trouvée");
            }

            $success = $controleur->supprimerLaParticipation($id);

            if ($success){
                deliver_response(200,  "Participation supprimée avec succès");
            } else {
                deliver_response(400, "Impossible de supprimer (la participation est liée à une rencontre passée)");
            }
            break;

        default:
            deliver_response(405, "Méthode HTTP non autorisée");
    }
} catch (Throwable $e) {
    deliver_response(500, "Erreur serveur : " . $e->getMessage());
}
?>