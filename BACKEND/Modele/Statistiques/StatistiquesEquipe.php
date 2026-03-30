<?php

// Déclaration du namespace
namespace R301\Modele\Statistiques;

// Importation des classes nécessaires
use R301\Modele\Rencontre\RencontreResultat;

// Classe représentant les statistiques d'une équipe, avec des méthodes pour calculer le nombre de victoires, de nuls, de défaites et les pourcentages correspondants
class StatistiquesEquipe implements \JsonSerializable {
    public readonly array $rencontres;

    // Constructeur de la classe StatistiquesEquipe, prenant en paramètre un tableau de rencontres
    public function __construct(
        array $rencontres
    ) {
        $this->rencontres = $rencontres;
    }

    // Méthode privée pour calculer le nombre de matchs joués en filtrant les rencontres qui ont été jouées
    private function nbMatchsJoues(): int {
        return count(array_filter($this->rencontres, function($rencontre) { return $rencontre->joue();}));
    }

    // Méthodes publiques pour calculer le nombre de victoires, de nuls et de défaites en filtrant les rencontres en fonction de leur résultat
    public function nbVictoires(): int {
        return count(array_filter($this->rencontres, function($rencontre) { return $rencontre->gagne(); }));
    }

    // Méthode pour calculer le nombre de nuls en filtrant les rencontres qui se sont terminées par un nul
    public function nbNuls(): int {
        return count(array_filter($this->rencontres, function($rencontre) { return $rencontre->nul(); }));
    }

    // Méthode pour calculer le nombre de défaites en filtrant les rencontres qui se sont terminées par une défaite
    public function nbDefaites(): int {
        return count(array_filter($this->rencontres, function($rencontre) { return $rencontre->perdu() ;}));
    }

    // Méthodes pour calculer les pourcentages de victoires, de nuls et de défaites en divisant le nombre de chaque résultat par le nombre total de matchs joués et en multipliant par 100
    public function pourcentageDeVictoires(): int {
        return $this->nbVictoires() / $this->nbMatchsJoues() * 100;
    }

    // Méthode pour calculer le pourcentage de nuls en divisant le nombre de nuls par le nombre total de matchs joués et en multipliant par 100
    public function pourcentageDeNuls(): int {
        return $this->nbNuls() / $this->nbMatchsJoues() * 100;
    }

    // Méthode pour calculer le pourcentage de défaites en divisant le nombre de défaites par le nombre total de matchs joués et en multipliant par 100
    public function pourcentageDeDefaites(): int {
        return $this->nbDefaites() / $this->nbMatchsJoues() * 100;
    }

    // Méthode pour sérialiser les statistiques de l'équipe en un tableau associatif, incluant le nombre de victoires, de nuls, de défaites et les pourcentages correspondants, utilisée pour la conversion en JSON
    public function jsonSerialize(): array{
        return [
            'nbVictoires'              => $this->nbVictoires(),
            'nbNuls'                   => $this->nbNuls(),
            'nbDefaites'               => $this->nbDefaites(),
            'pourcentageDeVictoires'   => $this->nbMatchsJoues() > 0 ? $this->pourcentageDeVictoires() : null,
            'pourcentageDeNuls'        => $this->nbMatchsJoues() > 0 ? $this->pourcentageDeNuls() : null,
            'pourcentageDeDefaites'    => $this->nbMatchsJoues() > 0 ? $this->pourcentageDeDefaites() : null,
        ];
    }
}


