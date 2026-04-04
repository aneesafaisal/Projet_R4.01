<?php

// Déclaration du namespace
namespace R301\Modele\Rencontre;

// Importation des classes nécessaires
use DateTime;

// Classe représentant une rencontre, avec des informations sur la date, l'équipe adverse, l'adresse, le lieu et le résultat
class Rencontre implements \JsonSerializable
{
    public int $rencontreId;
    public DateTime $dateEtHeure;
    public string $equipeAdverse;
    public string $adresse;
    public ?RencontreLieu $lieu;
    public ?RencontreResultat $resultat;

    // Constructeur de la classe Rencontre, prenant en paramètre les informations nécessaires pour créer une rencontre
    public function __construct(
        DateTime $dateEtheure,
        string $equipeAdverse,
        string $adresse,
        ?RencontreLieu $lieu,
        ?RencontreResultat $resultat,
        int $rencontreId = 0
    ) {
        $this->rencontreId = $rencontreId;
        $this->dateEtHeure = $dateEtheure;
        $this->equipeAdverse = $equipeAdverse;
        $this->adresse = $adresse;
        $this->lieu = $lieu;
        $this->resultat = $resultat;
    }

    // Getters et setters pour les propriétés de la classe Rencontre
    public function getRencontreId(): int
    {
        return $this->rencontreId;
    }

    public function getDateEtHeure(): DateTime
    {
        return $this->dateEtHeure;
    }

    public function setDateEtHeure(DateTime $dateEtHeure): void
    {
        $this->dateEtHeure = $dateEtHeure;
    }

    public function getEquipeAdverse(): string
    {
        return $this->equipeAdverse;
    }

    public function setEquipeAdverse(string $equipeAdverse): void
    {
        $this->equipeAdverse = $equipeAdverse;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): void
    {
        $this->adresse = $adresse;
    }

    public function getLieu(): ?RencontreLieu
    {
        return $this->lieu;
    }

    public function setLieu(?RencontreLieu $lieu): void
    {
        $this->lieu = $lieu;
    }

    public function joue(): bool
    {
        return $this->resultat !== null;
    }

    public function gagne(): bool
    {
        return $this->resultat === RencontreResultat::VICTOIRE;
    }

    public function nul(): bool
    {
        return $this->resultat === RencontreResultat::NUL;
    }

    public function perdu(): bool
    {
        return $this->resultat === RencontreResultat::DEFAITE;
    }

    public function getResultat(): ?RencontreResultat
    {
        return $this->resultat;
    }

    public function setResultat(?RencontreResultat $resultat): void
    {
        $this->resultat = $resultat;
    }

    public function estPassee(): bool
    {
        return $this->dateEtHeure < new DateTime();
    }

    // Méthode pour convertir l'objet Rencontre en un format JSON, utilisée pour la sérialisation
    public function jsonSerialize(): array
    {
        return [
            'rencontreId' => $this->getRencontreId(),
            'dateEtHeure' => $this->getDateEtHeure()->format('Y-m-d H:i:s'),
            'equipeAdverse' => $this->getEquipeAdverse(),
            'adresse' => $this->getAdresse(),
            'lieu' => $this->getLieu()?->name,
            'resultat' => $this->getResultat()?->name
        ];
    }
}
