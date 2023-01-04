<?php

class Database {

    private static $_instance = null;
    private $pdo;

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @param void
     * @return Singleton de la connexion
     */

    public static function getInstance() {

        if (is_null(self::$_instance)) {
            self::$_instance = new Database();
        }

        return self::$_instance;
    }

    /**
     * Fonction permettant d'ouvrir une connexion à la base de données.
     */

    private function __construct() {

        $dsn = DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [

            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
			
        } catch (\PDOException $e) {

            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * Fonction permettant de fermer la connexion à la base de données.
     */

    public function __destruct() {

        $this->pdo = null;
    }

    /**
     * Function for SELECT returns array
     */

    public function selectQuery($query, $params) {

        try {

            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            return $queryPrepared->fetchAll();
			
        } catch (PDOException $e) {

            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * Function for UPDATE, DELETE, INSERT returns boolean 
     */

    public function executeQuery($query, $params) {
        try {

            $queryPrepared = $this->pdo->prepare($query);
            $queryRes = $queryPrepared->execute($params);
            return $queryRes;
			
        } catch (PDOException $e) {

            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
}
