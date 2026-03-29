<?php

// Point d'entrée pour les opérations liées aux commentaires, gérant les requêtes HTTP GET, POST et DELETE pour lister, ajouter et supprimer des commentaires respectivement, avec vérification de l'authentification via token et gestion des erreurs
require_once 'Psr4AutoloaderClass.php';
require_once 'outils.php';

// Importation des classes nécessaires
use R301\Psr4AutoloaderClass;
use R301\Controleur\CommentaireControleur;
use R301\Controleur\JoueurControleur;

// Enregistrement de l'autoloader pour charger automatiquement les classes du namespace R301
$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);
$controleur = CommentaireControleur::getInstance();

// Gestion de la méthode HTTP OPTIONS pour les requêtes CORS préflight, en répondant avec un code 204 No Content
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    deliver_response(204, "Méthode OPTIONS autorisée");
}

$user = getUser();
$role = $user->role;

// Récupération de la méthode HTTP utilisée pour la requête
$http_method = $_SERVER['REQUEST_METHOD'];

// Gestion des différentes méthodes HTTP pour les opérations sur les commentaires, avec traitement des erreurs et réponses appropriées
try {
    switch ($http_method) {
        // Gestion de la méthode GET pour lister les commentaires d'un joueur, en vérifiant que le paramètre joueur_id est présent et en récupérant les commentaires via le contrôleur
        case 'GET':
            if (!isset($_GET['joueur_id'])) {
                deliver_response(400, "joueur_id manquant (paramètre obligatoire pour lister les commentaires)");
            }

            $joueurId = (int) $_GET['joueur_id'];

            $joueurControleur = JoueurControleur::getInstance();
            $joueur = $joueurControleur->getJoueurById($joueurId);

            if ($joueur === null) {
                deliver_response(404, "Joueur non trouvé");
            }

            $commentaires = $controleur->listerLesCommentairesDuJoueur($joueur);
            deliver_response(200, "La requête a réussi", $commentaires);
            break;

        // Gestion de la méthode POST pour ajouter un commentaire, en vérifiant que les champs nécessaires sont présents dans le JSON de la requête et en appelant le contrôleur pour créer le commentaire
        case 'POST':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour ajouter un commentaire");
            }
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !is_array($data)) {
                deliver_response(400, "JSON invalide");
            }

            if (!isset($data['contenu'], $data['joueur_id'])) {
                deliver_response(400, "Champs manquants (contenu et joueur_id obligatoires)");
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

        // Gestion de la méthode DELETE pour supprimer un commentaire, en vérifiant que le paramètre id est présent et en appelant le contrôleur pour supprimer le commentaire
        case 'DELETE':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour ajouter un commentaire");
            }
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }

            $id = (int) $_GET['id'];

            $success = $controleur->supprimerCommentaire($id);

            deliver_response(
                $success ? 200 : 404,
                $success ? "Commentaire supprimé avec succès" : "Commentaire non trouvé"
            );
            break;

        default:
            deliver_response(405, "Méthode HTTP non autorisée");
    }
} catch (Throwable $e) {
    deliver_response(500, "Erreur serveur : " . $e->getMessage());
}
?>