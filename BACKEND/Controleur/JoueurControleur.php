<?php

// Déclaration du namespace
namespace R301\Controleur;

// Import des classes nécessaires
use DateTime;
use R301\Modele\Joueur\Joueur;
use R301\Modele\Joueur\JoueurDAO;
use R301\Modele\Joueur\JoueurStatut;

// Contrôleur gérant les opérations liées aux joueurs
class JoueurControleur {
    private static ?JoueurControleur $instance = null;
    private readonly JoueurDAO $joueurs;
    private readonly ParticipationControleur $participationControleur;

    // Constructeur privé pour empêcher l’instanciation directe
    private function __construct() {
        $this->joueurs = JoueurDAO::getInstance();
        $this->participationControleur = ParticipationControleur::getInstance();
    }

    // Retourne l’instance unique du contrôleur
    public static function getInstance(): JoueurControleur {
        if (self::$instance == null) {
            self::$instance = new JoueurControleur();
        }
        return self::$instance;
    }

    // Ajoute un nouveau joueur
    public function ajouterJoueur(
        string $nom,
        string $prenom,
        string $numeroDeLicence,
        DateTime $dateDeNaissance,
        int $tailleEnCm,
        int $poidsEnKg,
        string $statut
    ) : bool {
        $joueurACreer = new Joueur(
            0,
            $nom,
            $prenom,
            $numeroDeLicence,
            $dateDeNaissance,
            $tailleEnCm,
            $poidsEnKg,
            JoueurStatut::fromName($statut)
        );

        return $this->joueurs->insertJoueur($joueurACreer);
    }

    // Récupère un joueur par son identifiant
    public function getJoueurById(int $joueurId) : ?Joueur {
        return $this->joueurs->selectJoueurById($joueurId);
    }

    // Liste les joueurs actifs pouvant être sélectionnés pour un match
    public function listerLesJoueursSelectionnablesPourUnMatch(int $rencontreId) : array {
        $joueursActifs = $this->joueurs->selectJoueursByStatut(JoueurStatut::ACTIF);
        $joueursSelectionnables = [];

        // Filtre les joueurs déjà présents sur la feuille de match
        foreach ($joueursActifs as $joueur) {
            if (!$this->participationControleur->lejoueurEstDejaSurLaFeuilleDeMatch($rencontreId, $joueur->getJoueurId())) {
                $joueursSelectionnables[] = $joueur;
            }
        }

        return $joueursSelectionnables;
    }

    // Récupère tous les joueurs
    public function listerTousLesJoueurs() : array {
        return $this->joueurs->selectAllJoueurs();
    }

    // Modifie les informations d’un joueur
    public function modifierJoueur(
        int $joueurId,
        string $nom,
        string $prenom,
        string $numeroDeLicence,
        DateTime $dateDeNaissance,
        int $tailleEnCm,
        int $poidsEnKg,
        string $statut
    ) : bool {
        $joueurAModifier = $this->joueurs->selectJoueurById($joueurId);

        $joueurAModifier->setNom($nom);
        $joueurAModifier->setPrenom($prenom);
        $joueurAModifier->setNumeroDeLicence($numeroDeLicence);
        $joueurAModifier->setDateDeNaissance($dateDeNaissance);
        $joueurAModifier->setTailleEnCm($tailleEnCm);
        $joueurAModifier->setPoidsEnKg($poidsEnKg);
        $joueurAModifier->setStatut(JoueurStatut::fromName($statut));

        return $this->joueurs->updateJoueur($joueurAModifier);
    }

    // Recherche des joueurs selon un string et/ou un statut
    public function rechercherLesJoueurs(string $recherche, string $statut) : array {
        $tousLesjoueurs = $this->joueurs->selectAllJoueurs();
        $joueursTrouves = [];

        foreach ($tousLesjoueurs as $joueur) {
            $conserverDansLaListe = true;

            // Filtre par nom ou prénom
            if ($recherche !== "") {
                $conserverDansLaListe = $joueur->nomOuPrenomContient($recherche);
            }

            // Filtre par statut
            if ($conserverDansLaListe && $statut !== "") {
                $conserverDansLaListe = $joueur->getStatut() == JoueurStatut::fromName($statut);
            }

            // Ajout à la liste si le joueur correspond aux critères
            if ($conserverDansLaListe) {
                $joueursTrouves[] = $joueur;
            }
        }

        return $joueursTrouves;
    }

    // Supprime un joueur (s'il n'est pas déjà associé à un match)
    public function supprimerJoueur(int $joueurId) : bool {
        if ($this->participationControleur->lejoueurEstDejaSurLaFeuilleDeMatch($joueurId, $joueurId)) {
            return false;
        }
        return $this->joueurs->supprimerJoueur($joueurId);
}
}