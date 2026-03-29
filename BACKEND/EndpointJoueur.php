<?php

// Point d'entrée pour les requêtes liées aux joueurs, gérant les méthodes HTTP GET, POST, PUT et DELETE pour récupérer, créer, modifier et supprimer des joueurs, avec vérification du token d'authentification et gestion des réponses JSON
require_once 'Psr4AutoloaderClass.php';
require_once 'outils.php'; 

// Importation des classes nécessaires
use R301\Psr4AutoloaderClass;
use R301\Controleur\JoueurControleur;

// Enregistrement de l'autoloader pour charger automatiquement les classes du namespace R301
$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

// Obtention de l'instance du contrôleur des joueurs pour gérer les opérations liées aux joueurs
$controleur = JoueurControleur::getInstance();

// Gestion de la méthode HTTP OPTIONS pour les requêtes CORS préflight, en répondant avec un code 204 No Content
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    deliver_response(204, "Méthode OPTIONS autorisée");
}

$user = getUser();
$role = $user->role;

// Récupération de la méthode HTTP utilisée pour la requête
$http_method = $_SERVER['REQUEST_METHOD'];

// Gestion des différentes méthodes HTTP pour les opérations sur les joueurs, avec traitement des erreurs et réponses appropriées
try {
    switch ($http_method) {

        // Gestion de la méthode GET pour récupérer les joueurs, en vérifiant si un paramètre id est présent pour récupérer un joueur spécifique ou tous les joueurs sinon
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

        // Gestion de la méthode POST pour créer un nouveau joueur, en vérifiant que les données JSON sont valides et que tous les champs requis sont présents, puis en appelant le contrôleur pour ajouter le joueur
        case 'POST':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour ajouter un joueur");
            }
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

        // Gestion de la méthode PUT pour modifier un joueur existant, en vérifiant que l'id du joueur à modifier est présent, que les données JSON sont valides et que tous les champs requis sont présents, puis en appelant le contrôleur pour modifier le joueur    
        case 'PUT':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour modifier un joueur");
            }
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
            
        // Gestion de la méthode DELETE pour supprimer un joueur, en vérifiant que l'id du joueur à supprimer est présent, puis en appelant le contrôleur pour supprimer le joueur et en répondant avec un code 200 OK si la suppression a réussi ou 404 Not Found si le joueur n'a pas été trouvé    
        case 'DELETE':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour supprimer un joueur");
            }
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