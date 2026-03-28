<?php

// Déclaration du namespace
namespace rencontre;

// Enumération représentant le lieu d'une rencontre, soit à domicile soit à l'extérieur
enum Lieu {
    case DOMICILE;
    case EXTERIEUR;

    // Méthode permettant de récupérer le nom du lieu
    public function getName(): string {
        return $this->name;
    }

    // Méthode statique permettant de récupérer un lieu à partir d'une chaîne de caractères
    public static function fromString(string $name): Lieu {
        return match (strtoupper($name)) {
            "DOMICILE" => Lieu::DOMICILE,
            "EXTERIEUR" => Lieu::EXTERIEUR,
        };
    }
}
