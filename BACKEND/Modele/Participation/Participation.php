<?php

// Déclaration du namespace
namespace R301\Modele\Participation;

// Import des classes nécessaires
use R301\Modele\Joueur\Joueur;
use R301\Modele\Rencontre\Rencontre;

// Classe représentant la participation d'un joueur à une rencontre, avec des informations sur son rôle (titulaire ou remplaçant), sa performance et son poste
class Participation implements \JsonSerializable
{
    public int $participationId;
    public Joueur $participant;
    public readonly Rencontre $rencontre;
    public TitulaireOuRemplacant $titulaireOuRemplacant;
    public ?Performance $performance;
    public Poste $poste;

    // Constructeur de la classe Participation, prenant en paramètre les informations nécessaires pour créer une participation
    public function __construct(
        int $participationId,
        Joueur $participant,
        Rencontre $rencontre,
        TitulaireOuRemplacant $titulaireOuRemplacant,
        ?Performance $performance,
        Poste $poste
    ) {
        $this->participationId = $participationId;
        $this->participant = $participant;
        $this->rencontre = $rencontre;
        $this->titulaireOuRemplacant = $titulaireOuRemplacant;
        $this->performance = $performance;
        $this->poste = $poste;
    }

    // Getters et setters pour les propriétés de la classe Participation
    public function getParticipant(): Joueur
    {
        return $this->participant;
    }

    public function setParticipant(Joueur $participant): void
    {
        $this->participant = $participant;
    }

    public function getRencontre(): Rencontre
    {
        return $this->rencontre;
    }

    public function getParticipationId(): int
    {
        return $this->participationId;
    }

    public function getTitulaireOuRemplacant(): TitulaireOuRemplacant
    {
        return $this->titulaireOuRemplacant;
    }

    public function estTitulaire()
    {
        return $this->titulaireOuRemplacant === TitulaireOuRemplacant::TITULAIRE;
    }

    public function estRemplacant()
    {
        return $this->titulaireOuRemplacant === TitulaireOuRemplacant::REMPLACANT;
    }

    public function setTitulaireOuRemplacant(TitulaireOuRemplacant $titulaireOuRemplacant): void
    {
        $this->titulaireOuRemplacant = $titulaireOuRemplacant;
    }

    public function notePerformance(): int
    {
        return $this->performance !== null ? $this->performance->value : 0;
    }

    public function getPerformance(): ?Performance
    {
        return $this->performance;
    }

    public function setPerformance(?Performance $performance): void
    {
        $this->performance = $performance;
    }

    public function getPoste(): Poste
    {
        return $this->poste;
    }

    public function setPoste(Poste $poste): void
    {
        $this->poste = $poste;
    }

    // Méthode pour convertir l'objet Participation en un tableau associatif, utilisé pour la sérialisation JSON
    public function jsonSerialize(): array
    {
        return [
            'participationId' => $this->participationId,
            'participant' => $this->participant,
            'rencontre' => $this->rencontre,
            'titulaireOuRemplacant' => $this->titulaireOuRemplacant->name,
            'performance' => $this->performance?->value,
            'poste' => $this->poste->name
        ];
    }
}

