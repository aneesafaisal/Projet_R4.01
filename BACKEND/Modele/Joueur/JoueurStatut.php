<?php

// Déclaration du namespace
namespace R301\Modele\Joueur;

// Enumération représentant le statut d'un joueur
enum JoueurStatut
{
    case ACTIF;
    case BLESSE;
    case ABSENT;
    case SUSPENDU;

    // Méthode statique permettant de récupérer un statut à partir de son nom (string)
    public static function fromName(string $name): ?JoueurStatut
    {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status;
            }
        }
        return null;
    }
}
