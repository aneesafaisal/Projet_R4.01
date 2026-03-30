<?php

// Déclaration du namespace
namespace R301\Modele\Joueur\Commentaire;

// Import des classes nécessaires
use DateTime;
use R301\Modele\Joueur\Joueur;

// Classe représentant un commentaire laissé par un utilisateur sur un joueur
class Commentaire implements \JsonSerializable {
    public int $commentaireId;
    public readonly string $contenu;
    public readonly DateTime $date;

    // Constructeur de la classe Commentaire
    public function __construct(int $commentaireId, string $contenu, DateTime $date)
    {
        $this->commentaireId = $commentaireId;
        $this->contenu = $contenu;
        $this->date = $date;
    }

    // Getters pour accéder aux propriétés de la classe
    public function getCommentaireId(): int
    {
        return $this->commentaireId;
    }
    
    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    // Méthode pour convertir l'objet en format JSON
    public function jsonSerialize(): array {
        return [
            'commentaireId' => $this->commentaireId,
            'contenu'       => $this->contenu,
            'date'          => $this->date->format('Y-m-d H:i:s')
        ];
    }
}



