<?php

// Déclaration du namespace
namespace rencontre;

// Classe représentant le résultat d'une rencontre, avec les scores de l'équipe et des adversaires
class Resultat
{
    private int $scoreDeLequipe;
    private int $scoreDesAdversaires;

    // Constructeur de la classe Resultat, prenant en paramètre les scores de l'équipe et des adversaires
    public function __construct(int $scoreDeLequipe, int $scoreDesAdversaires)
    {
        $this->scoreDeLequipe = $scoreDeLequipe;
        $this->scoreDesAdversaires = $scoreDesAdversaires;
    }

    // Méthode statique pour construire un objet Resultat à partir des scores de l'équipe et des adversaires, utilisée pour la construction à partir de la base de données
    public static function constructFromDB(
        int $scoreDeLequipe,
        int $scoreDesAdversaires
    ): Resultat {
        return new Resultat($scoreDeLequipe, $scoreDesAdversaires);
    }

    // Méthode pour déterminer le sens du résultat (victoire, défaite ou nul) en fonction des scores de l'équipe et des adversaires
    public function getSensDuResultat(): SensDuResultat
    {
        return SensDuResultat::fromResultat($this);
    }

    // Getters et setters pour les propriétés de la classe Resultat
    public function getScoreDeLequipe(): int
    {
        return $this->scoreDeLequipe;
    }

    public function setScoreDeLequipe(int $scoreDeLequipe): void
    {
        $this->scoreDeLequipe = $scoreDeLequipe;
    }

    public function getScoreDesAdversaires(): int
    {
        return $this->scoreDesAdversaires;
    }

    public function setScoreDesAdversaires(int $scoreDesAdversaires): void
    {
        $this->scoreDesAdversaires = $scoreDesAdversaires;
    }
}
