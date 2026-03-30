<?php

// Déclaration du namespace
namespace R301\Modele\Participation;

// Import des classes nécessaires
use R301\Modele\Joueur\Joueur;
use R301\Modele\Joueur\JoueurStatut;
use R301\Modele\Rencontre\Rencontre;

// Classe représentant la feuille de match d'une rencontre, contenant les participants et leurs performances
class FeuilleDeMatch {
    public readonly array $participants;

    // Constructeur de la classe FeuilleDeMatch, prenant en paramètre un tableau de participants
    public function __construct(array $participants) {
        $this->participants = $participants;
    }

    // Getter pour accéder à la liste des participants de la feuille de match
    public function getParticipants(): array {
        return $this->participants;
    }

    // Méthode pour récupérer un participant occupant un poste spécifique avec un statut de titulaire ou de remplaçant
    public function getParticipantAuPoste(Poste $poste, TitulaireOuRemplacant $titulaireOuRemplacant): ?Participation {
        foreach ($this->participants as $participant) {
            if ($participant->getPoste() === $poste
                && $participant->getTitulaireOuRemplacant() === $titulaireOuRemplacant
            ) {
                return $participant;
            }
        }
        return null;
    }

    // Méthode pour récupérer un participant occupant un poste spécifique avec un statut de remplaçant
    public function getRemplacantAuPoste(Poste $poste): ?Participation {
        foreach ($this->participants as $participant) {
            if ($participant->getPoste() === $poste
                && $participant->getTitulaireOuRemplacant() === TitulaireOuRemplacant::REMPLACANT
            ) {
                return $participant;
            }
        }
        return null;
    }

    // Méthode pour vérifier si la feuille de match est complète, c'est-à-dire si tous les postes ont un titulaire et si tous les participants sont actifs
    public function estComplete(): bool {
        return $this->tousLesPostesOntUnTitulaire() && $this->tousLesParticipantsSontActifs();
    }

    // Méthode privée pour vérifier si tous les postes ont un titulaire
    private function tousLesPostesOntUnTitulaire(): bool {
        foreach (Poste::cases() as $poste) {
            if($this->getParticipantAuPoste($poste, TitulaireOuRemplacant::TITULAIRE) === null) {
                return false;
            }
        }
        return true;
    }

    // Méthode privée pour vérifier si tous les participants de la feuille de match sont actifs
    private function tousLesParticipantsSontActifs(): bool {
        foreach ($this->getParticipants() as $participant) {
            if($participant->getParticipant()->getStatut() !== JoueurStatut::ACTIF) {
                return false;
            }
        }
        return true;
    }

    // Méthode pour vérifier si tous les participants de la feuille de match ont été évalués, c'est-à-dire si leur performance n'est pas nulle
    public function estEvalue() {
        foreach ($this->getParticipants() as $participant) {
            if($participant->getPerformance() === null) {
                return false;
            }
        }
        return true;
    }

    // Méthode pour convertir la feuille de match en un tableau associatif, facilitant la sérialisation en JSON pour les réponses d'API
    public function toArray(): array {
    $result = [];
    foreach ($this->participants as $participant) {
        $joueur = $participant->getParticipant();
        $rencontre = $participant->getRencontre();
        $result[] = [
            'participationId'        => $participant->getParticipationId(),
            'poste'                  => $participant->getPoste()->name,
            'titulaire_ou_remplacant'=> $participant->getTitulaireOuRemplacant()->name,
            'performance'            => $participant->getPerformance() !== null ? $participant->getPerformance()->name : null,
            'joueur'                 => [
                'joueurId' => $joueur->getJoueurId(),
                'nom'      => $joueur->getNom(),
                'prenom'   => $joueur->getPrenom(),
            ],
            'rencontre'              => [
                'rencontreId' => $rencontre->getRencontreId(),
            ]
        ];
    }
    return $result;
}
}

