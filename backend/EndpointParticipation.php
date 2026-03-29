<?php

/// Endpoint de gestion des participations des joueurs aux rencontres, permettant de créer, lire, mettre à jour et supprimer les participations, ainsi que de récupérer la feuille de match d'une rencontre spécifique
require_once 'Psr4AutoloaderClass.php';
require_once 'outils.php';

// Import des classes nécessaires pour gérer les participations et les feuilles de match
use R301\Psr4AutoloaderClass;
use R301\Controleur\ParticipationControleur;
use R301\Modele\Participation\Poste;
use R301\Modele\Participation\TitulaireOuRemplacant;

$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

// Instanciation du contrôleur de participation pour gérer les requêtes liées aux participations des joueurs aux rencontres
$controleur = ParticipationControleur::getInstance();

// Gestion de la méthode HTTP OPTIONS pour les requêtes CORS préflight, en répondant avec un code 204 No Content
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    deliver_response(204, "Méthode OPTIONS autorisée");
}

$user = getUser();
$role = $user->role;

// Récupération de la méthode HTTP utilisée pour la requête
$http_method = $_SERVER['REQUEST_METHOD'];

// Gestion des différentes méthodes HTTP pour les opérations sur les participations, avec traitement des erreurs et réponses appropriées
try {
    switch ($http_method) {

        // Gestion de la méthode GET pour récupérer les participations ou la feuille de match d'une rencontre, en vérifiant les paramètres de la requête et en appelant le contrôleur pour obtenir les données
        case 'GET':
            if (isset($_GET['rencontre_id'])) {
                $participation = $controleur->getFeuilleDeMatch((int) $_GET['rencontre_id']);
                deliver_response(200, "La requête a réussi", $participation->toArray());
            }
            deliver_response(200, "La requête a réussi", $controleur->listerToutesLesParticipations());
            break;

        // Gestion de la méthode POST pour créer une nouvelle participation, en vérifiant les données reçues et en appelant le contrôleur pour créer la participation
        case 'POST':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour ajouter une participation");
            }
            $data = json_decode(file_get_contents('php://input'), true);

            // Mise à jour de performance
            if (isset($_GET['id']) && isset($data['performance'])) {
                $success = $controleur->mettreAJourLaPerformance((int) $_GET['id'], $data['performance']);
                if ($success) {
                    deliver_response(200, "Note de performance mise à jour avec succès");
                } else {
                    deliver_response(400, "Erreur lors de la mise à jour");
                }
            }
            // Création d'une participation
            if (!$data || !isset($data['joueur_id'], $data['rencontre_id'], $data['poste'], $data['titulaire_ou_remplacant'])) {
                deliver_response(400, "JSON invalide ou champs manquants");
            }

            $success = $controleur->assignerUnParticipant(
                (int) $data['joueur_id'],
                (int) $data['rencontre_id'],
                Poste::fromName(strtoupper($data['poste'])),
                TitulaireOuRemplacant::fromName(strtoupper($data['titulaire_ou_remplacant']))
            );

            deliver_response(
                $success ? 201 : 400,
                $success ? "Participation créée avec succès" : "Erreur lors de la création (poste déjà occupé ou joueur déjà sur la feuille de match)"
            );
            break;

        case 'PUT':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour modifier une participation");
            }
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['joueur_id'], $data['poste'], $data['titulaire_ou_remplacant'])) {
                deliver_response(400, "JSON invalide ou champs manquants");
            }

            $success = $controleur->modifierParticipation(
                (int) $_GET['id'],
                Poste::fromName(strtoupper($data['poste'])),
                TitulaireOuRemplacant::fromName(strtoupper($data['titulaire_ou_remplacant'])),
                (int) $data['joueur_id']
            );

            deliver_response(
                $success ? 200 : 400,
                $success ? "Participation mise à jour" : "Erreur lors de la mise à jour (poste déjà occupé ou joueur déjà sur la feuille de match)"
            );
            break;

        case 'DELETE':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour ajouter un joueur");
            }
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }

            // Suppression de performance uniquement
            if (isset($_GET['action']) && $_GET['action'] === 'delete_performance') {
                $success = $controleur->supprimerLaPerformance((int) $_GET['id']);
                if ($success) {
                    deliver_response(200, "Performance supprimée avec succès");
                } else {
                    deliver_response(400, "Erreur lors de la suppression");
                }
            }

            // Suppression de la participation
            $success = $controleur->supprimerLaParticipation((int) $_GET['id']);
            if ($success) {
                deliver_response(200, "Participation supprimée avec succès");
            } else {
                deliver_response(400, "Impossible de supprimer");
            }
            break;

        default:
            deliver_response(405, "Méthode HTTP non autorisée");
    }
} catch (Throwable $e) {
    deliver_response(500, "Erreur serveur : " . $e->getMessage());
}
?>