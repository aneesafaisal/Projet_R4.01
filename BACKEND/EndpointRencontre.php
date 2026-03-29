<?php

// Point d'entrée pour les requêtes liées aux rencontres, gérant les méthodes HTTP GET, POST, PUT, PATCH et DELETE pour lister, créer, modifier, enregistrer le résultat et supprimer des rencontres, avec vérification du token d'authentification et gestion des réponses JSON
require_once 'Psr4AutoloaderClass.php';
require_once 'token.php';

// Importation des classes nécessaires
use R301\Controleur\RencontreControleur;
use R301\Modele\Rencontre\RencontreLieu;

// Enregistrement de l'autoloader pour charger automatiquement les classes du namespace R301
$loader = new R301\Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

// Obtention de l'instance du contrôleur des rencontres pour gérer les opérations liées aux rencontres
$controleur = RencontreControleur::getInstance();

// Fonction pour délivrer une réponse HTTP au client, en définissant le code de statut, le message et les données, et en encodant la réponse en JSON
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

// Gestion de la méthode HTTP OPTIONS pour les requêtes CORS préflight, en répondant avec un code 204 No Content
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    deliver_response(204, "Méthode OPTIONS autorisée");
}

// Vérification de l'authentification via token, en répondant avec un code 401 Unauthorized si le token
$user = verifyToken();
if ($user === null) {
    deliver_response(401, "Token invalide ou manquant");
}
$role = $user['role'];

// Récupération de la méthode HTTP utilisée pour la requête
$http_method = $_SERVER['REQUEST_METHOD'];

// Gestion des différentes méthodes HTTP pour les opérations sur les rencontres, avec traitement des erreurs et réponses appropriées
try {
    switch ($http_method) {

        // Gestion de la méthode GET pour récupérer les rencontres, en vérifiant si un paramètre id est présent pour récupérer une rencontre spécifique ou toutes les rencontres sinon
        case 'GET':
            if (isset($_GET['id'])) {
                $id = (int) $_GET['id'];
                $rencontre = $controleur->getRenconterById($id);
                if ($rencontre === null) {
                    deliver_response(404, "Rencontre non trouvée");
                }
                deliver_response(200, "La requête a réussi", $rencontre);
            }
            deliver_response(200, "La requête a réussi", $controleur->listerToutesLesRencontres());
            break;

        // Gestion de la méthode POST pour créer une rencontre, en vérifiant que les champs nécessaires sont présents dans le JSON de la requête, puis en appelant le contrôleur pour créer la rencontre et en répondant avec un code 201 Created si la création a réussi ou 400 Bad Request si la création a échoué (JSON invalide ou champs manquants)    
        case 'POST':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour ajouter une rencontre");
            }
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

        // Gestion de la méthode PUT pour modifier une rencontre, en vérifiant que l'id de la rencontre à modifier est présent, que les champs nécessaires sont présents dans le JSON de la requête, puis en appelant le contrôleur pour modifier la rencontre et en répondant avec un code 200 OK si la modification a réussi ou 400 Bad Request si la modification a échoué (JSON invalide, champs manquants ou rencontre non trouvée)    
        case 'PUT':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour modifier une rencontre");
            }
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id = (int) $_GET['id'];
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

        // Gestion de la méthode PATCH pour mettre à jour le résultat d'une rencontre, en vérifiant que l'id de la rencontre à modifier est présent, que le champ resultat est présent dans le JSON de la requête, puis en appelant le contrôleur pour enregistrer le résultat et en répondant avec un code 200 OK si l'enregistrement a réussi ou 400 Bad Request si l'enregistrement a échoué (rencontre non encore passée ou résultat invalide)    
        case 'PATCH':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour modifier une rencontre");
            }
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id = (int) $_GET['id'];
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

        // Gestion de la méthode DELETE pour supprimer une rencontre, en vérifiant que l'id de la rencontre à supprimer est présent, puis en appelant le contrôleur pour supprimer la rencontre et en répondant avec un code 200 OK si la suppression a réussi ou 404 Not Found si la rencontre n'a pas été trouvée    
        case 'DELETE':
            if ($role !== 'admin' && $role !== 'coach') {
                deliver_response(403, "Accès refusé : vous n'avez pas les permissions nécessaires pour supprimer une rencontre");
            }
            if (!isset($_GET['id'])) {
                deliver_response(400, "ID manquante");
            }
            $id = (int) $_GET['id'];
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