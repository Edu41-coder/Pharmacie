<?php

require_once __DIR__ . '/Config/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Récupère un utilisateur par son email
     * @param string $email Email de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getUserByEmail($email) {
        $query = "SELECT * FROM user WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son nom d'utilisateur
     * @param string $nom Nom de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getUserByUsername($nom) {
        $query = "SELECT * FROM user WHERE nom = :nom";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':nom' => $nom]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel utilisateur
     * @param string $nom Nom de l'utilisateur
     * @param string $prenom Prénom de l'utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe
     * @param int $role_id ID du rôle de l'utilisateur
     * @return bool True si l'insertion a réussi, false sinon
     */
    public function createUser($nom, $prenom, $email, $password, $role_id) {
        $query = "INSERT INTO user (nom, prenom, email, password, role_id) VALUES (:nom, :prenom, :email, :password, :role_id)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':password' => $password,
            ':role_id' => $role_id
        ]);
    }

    /**
     * Récupère un utilisateur par son ID
     * @param int $id ID de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getUserById($id) {
        $query = "SELECT * FROM user WHERE user_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $result= $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : false;
    }

    /**
     * Met à jour les informations d'un utilisateur
     * @param int $id ID de l'utilisateur
     * @param array $data Données à mettre à jour
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateUser($id, $data) {
        $allowedFields = ['nom', 'prenom', 'email', 'password', 'role_id'];
        $setFields = [];
        $params = [':id' => $id];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $setFields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($setFields)) {
            return false;
        }

        $query = "UPDATE user SET " . implode(', ', $setFields) . " WHERE user_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Change le mot de passe d'un utilisateur
     * @param int $id ID de l'utilisateur
     * @param string $newPassword Nouveau mot de passe
     * @return bool True si le changement a réussi, false sinon
     */
    public function changePassword($id, $newPassword) {
        $query = "UPDATE user SET password = :password WHERE user_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':password' => $newPassword
        ]);
    }

    /**
     * Supprime un utilisateur
     * @param int $id ID de l'utilisateur
     * @return bool True si la suppression a réussi, false sinon
     */
    public function deleteUser($id) {
        $query = "DELETE FROM user WHERE user_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Récupère tous les utilisateurs
     * @return array Liste de tous les utilisateurs
     */
    public function getAllUsers() {
        $query = "SELECT user_id, nom, prenom, email, role_id FROM user";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les utilisateurs par rôle
     * @param int $role_id ID du rôle de l'utilisateur
     * @return array Liste des utilisateurs avec le rôle spécifié
     */
    public function getUserByRole($role_id) {
        $query = "SELECT user_id, nom, prenom, email, role_id FROM user WHERE role_id = :role_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':role_id' => $role_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUserRole($user_id) {
        $query = "SELECT r.role_id, r.nom, r.description 
                  FROM user u 
                  JOIN role r ON u.role_id = r.role_id 
                  WHERE u.user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}