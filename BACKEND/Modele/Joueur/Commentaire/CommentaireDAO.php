<?php

// Déclaration du namespace
namespace R301\Modele\Joueur\Commentaire;

// Import des classes nécessaires
use DateTime;
use PDO;
use R301\Modele\DatabaseHandler;

// Classe de gestion des opérations liées aux commentaires dans la base de données
class CommentaireDAO {
    private static ?CommentaireDAO $instance = null;
    private readonly DatabaseHandler $database;

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
        $this->database = DatabaseHandler::getInstance();
    }

    // Méthode pour obtenir l'instance unique de CommentaireDAO
    public static function getInstance(): CommentaireDAO {
        if (self::$instance == null) {
            self::$instance = new CommentaireDAO();
        }
        return self::$instance;
    }

    // Méthode pour mapper une ligne de la base de données à un objet Commentaire
    private function mapToCommentaire(array $dbLine): Commentaire {
        return new Commentaire(
            $dbLine['commentaire_id'],
            $dbLine['contenu'],
            new DateTime($dbLine['date'])
        );
    }

    // Récupère la liste des commentaires associés à un joueur donné
    public function selectCommentaireByJoueurId(string $joueurId): array {
        $query = 'SELECT * FROM commentaire WHERE joueur_id = :joueur_id';
        $statement = $this->database->pdo()->prepare($query);
        $statement->execute(array('joueur_id' => $joueurId));
        if ($statement->execute()){
            return array_map(
                function($commentaire) { return $this->mapToCommentaire($commentaire); },
                $statement->fetchAll(PDO::FETCH_ASSOC)
            );
        } else {
            exit();
        }
    }

    // Insère un nouveau commentaire dans la base de données pour un joueur donné
    public function insertCommentaire(Commentaire $commentaire, string $joueurId): bool {
        $query = 'INSERT INTO commentaire(contenu,date,joueur_id) 
            values (:contenu,:date,:joueur_id)';
        $statement = $this->database->pdo()->prepare($query);
        $statement->bindValue(':joueur_id', $joueurId);
        $statement->bindValue(':contenu', $commentaire->getContenu());
        $statement->bindValue(':date', $commentaire->getDate()->format('Y-m-d H:i'));

        return $statement->execute();
    }

    // Supprime un commentaire de la base de données à partir de son identifiant
    public function deleteCommentaire(string $commentaireId): bool {
        $query = 'DELETE FROM commentaire WHERE commentaire_id = :commentaireId';
        $statement = $this->database->pdo()->prepare($query);
        $statement->bindValue(':commentaireId', $commentaireId);
        return ($statement->execute());
    }
}