<?php

// Déclaration du namespace
namespace R301\Controleur;

// Import des classes nécessaires
use DateTime;
use R301\Modele\Joueur\Commentaire\Commentaire;
use R301\Modele\Joueur\Commentaire\CommentaireDAO;
use R301\Modele\Joueur\Joueur;
use R301\Modele\Joueur\JoueurDAO;
use R301\Modele\Joueur\JoueurStatut;
use R301\Modele\Statistiques\StatistiquesEquipe;
use R301\Modele\Statistiques\StatistiquesJoueurs;


// Contrôleur dédié au calcul des statistiques
class StatistiquesControleur
{
    private static ?StatistiquesControleur $instance = null;
    private readonly RencontreControleur $rencontres;
    private readonly ParticipationControleur $participations;

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct()
    {
        $this->rencontres = RencontreControleur::getInstance();
        $this->participations = ParticipationControleur::getInstance();
    }

    // Retourne l'instance unique du contrôleur
    public static function getInstance(): StatistiquesControleur
    {
        if (self::$instance == null) {
            self::$instance = new StatistiquesControleur();
        }
        return self::$instance;
    }

    // Calcule et retourne les statistiques globales de l’équipe
    public function getStatistiquesEquipe(): StatistiquesEquipe
    {
        return new StatistiquesEquipe($this->rencontres->listerToutesLesRencontres());
    }

    // Calcule et retourne les statistiques des joueurs
    public function getStatistiquesJoueurs(): StatistiquesJoueurs
    {
        return new StatistiquesJoueurs($this->participations->listerToutesLesParticipations(), $this->rencontres->listerToutesLesRencontres());
    }
}