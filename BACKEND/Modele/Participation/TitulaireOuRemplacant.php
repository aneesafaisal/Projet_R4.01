<?php

// Déclaration du namespace
namespace R301\Modele\Participation;

// Enumération représentant le rôle d'un joueur lors d'une rencontre, soit titulaire soit remplaçant
enum TitulaireOuRemplacant
{
    case TITULAIRE;
    case REMPLACANT;

    // Méthode statique permettant de récupérer un rôle à partir de son nom (string)
    public static function fromName(string $name): ?TitulaireOuRemplacant
    {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status;
            }
        }

        return null;
    }
}
