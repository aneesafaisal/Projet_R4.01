<?php

// Déclaration du namespace
namespace R301\Modele\Participation;

// Enumération représentant la performance d'un joueur lors d'une rencontre, avec des valeurs allant de 1 (catastrophique) à 5 (excellente)
enum Performance: int
{
    case EXCELLENTE = 5;
    case BONNE = 4;
    case MOYENNE = 3;
    case MAUVAISE = 2;
    case CATASTROPHIQUE = 1;

    // Méthode statique permettant de récupérer une performance à partir de son nom (string)
    public static function fromName(string $name): ?Performance
    {
        foreach (self::cases() as $performance) {
            if( $name === $performance->name ){
                return $performance;
            }
        }

        return null;
    }

    // Méthode statique permettant de récupérer une performance à partir de sa valeur (int)
    public static function fromValue(int $value): ?Performance
    {
        foreach (self::cases() as $performance) {
            if( $value === $performance->value ){
                return $performance;
            }
        }

        return null;
    }
}
