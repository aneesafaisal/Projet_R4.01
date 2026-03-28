<?php

// Déclaration du namespace
namespace R301\Modele\Participation;

// Import des classes nécessaires
use DateTime;
use PDO;
use R301\Modele\DatabaseHandler;
use R301\Modele\Joueur\JoueurDAO;
use R301\Modele\Rencontre\Rencontre;
use R301\Modele\Rencontre\RencontreDAO;
use R301\Modele\Rencontre\RencontreLieu;
use R301\Modele\Rencontre\RencontreResultat;

// Classe de Data Access Object (DAO) pour gérer les opérations de base de données liées à la classe Participation
class ParticipationDAO {
    private static ?ParticipationDAO $instance = null;
    private readonly DatabaseHandler $database;
    private readonly JoueurDAO $joueurs;
    private readonly RencontreDAO $rencontres;

    // Constructeur privé pour empêcher l'instanciation directe de la classe ParticipationDAO
    private function __construct() {
        $this->database = DatabaseHandler::getInstance();
        $this->joueurs = JoueurDAO::getInstance();
        $this->rencontres = RencontreDAO::getInstance();
    }

    // Méthode pour obtenir l'instance unique de ParticipationDAO
    public static function getInstance(): ParticipationDAO {
        if (self::$instance == null) {
            self::$instance = new ParticipationDAO();
        }
        return self::$instance;
    }

    // Méthode privée pour mapper une ligne de la base de données à un objet Participation
    private function mapToParticipation(array $dbLine): Participation {
        return new Participation(
            $dbLine['participation_id'],
            $this->joueurs->selectJoueurById($dbLine['joueur_id']),
            $this->rencontres->selectRencontreById($dbLine['rencontre_id']),
            $dbLine['titulaire_ou_remplacant'] ? TitulaireOuRemplacant::fromName($dbLine['titulaire_ou_remplacant']) : null,
            $dbLine['note_performance'] ? Performance::fromValue($dbLine['note_performance']) : null,
            $dbLine['poste'] ? Poste::fromName($dbLine['poste']) : null
        );
    }

    // Méthode pour récupérer la liste de toutes les participations présentes dans la base de données
    public function selectAllParticipations() {
        $query = 'SELECT * FROM participation';
        $statement=$this->database->pdo()->prepare($query);
        if ($statement->execute()){
            return array_map(
                function($participation) { return $this->mapToParticipation($participation); },
                $statement->fetchAll(PDO::FETCH_ASSOC)
            );
        } else {
            exit();
        }
    }

    // Méthode pour récupérer la liste des participations associées à une rencontre donnée
    public function selectParticipationsByRencontreId(int $rencontreId): array {
        $query = 'SELECT * FROM participation WHERE rencontre_id = :rencontreId';
        $statement=$this->database->pdo()->prepare($query);
        $statement->bindValue(':rencontreId', $rencontreId);
        if ($statement->execute()){
            return array_map(
                function($participation) { return $this->mapToParticipation($participation); },
                $statement->fetchAll(PDO::FETCH_ASSOC)
            );
        } else {
            exit();
        }
    }

    // Méthode pour récupérer une participation à partir de son identifiant
    public function selectParticipationById(string $participationId): Participation
    {
        $query = 'SELECT * FROM participation WHERE participation_id = :participationId';
        $statement=$this->database->pdo()->prepare($query);
        $statement->bindValue(':participationId', $participationId);
        if ($statement->execute()){
             return $this->mapToParticipation($statement->fetch(PDO::FETCH_ASSOC));
        } else {
            exit();
        }
    }
    
    // Méthode pour insérer une nouvelle participation dans la base de données
    public function insertParticipation(Participation $participationACreer): bool {
        $query = '
            INSERT INTO participation(joueur_id, rencontre_id, titulaire_ou_remplacant, poste)
            VALUES (:joueur_id,:rencontre_id,:titulaire_ou_remplacant,:poste)
        ';
        $statement=$this->database->pdo()->prepare($query);
        $statement->bindValue(':joueur_id', $participationACreer->getParticipant()->getJoueurId());
        $statement->bindValue(':rencontre_id', $participationACreer->getRencontre()->getRencontreId());
        $statement->bindValue(':titulaire_ou_remplacant', $participationACreer->getTitulaireOuRemplacant()->name);
        $statement->bindValue(':poste', $participationACreer->getPoste()->name);

        return $statement->execute();
    }

    // Méthode pour mettre à jour une participation existante dans la base de données
    public function updateParticipation(Participation $participationAModifier): bool {
        $query = 'UPDATE participation 
                  SET 
                      titulaire_ou_remplacant = :titulaire_ou_remplacant,
                      poste = :poste,
                      joueur_id = :joueur_id
                  WHERE participation_id = :participation_id';
        $statement=$this->database->pdo()->prepare($query);
        $statement->bindValue(':titulaire_ou_remplacant', $participationAModifier->getTitulaireOuRemplacant()->name);
        $statement->bindValue(':poste', $participationAModifier->getPoste()->name);
        $statement->bindValue(':participation_id', $participationAModifier->getParticipationId());
        $statement->bindValue(':joueur_id', $participationAModifier->getParticipant()->getJoueurId());
        return $statement->execute();
    }

    // Méthode pour mettre à jour la performance d'une participation existante dans la base de données
    public function updatePerformance(Participation $participationAModifier): bool {
        $query = 'UPDATE participation 
                  SET 
                      note_performance = :note_performance
                  WHERE participation_id = :participation_id';
        $statement=$this->database->pdo()->prepare($query);
        $statement->bindValue(':note_performance', $participationAModifier->getPerformance()->value);
        $statement->bindValue(':participation_id', $participationAModifier->getParticipationId());
        return $statement->execute();
    }

    // Méthode pour supprimer une participation de la base de données à partir de son identifiant
    public function deleteParticipation(int $participationId) : bool {
        $query = 'DELETE FROM participation WHERE participation_id = :participationId';
        $statement=$this->database->pdo()->prepare($query);
        $statement->bindValue(':participationId', $participationId);
        return $statement->execute();
    }

    // Méthode pour vérifier si un poste est déjà occupé pour une rencontre donnée, en fonction du poste et du rôle (titulaire ou remplaçant)
    public function lePosteEstDejaOccupe(int $rencontreId, Poste $poste, TitulaireOuRemplacant $titulaireOuRemplacant) : bool {
        $query = '
                SELECT * FROM participation 
                WHERE rencontre_id = :rencontreId AND poste = :poste AND titulaire_ou_remplacant = :titulaireOuRemplacant
        ';
        $statement=$this->database->pdo()->prepare($query);
        $statement->bindValue(':rencontreId', $rencontreId);
        $statement->bindValue(':poste', $poste->name);
        $statement->bindValue('titulaireOuRemplacant', $titulaireOuRemplacant->name);
        if ($statement->execute()){
            return $statement->fetch() > 0;
        } else {
            exit();
        }
    }

    // Méthode pour vérifier si un joueur est déjà inscrit sur la feuille de match d'une rencontre donnée
    public function lejoueurEstDejaSurLaFeuilleDeMatch(int $rencontreId, int $joueur_id) : bool {
        $query = '
                SELECT * FROM participation 
                WHERE rencontre_id = :rencontreId AND joueur_id = :joueur_id;
        ';
        $statement=$this->database->pdo()->prepare($query);
        $statement->bindValue(':rencontreId', $rencontreId);
        $statement->bindValue(':joueur_id', $joueur_id);
        if ($statement->execute()){
            return $statement->fetch() > 0;
        } else {
            exit();
        }
    }
}