<?php

// Point d'entrée pour les requêtes liées aux statistiques de l'équipe et des joueurs, gérant les méthodes GET pour récupérer les statistiques et vérifiant le token d'authentification
require_once 'Psr4AutoloaderClass.php';
require_once 'outils.php';

// Importation des classes nécessaires du namespace R301
use R301\Psr4AutoloaderClass;
use R301\Controleur\StatistiquesControleur;

// Enregistrement de l'autoloader pour charger automatiquement les classes du namespace R301
$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('R301', __DIR__);

// Obtention de l'instance du contrôleur des statistiques pour gérer les opérations liées aux statistiques de l'équipe et des joueurs
$controleur = StatistiquesControleur::getInstance();

// Gestion de la méthode HTTP OPTIONS pour les requêtes CORS préflight, en répondant avec un code 204 No Content
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    deliver_response(204, "Méthode OPTIONS autorisée");
}

// Récupération de la méthode HTTP utilisée pour la requête
$http_method = $_SERVER['REQUEST_METHOD'];

// Gestion des différentes méthodes HTTP pour les opérations sur les statistiques, avec traitement des erreurs et réponses appropriées
try {
    switch ($http_method) {

        // Gestion de la méthode GET pour récupérer les statistiques de l'équipe et des joueurs, en appelant le contrôleur pour obtenir les statistiques et en répondant avec un code 200 OK et les données des statistiques si la récupération a réussi ou 500 Internal Server Error si une erreur est survenue lors de la récupération des statistiques
        case 'GET':
            $statistiquesEquipe = $controleur->getStatistiquesEquipe();
            $statistiquesJoueurs = $controleur->getStatistiquesJoueurs();

            $data = [
                'statistiques_equipe' => $statistiquesEquipe,
                'statistiques_joueurs' => $statistiquesJoueurs
            ];
            deliver_response(200, "La requête a réussi", $data);
            break;

        default:
            deliver_response(405, "Méthode HTTP non autorisée");
    }
} catch (Throwable $e) {
    deliver_response(500, "Erreur serveur : " . $e->getMessage());
}
?>