<?php

// Déclaration du namespace
namespace R301\Modele;

// Importation des classes nécessaires
use Exception;
use PDO;

// Classe gérant la connexion à la base de données, implémentant le pattern singleton pour garantir une seule instance de connexion à la base de données
class DatabaseHandler {
    private static ?DatabaseHandler $instance = null;
    private readonly PDO $linkpdo;

    // Constructeur privé pour empêcher l'instanciation directe de la classe DatabaseHandler, initialisant les paramètres de connexion à la base de données et établissant la connexion PDO
    private function __construct(){
        try{
            $host = getenv('DB_HOST');
            $dbname = getenv('DB_NAME');
            $username = getenv('DB_USER');
            $password = getenv('DB_PASSWORD');
            $this->linkpdo=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        }catch(Exception $e){
            die("Erreur : ".$e->getMessage());
        }
    }

    // Méthode pour obtenir l'instance unique de DatabaseHandler, implémentant le pattern singleton
    public static function getInstance(): DatabaseHandler
    {
        if (self::$instance == null) {
            self::$instance = new DatabaseHandler();
        }
        return self::$instance;
    }

    // Méthode pour obtenir l'objet PDO de la connexion à la base de données, permettant d'exécuter des requêtes SQL
    public function pdo(): PDO {
        return $this->linkpdo;
    }
}