<?php d

namespace R301\Controleur;

use DateTime;
use R301\Modele\Joueur\Commentaire\Commentaire;
use R301\Modele\Joueur\Commentaire\CommentaireDAO;
use R301\Modele\Joueur\Joueur;
use R301\Modele\Joueur\JoueurDAO;
use R301\Modele\Joueur\JoueurStatut;
use R301\Modele\Statistiques\StatistiquesEquipe;
use R301\Modele\Statistiques\StatistiquesJoueurs;
use R301\Modele\Utilisateur\UtilisateurDAO;

class UtilisateurControleur {
    private static ?UtilisateurControleur $instance = null;
    private readonly UtilisateurDAO $utilisateurs;

    private function __construct() {
        $this->utilisateurs = UtilisateurDAO::getInstance();
    }

    public static function getInstance(): UtilisateurControleur {
        if (self::$instance == null) {
            self::$instance = new UtilisateurControleur();
        }
        return self::$instance;
    }

    public function seConnecter(string $username, string $password): ?string {
    $utilisateur = $this->utilisateurs->getUtilisateur($username);
    if ($utilisateur && $utilisateur->getMotDePasse() === hash('sha256', $password)) {
        return generateJWT(
            ['username' => $username, 'exp' => time() + 3600],
            "votre_secret_jwt"
        );
    }
    return null;
}
}