<?php

// Déclaration du namespace
namespace R301\Modele\Rencontre;

// Enumération représentant le résultat d'une rencontre, soit victoire, défaite ou nul
enum RencontreResultat
{
    case VICTOIRE;
    case DEFAITE;
    case NUL;

    // Méthode statique permettant de récupérer un résultat à partir de son nom (string)
    public static function fromName(string $name): ?RencontreResultat
    {
        foreach (self::cases() as $resultat) {
            if( $name === $resultat->name ){
                return $resultat;
            }
        }

        return null;
    }
    
}
