<?php

// Déclaration du namespace
namespace R301\Modele\Joueur;

// Import des classes nécessaires
use DateTime;

// Classe représentant un joueur de l'équipe
class Joueur implements \JsonSerializable{

    // Propriétés de la classe Joueur
    public int $joueurId;
    public string $nom;
    public string $prenom;
    public string $numeroDeLicence;
    public DateTime $dateDeNaissance;
    public int $tailleEnCm;
    public int $poidsEnKg;
    public ?JoueurStatut $statut;

    // Constructeur de la classe Joueur
    public function __construct(
        int $joueurId,
        string $nom,
        string $prenom,
        string $numeroDeLicence,
        DateTime $dateDeNaissance,
        int $tailleEnCm,
        int $poidsEnKg,
        ?JoueurStatut $statut
    ) {
        $this->joueurId = $joueurId;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->numeroDeLicence = $numeroDeLicence;
        $this->dateDeNaissance = $dateDeNaissance;
        $this->tailleEnCm = $tailleEnCm;
        $this->poidsEnKg = $poidsEnKg;
        $this->statut = $statut;
    }

    // Méthode pour vérifier si le nom ou le prénom du joueur contient une chaîne de recherche donnée
    public function nomOuPrenomContient(string $recherche) : bool {
        return str_contains(strtolower($this->nom), strtolower($recherche))
            || str_contains(strtolower($this->prenom), strtolower($recherche));
    }

    // Méthode pour obtenir une représentation textuelle du joueur, incluant son numéro de licence, son nom et son prénom, ainsi que son statut s'il n'est pas actif
    public function toString() : string {
        $selectableString = "";
        $selectableString .= $this->getNumeroDeLicence() . ' : ' . $this->nom . ' ' . $this->prenom;

        if ($this->statut !== JoueurStatut::ACTIF) {
            $selectableString .= ' (' . $this->statut->name . ')';
        }
        return $selectableString;
    }

    // Getters et setters pour les propriétés de la classe Joueur
    public function getJoueurId(): int
    {
        return $this->joueurId;
    }

    public function setJoueurId(int $joueurId): void
    {
        $this->joueurId = $joueurId;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function getNumeroDeLicence() {
        return $this->numeroDeLicence;
    }

    public function getDateDeNaissance() : DateTime {
        return $this->dateDeNaissance;
    }

    public function getTailleEnCm() {
        return $this->tailleEnCm;
    }

    public function getPoidsEnKg() {
        return $this->poidsEnKg;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function setNumeroDeLicence(string $numeroDeLicence): void
    {
        $this->numeroDeLicence = $numeroDeLicence;
    }

    public function setDateDeNaissance(DateTime $dateDeNaissance): void
    {
        $this->dateDeNaissance = $dateDeNaissance;
    }

    public function setTailleEnCm(int $tailleEnCm): void
    {
        $this->tailleEnCm = $tailleEnCm;
    }

    public function setPoidsEnKg(int $poidsEnKg): void
    {
        $this->poidsEnKg = $poidsEnKg;
    }

    public function setStatut(?JoueurStatut $statut): void
    {
        $this->statut = $statut;
    }

    // Méthode pour convertir l'objet Joueur en format JSON
    public function jsonSerialize(): array
    {
        return [
            'joueurId'          => $this->getJoueurId(),
            'nom'               => $this->getNom(),
            'prenom'            => $this->getPrenom(),
            'numeroDeLicence'   => $this->getNumeroDeLicence(),
            'dateDeNaissance'   => $this->getDateDeNaissance()->format('Y-m-d'), 
            'tailleEnCm'        => $this->getTailleEnCm(),
            'poidsEnKg'         => $this->getPoidsEnKg(),
            'statut'            => $this->getStatut()->name,   
        ];
    }
}

