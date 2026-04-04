<?php

// Déclaration du namespace
namespace R301\Modele\Rencontre;

// Importation des classes nécessaires
use DateTime;
use PDO;
use R301\Modele\DatabaseHandler;

// Classe de Data Access Object (DAO) pour la gestion des rencontres, permettant d'effectuer des opérations de création, lecture, mise à jour et suppression (CRUD) sur les rencontres dans la base de données
class RencontreDAO
{
    private static ?RencontreDAO $instance = null;
    private readonly DatabaseHandler $database;

    // Constructeur privé pour empêcher l'instanciation directe de la classe RencontreDAO, initialisant la connexion à la base de données
    private function __construct()
    {
        $this->database = DatabaseHandler::getInstance();
    }

    // Méthode pour obtenir l'instance unique de RencontreDAO, implémentant le pattern Singleton
    public static function getInstance(): RencontreDAO
    {
        if (self::$instance == null) {
            self::$instance = new RencontreDAO();
        }
        return self::$instance;
    }

    // Méthode privée pour mapper une ligne de la base de données à un objet Rencontre
    private function mapToRencontre(array $dbLine): Rencontre
    {
        return new Rencontre(
            new DateTime($dbLine['date_heure']),
            $dbLine['equipe_adverse'],
            $dbLine['adresse'],
            $dbLine['lieu'] ? RencontreLieu::fromName($dbLine['lieu']) : null,
            $dbLine['resultat'] ? RencontreResultat::fromName($dbLine['resultat']) : null,
            $dbLine['rencontre_id']
        );
    }

    // Méthode pour récupérer la liste de toutes les rencontres présentes dans la base de données
    public function selectAllRencontres(): array
    {
        $query = 'SELECT * FROM rencontre';
        $statement = $this->database->pdo()->prepare($query);
        if ($statement->execute()) {
            return array_map(
                function ($rencontre) {
                    return $this->mapToRencontre($rencontre); },
                $statement->fetchAll(PDO::FETCH_ASSOC)
            );
        } else {
            exit();
        }
    }

    // Méthode pour récupérer une rencontre spécifique à partir de son identifiant
    public function selectRencontreById(string $rencontreId): Rencontre
    {
        $query = 'SELECT * FROM rencontre WHERE rencontre_id = :rencontreId';
        $statement = $this->database->pdo()->prepare($query);
        $statement->bindValue(':rencontreId', $rencontreId);
        if ($statement->execute()) {
            return $this->mapToRencontre($statement->fetch(PDO::FETCH_ASSOC));
        } else {
            exit();
        }
    }

    // Méthode pour insérer une nouvelle rencontre dans la base de données, prenant en paramètre un objet Rencontre à créer
    public function insertRencontre(Rencontre $rencontreACreer): bool
    {
        $query = '
            INSERT INTO rencontre(adresse, date_heure, equipe_adverse, lieu, resultat)
            VALUES (:adresse, :date_heure, :equipe_adverse, :lieu, :resultat)
        ';
        $statement = $this->database->pdo()->prepare($query);
        $statement->bindValue(':adresse', $rencontreACreer->getAdresse());
        $statement->bindValue(':date_heure', $rencontreACreer->getDateEtHeure()->format('Y-m-d H:i:s'));
        $statement->bindValue(':equipe_adverse', $rencontreACreer->getEquipeAdverse());
        $statement->bindValue(':lieu', $rencontreACreer->getLieu()?->name);
        $statement->bindValue(':resultat', $rencontreACreer->getResultat()?->name);

        return $statement->execute();
    }

    // Méthode pour mettre à jour les informations d'une rencontre existante dans la base de données, prenant en paramètre un objet Rencontre à modifier
    public function updateRencontre(Rencontre $rencontreAModifier): bool
    {
        $query = 'UPDATE rencontre 
                  SET 
                      adresse = :adresse,
                      date_heure = :date_heure,
                      equipe_adverse = :equipe_adverse,
                      lieu = :lieu,
                      resultat = :resultat
                  WHERE rencontre_id = :rencontre_id';
        $statement = $this->database->pdo()->prepare($query);
        $statement->bindValue(':rencontre_id', $rencontreAModifier->getRencontreId());
        $statement->bindValue(':date_heure', $rencontreAModifier->getDateEtHeure()->format('Y-m-d H:i:s'));
        $statement->bindValue(':equipe_adverse', $rencontreAModifier->getEquipeAdverse());
        $statement->bindValue(':adresse', $rencontreAModifier->getAdresse());
        $statement->bindValue(':lieu', $rencontreAModifier->getLieu()?->name);
        $statement->bindValue(':resultat', $rencontreAModifier->getResultat());
        return $statement->execute();
    }

    // Méthode pour enregistrer le résultat d'une rencontre existante dans la base de données, prenant en paramètre un objet Rencontre à modifier
    public function enregistrerResultat(Rencontre $rencontreAModifier): bool
    {
        $query = 'UPDATE rencontre 
                  SET 
                      resultat = :resultat
                  WHERE rencontre_id = :rencontre_id';
        $statement = $this->database->pdo()->prepare($query);
        $statement->bindValue(':rencontre_id', $rencontreAModifier->getRencontreId());
        $statement->bindValue(':resultat', $rencontreAModifier->getResultat()->name);
        return $statement->execute();
    }

    // Méthode pour supprimer une rencontre de la base de données à partir de son identifiant
    public function supprimerRencontre(int $rencontreId): bool
    {
        $query = 'DELETE FROM rencontre WHERE rencontre_id = :rencontreId';
        $statement = $this->database->pdo()->prepare($query);
        $statement->bindValue(':rencontreId', $rencontreId);
        return $statement->execute();
    }

}