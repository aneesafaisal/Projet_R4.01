<?php

// Déclaration du namespace
namespace R301\Controleur;

// Import des classes nécessaires
use DateTime;
use R301\Modele\Rencontre\Rencontre;
use R301\Modele\Rencontre\RencontreDAO;
use R301\Modele\Rencontre\RencontreLieu;
use R301\Modele\Rencontre\RencontreResultat;

// Contrôleur gérant les opérations liées aux rencontres (matchs)
class RencontreControleur {
    private static ?RencontreControleur $instance = null;
    private readonly RencontreDAO $rencontres;

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
        $this->rencontres = RencontreDAO::getInstance();
    }

    // Retourne l'instance unique du contrôleur
    public static function getInstance(): RencontreControleur {
        if (self::$instance == null) {
            self::$instance = new RencontreControleur();
        }
        return self::$instance;
    }

    // Ajoute une nouvelle rencontre
    public function ajouterRencontre(
        DateTime $dateHeure,
        string $equipeAdverse,
        string $adresse,
        RencontreLieu $lieu
    ) : bool {

        if ($dateHeure < date("Y-m-d H:i:s")) {
            return false;
        } else {
            $rencontreAAjouter = new Rencontre(
                $dateHeure,
                $equipeAdverse,
                $adresse,
                $lieu
            );

            // Insertion en base
            return $this->rencontres->insertRencontre($rencontreAAjouter);
        }
    }

    // Enregistre le résultat d’une rencontre
    public function enregistrerResultat(
        int $rencontreId,
        string $resultat
    ) : bool {
        $rencontreAModifier = $this->rencontres->selectRencontreById($rencontreId);

        if (!$rencontreAModifier->estPassee()) {
            return false;
        } else {
            $rencontreAModifier->setResultat(RencontreResultat::fromName($resultat));
            return $this->rencontres->enregistrerResultat($rencontreAModifier);
        }
    }

    // Récupère une rencontre par son identifiant
    public function getRenconterById(int $rencontreId) : Rencontre {
        return $this->rencontres->selectRencontreById($rencontreId);
    }

    // Liste toutes les rencontres
    public function listerToutesLesRencontres() : array {
        return $this->rencontres->selectAllRencontres();
    }

    // Modifie une rencontre existante
    public function modifierRencontre(
        int $rencontreId,
        DateTime $dateHeure,
        string $equipeAdverse,
        string $adresse,
        RencontreLieu $lieu
    ) : bool {

        $rencontreAModifier = $this->rencontres->selectRencontreById($rencontreId);

        if (
            $rencontreAModifier->estPassee()
            || $dateHeure < new DateTime()
        ) {
            return false;
        } else {
            // Mise à jour des informations
            $rencontreAModifier->setDateEtHeure($dateHeure);
            $rencontreAModifier->setEquipeAdverse($equipeAdverse);
            $rencontreAModifier->setAdresse($adresse);
            $rencontreAModifier->setLieu($lieu);

            return $this->rencontres->updateRencontre($rencontreAModifier);
        }
    }

    // Supprime une rencontre (uniquement si aucun résultat n’est enregistré)
    public function supprimerRencontre(int $rencontreId) : bool {
        $rencontreASupprimer = $this->rencontres->selectRencontreById($rencontreId);

        if($rencontreASupprimer->getResultat() != null) {
            return false;
        } else {
            return $this->rencontres->supprimerRencontre($rencontreId);
        }
    }
}