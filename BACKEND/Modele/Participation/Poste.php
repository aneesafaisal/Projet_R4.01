<?php

// Déclaration du namespace
namespace R301\Modele\Participation;

// Enumération représentant les différents postes d'un joueur lors d'une rencontre (toplane, jungle, midlane, adc et support)
enum Poste
{
    case TOPLANE;
    case JUNGLE;
    case MIDLANE;
    case ADCARRY;
    case SUPPORT;

    // Méthode statique permettant de récupérer un poste à partir de son nom (string)
    public static function fromName(string $name): ?Poste
    {
        foreach (self::cases() as $poste) {
            if( $name === $poste->name ){
                return $poste;
            }
        }

        return null;
    }
}
