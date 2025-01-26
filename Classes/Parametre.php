<?php

require_once __DIR__ . '/Config/Database.php';

class Parametre
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Créer un paramètre
    public function createParametre($nom, $valeur)
    {
        $query = "INSERT INTO parametres (nom, valeur) VALUES (:nom, :valeur)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nom' => $nom,
            ':valeur' => $valeur
        ]);
    }

    // Lire un paramètre par nom
    public function getParametre($nom)
    {
        $query = "SELECT valeur FROM parametres WHERE nom = :nom";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':nom' => $nom]);
        return $stmt->fetchColumn();
    }

    // Mettre à jour un paramètre
    public function updateParametre($nom, $valeur)
    {
        $query = "UPDATE parametres SET valeur = :valeur WHERE nom = :nom";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nom' => $nom,
            ':valeur' => $valeur
        ]);
    }

    // Supprimer un paramètre
    public function deleteParametre($nom)
    {
        $query = "DELETE FROM parametres WHERE nom = :nom";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':nom' => $nom]);
    }

    // Récupérer tous les paramètres
    public function getAllParametres()
    {
        $query = "SELECT * FROM parametres";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}