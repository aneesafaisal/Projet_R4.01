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
use R301\Modele\Utilisateur\UtilisateurDAO; 

// Contrôleur gérant l’authentification des utilisateurs
class UtilisateurControleur {
    private static ?UtilisateurControleur $instance = null;
    private readonly UtilisateurDAO $utilisateurs;

    // Constructeur privé pour empêcher l’instanciation directe
    private function __construct() {
        $this->utilisateurs = UtilisateurDAO::getInstance();
    }

    // Retourne l’instance unique du contrôleur
    public static function getInstance(): UtilisateurControleur {
        if (self::$instance == null) {
            self::$instance = new UtilisateurControleur();
        }
        return self::$instance;
    }

    // Permet à un utilisateur de se connecter en vérifiant ses identifiants
    public function seConnecter(string $username, string $password): bool {
        $utilisateurEssayantDeSeConnecter = $this->utilisateurs->getUtilisateur($username);

        // Si l'utilisateur n'existe pas, la connexion échoue
        if ($utilisateurEssayantDeSeConnecter === null) {
            return false; 
        }   

        // Vérifie que le mot de passe fourni correspond au mot de passe stocké (hashé)
        if (password_verify($password, $utilisateurEssayantDeSeConnecter->getMotDePasse())) {
            // Si les identifiants sont corrects, démarre une session pour l'utilisateur
            session_set_cookie_params(1800);
            // Définit la durée de vie de la session à 30 minutes (1800 secondes)
            ini_set('session.gc_maxlifetime', 1800);
            // Démarre la session
            session_start();
            $_SESSION['username'] = $username;
            return true;
        } else {
            return false;
        }
    }
}
