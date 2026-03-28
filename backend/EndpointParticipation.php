<?php

// Inclusion des fichiers nécessaires pour l'autoloading et la vérification du token d'authentification
require_once 'Psr4AutoloaderClass.php';
require_once 'token.php'; 

// Importation des classes nécessaires du namespace R301
use R301\Psr4AutoloaderClass;
use R301\Controleur\ParticipationControleur;
use R301\Modele\Participation\Poste;
use R301\Modele\Participation\TitulaireOuRemplacant;

// Enregistrement de l'autoloader pour charger automatiquement les classes du namespace R301
$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

// Obtention de l'instance du contrôleur de participation pour gérer les opérations liées aux participations des joueurs aux rencontres
$controleur = ParticipationControleur::getInstance();

// Fonction pour délivrer une réponse HTTP au client, en définissant le code de statut, le message et les données, et en encodant la réponse en JSON
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

// Gestion de la méthode HTTP OPTIONS pour les requêtes CORS préflight, en répondant avec un code 204 No Content
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    deliver_response(204, "Méthode OPTIONS autorisée");
}

// Vérification de l'authentification via token, en répondant avec un code 401 Unauthorized si le token
if (!verifyToken()) {
    deliver_response(401, "Token invalide ou manquant");
}

// Récupération de la méthode HTTP utilisée pour la requête
$http_method = $_SERVER['REQUEST_METHOD'];

// Gestion des différentes méthodes HTTP pour les opérations sur les participations, avec traitement des erreurs et réponses appropriées
try {
    switch ($http_method) {

        // Gestion de la méthode GET pour récupérer les participations, en vérifiant si un paramètre rencontre_id est présent pour récupérer la feuille de match d'une rencontre spécifique ou toutes les participations sinon
        case 'GET':
            if (isset($_GET['rencontre_id'])) {
                $id = (int)$_GET['rencontre_id'];
                $participation = $controleur->getFeuilleDeMatch($id);
                if (empty($participation->getParticipants())) {
                    deliver_response(404, "Participation non trouvée");
                }
                deliver_response(200, "La requête a réussi", $participation);
            }

            $participation = $controleur->listerToutesLesParticipations();
            deliver_response(200, "La requête a réussi", $participation);
            break;

        // Gestion de la méthode POST pour créer une nouvelle participation, en vérifiant que les champs nécessaires sont présents dans le JSON de la requête et en appelant le contrôleur pour créer la participation    
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

            if ($success) {
                deliver_response(201, "Participation créée avec succès");
            } else {
                deliver_response(400, "Erreur lors de la création (poste déjà occupé ou joueur déjà sur la feuille de match)");
            }
            break;

        // Gestion de la méthode PUT pour modifier une participation, en vérifiant que l'id de la participation à modifier est présent, que les champs nécessaires sont présents dans le JSON de la requête, puis en appelant le contrôleur pour modifier la participation et en répondant avec un code 200 OK si la modification a réussi ou 404 Not Found si la participation n'a pas été trouvée        
        case 'PUT':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id = (int)$_GET['id'];

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['joueur_id'], $data['poste'], $data['titulaire_ou_remplacant'])) {
                deliver_response(400, "JSON invalide ou champs manquants");
            }

            $participation = $controleur->getFeuilleDeMatch($id);
            if (empty($participation->getParticipants())) {
                deliver_response(404, "Participation non trouvée");
            }

            $poste = Poste::fromName(strtoupper($data['poste']));
            $titulaireOuRemplacant = TitulaireOuRemplacant::fromName(strtoupper($data['titulaire_ou_remplacant']));
            $joueurId = (int)$data['joueur_id'];

            $success = $controleur->modifierParticipation(
                $id, $poste, $titulaireOuRemplacant, $joueurId
            );

            if ($success) {
                deliver_response(200, "Participation mise à jour");
            } else {
                deliver_response(400, "Erreur lors de la mise à jour (poste déjà occupé ou joueur déjà sur la feuille de match)");
            }
            break;
        
        // Gestion de la méthode PATCH pour mettre à jour la note de performance d'une participation, en vérifiant que l'id de la participation à modifier est présent, que le champ performance est présent dans le JSON de la requête, puis en appelant le contrôleur pour mettre à jour la performance et en répondant avec un code 200 OK si la mise à jour a réussi ou 400 Bad Request si la mise à jour a échoué (match non encore passé ou note invalide)    
        case 'PATCH':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id = (int)$_GET['id'];

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['performance'])) {
                deliver_response(400, "JSON invalide ou champ 'performance' manquant");
            }

            $participation = $controleur->getFeuilleDeMatch($id);
            if (empty($participation->getParticipants())) {
                deliver_response(404, "Participation non trouvée");
            }

            $success = $controleur->mettreAJourLaPerformance($id, $data['performance']);

            if ($success) {
                deliver_response(200, "Note de performance mise à jour avec succès");
            } else {
                deliver_response(400, "Erreur lors de la mise à jour (match non encore passé ou note invalide)");
            }
            break;
            
        // Gestion de la méthode DELETE pour supprimer une participation, en vérifiant que l'id de la participation à supprimer est présent, puis en appelant le contrôleur pour supprimer la participation et en répondant avec un code 200 OK si la suppression a réussi ou 404 Not Found si la participation n'a pas été trouvée    
        case 'DELETE':
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id = (int)$_GET['id'];

            $participation = $controleur->getFeuilleDeMatch($id);
            if (empty($participation->getParticipants())) {
                deliver_response(404, "Participation non trouvée");
            }

            $success = $controleur->supprimerLaParticipation($id);

            if ($success) {
                deliver_response(200, "Participation supprimée avec succès");
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