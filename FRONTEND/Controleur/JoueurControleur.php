<?php

namespace R301\Controleur;

use DateTime;

class JoueurControleur {
    private static ?JoueurControleur $instance = null;
    private readonly ParticipationControleur $participationControleur;

    private function __construct() {
        $this->participationControleur = ParticipationControleur::getInstance();
    }

    public static function getInstance(): JoueurControleur {
        if (self::$instance == null) {
            self::$instance = new JoueurControleur();
        }
        return self::$instance;
    }

    public function ajouterJoueur(
        string $nom,
        string $prenom,
        string $numeroDeLicence,
        DateTime $dateDeNaissance,
        int $tailleEnCm,
        int $poidsEnKg,
        string $statut
    ) : bool {
        return false;
    }

    //Joueur
    public function getJoueurById(int $joueurId)  {  
        
    }

    // array
    public function listerLesJoueursSelectionnablesPourUnMatch(int $rencontreId)   { 
        
    }

    //array
    public function listerTousLesJoueurs()  { 
       
    }

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
        return false;
    }

    public function rechercherLesJoueurs(string $recherche, string $statut)   { #array
        
    }

    public function supprimerJoueur(int $joueurId) : bool {
        return false;
}
}