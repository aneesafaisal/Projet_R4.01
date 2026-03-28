<?php

// Déclaration du namespace
namespace R301\Modele\Rencontre;

// Enumération représentant le lieu d'une rencontre, soit à domicile soit à l'extérieur
enum RencontreLieu {
    case DOMICILE;
    case EXTERIEUR;

    // Méthode pour récupérer le nom du lieu
    public static function fromName(string $name): ?RencontreLieu
    {
        foreach (self::cases() as $lieu) {
            if( $name === $lieu->name ){
                return $lieu;
            }
        }
        return null;
    }
}
