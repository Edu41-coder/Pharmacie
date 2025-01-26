<?php
require_once(__DIR__ . '/../../vendor/autoload.php');

use MongoDB\Client;
use Dotenv\Dotenv;

if (!class_exists('Database_Mongo')) {
    class Database_Mongo {
        private $host;
        private $db_name;
        private $client;
        private $bdd;
        private static $instance = null;

        private function __construct() {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            $this->host = $_ENV['MONGO_HOST'] ?? 'mongodb://localhost:27017';
            $this->db_name = $_ENV['MONGO_DATABASE'] ?? 'pharmacie';

            try {
                $this->client = new Client($this->host);
                $this->bdd = $this->client->{$this->db_name};
            } catch (Exception $e) {
                error_log("Erreur de connexion MongoDB : " . $e->getMessage());
                throw new Exception("Erreur de connexion à la base de données MongoDB.");
            }
        }

        public static function getInstance() {
            if (self::$instance == null) {
                self::$instance = new Database_Mongo();
            }
            return self::$instance;
        }

        public function getBdd() {
            return $this->bdd;
        }

        private function __clone() {}

        public function __wakeup() {
            throw new Exception("Impossible de désérialiser un singleton.");
        }
    }
}
?>