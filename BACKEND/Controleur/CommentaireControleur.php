<?php

// Déclaration du namespace pour organiser le code
namespace R301\Controleur;

// Import des classes nécessaires
use DateTime;
use R301\Modele\Joueur\Commentaire\Commentaire;
use R301\Modele\Joueur\Commentaire\CommentaireDAO;
use R301\Modele\Joueur\Joueur;
use R301\Modele\Joueur\JoueurDAO;
use R301\Modele\Joueur\JoueurStatut;

// Contrôleur gérant les opérations liées aux commentaires
class CommentaireControleur {
    private static ?CommentaireControleur $instance = null;
    private readonly CommentaireDAO $commentaires;

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
        $this->commentaires = CommentaireDAO::getInstance();
    }

    // Méthode permettant d'obtenir l'instance unique du contrôleur
    public static function getInstance(): CommentaireControleur {
        if (self::$instance == null) {
            self::$instance = new CommentaireControleur();
        }
        return self::$instance;
    }

    // Ajoute un nouveau commentaire pour un joueur donné
    public function ajouterCommentaire(
        string $contenu,
        string $joueurId
    ) : bool {

        $commentaireACreer = new Commentaire(
            0,
            $contenu,
            new DateTime()
        );

        return $this->commentaires->insertCommentaire($commentaireACreer, $joueurId);
    }

    // Récupère la liste des commentaires d’un joueur
    public function listerLesCommentairesDuJoueur(Joueur $joueur) : array {
        return $this->commentaires->selectCommentaireByJoueurId($joueur->getJoueurId());
    }

    // Supprime un commentaire à partir de son identifiant
    public function supprimerCommentaire(string $commentaireId) : bool {
        return $this->commentaires->deleteCommentaire($commentaireId);
    }
}