<?php

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $host = $_ENV['DB_HOST'] ?? null;
        $db = $_ENV['DB_DATABASE'] ?? null;
        $user = $_ENV['DB_USERNAME'] ?? null;
        $pass = $_ENV['DB_PASSWORD'] ?? '';
        $charset = 'utf8mb4';

        if (!$host || !$db || !$user) {
            throw new Exception("Les variables d'environnement ne sont pas chargées correctement.");
        }

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Log détaillé de l'erreur de connexion
            error_log("Erreur de connexion : " . $e->getMessage());
            error_log("DSN : " . $dsn);
            error_log("Utilisateur : " . $user);
            throw new PDOException("Erreur de connexion à la base de données.");
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
?>