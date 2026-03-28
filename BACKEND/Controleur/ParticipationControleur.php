<?php

// Déclaration du namespace
namespace R301\Controleur;

// Import des classes nécessaires liées aux participations
use R301\Modele\Participation\FeuilleDeMatch;
use R301\Modele\Participation\Participation;
use R301\Modele\Participation\ParticipationDAO;
use R301\Modele\Participation\Performance;
use R301\Modele\Participation\Poste;
use R301\Modele\Participation\TitulaireOuRemplacant;

// Contrôleur gérant les participations des joueurs aux matchs
class ParticipationControleur {
    private static ?ParticipationControleur $instance = null;
    private readonly ParticipationDAO $participations;
    private readonly RencontreControleur $rencontres;

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
        $this->participations = ParticipationDAO::getInstance();
        $this->rencontres = RencontreControleur::getInstance();
    }

    // Retourne l’instance unique
    public static function getInstance(): ParticipationControleur {
        if (self::$instance == null) {
            self::$instance = new ParticipationControleur();
        }
        return self::$instance;
    }
    
    // Vérifie si un joueur est déjà présent sur la feuille de match
    public function lejoueurEstDejaSurLaFeuilleDeMatch(int $rencontreId, int $joueurId) : bool {
        return $this->participations->lejoueurEstDejaSurLaFeuilleDeMatch($rencontreId, $joueurId);
    }

    // Liste toutes les participations
    public function listerToutesLesParticipations() : array {
        return $this->participations->selectAllParticipations();
    }

    // Récupère la feuille de match d’une rencontre
    public function getFeuilleDeMatch(int $rencontreId) : FeuilleDeMatch {
        return new FeuilleDeMatch($this->participations->selectParticipationsByRencontreId($rencontreId));
    }

    // Assigne un joueur à une rencontre avec un poste et un statut
    public function assignerUnParticipant(
        int $joueurId,
        int $rencontreId,
        Poste $poste,
        TitulaireOuRemplacant $titulaireOuRemplacant
    ) : bool {

        // Vérifie si le poste est déjà occupé ou si le joueur est déjà sélectionné
        if ($this->participations->lePosteEstDejaOccupe($rencontreId, $poste, $titulaireOuRemplacant)
            || $this->lejoueurEstDejaSurLaFeuilleDeMatch($rencontreId, $joueurId)
        ) {
            return false;
        } else {
            // Récupération du joueur et de la rencontre
            $joueur = JoueurControleur::getInstance()->getJoueurById($joueurId);
            $rencontre = $this->rencontres->getRenconterById($rencontreId);

            $participationACreer = new Participation(
                0,
                $joueur,
                $rencontre,
                $titulaireOuRemplacant,
                null,
                $poste
            );

            return $this->participations->insertParticipation($participationACreer);
        }
    }

    // Modifie une participation existante
    public function modifierParticipation(
        int $participationId,
        Poste $poste,
        TitulaireOuRemplacant $titulaireOuRemplacant,
        int $joueurId
    ) : bool {
        $participationAModifier = $this->participations->selectParticipationById($participationId);

        if ($participationAModifier->getParticipant()->getJoueurId() != $joueurId) {
            $participationAModifier->setParticipant(JoueurControleur::getInstance()->getJoueurById($joueurId));
        }

        $participationAModifier->setPoste($poste);
        $participationAModifier->setTitulaireOuRemplacant($titulaireOuRemplacant);

        return $this->participations->updateParticipation($participationAModifier);
    }

    // Supprime une participation
    public function supprimerLaParticipation(int $participationId) : bool {
        return $this->participations->deleteParticipation($participationId);
    }

    // Met à jour la performance d’un joueur (uniquement si le match est passé)
    public function mettreAJourLaPerformance(
        int $participationId,
        string $performance
    ) : bool {
        $participationAEvaluer = $this->participations->selectParticipationById($participationId);

        if (!$participationAEvaluer->getRencontre()->estPassee()) {
            return false;
        }

        $participationAEvaluer->setPerformance(Performance::fromName($performance));
        return $this->participations->updatePerformance($participationAEvaluer);
    }

    // Supprime la performance d’un joueur (si le match est passé)
    public function supprimerLaPerformance(int $participationId) : bool {
        $participationAEvaluer = $this->participations->selectParticipationById($participationId);

        if (!$participationAEvaluer->getRencontre()->estPassee()) {
            return false;
        }

        $participationAEvaluer->setPerformance(null);
        return $this->participations->updatePerformance($participationAEvaluer);
    }
}