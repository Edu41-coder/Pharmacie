<?php

require_once __DIR__ . '/Config/Database.php';

class Ordonnance
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    public function createOrdonnance($numero_ordonnance, $numero_ordre, $image_path = null)
    {
        if (empty($numero_ordonnance) || empty($numero_ordre)) {
            throw new Exception("Le numéro d'ordonnance et le numéro d'ordre ne peuvent pas être vides.");
        }

        // Utilisation correcte des backticks pour le nom de la colonne
        $query = "INSERT INTO ordonnance (numero_ordonnance, `numero_d'ordre`, image_path) VALUES (:numero_ordonnance, :numero_ordre, :image_path)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':numero_ordonnance' => $numero_ordonnance,
            ':numero_ordre' => $numero_ordre,
            ':image_path' => $image_path // Peut être null
        ]);
        return $this->db->lastInsertId();
    }
    public function addProduitToOrdonnance($ordonnance_id, $produit_id)
    {
        $query = "INSERT INTO ordonnance_produit (ordonnance_id, produit_id) VALUES (:ordonnance_id, :produit_id)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':ordonnance_id' => $ordonnance_id,
            ':produit_id' => $produit_id
        ]);
    }


    public function getOrdonnanceById($ordonnance_id)
    {
        $query = "SELECT * FROM ordonnance WHERE ordonnance_id = :ordonnance_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ordonnance_id' => $ordonnance_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProduitsForOrdonnance($ordonnance_id)
    {
        $query = "SELECT p.* FROM produit p
                  JOIN ordonnance_produit op ON p.produit_id = op.produit_id
                  WHERE op.ordonnance_id = :ordonnance_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ordonnance_id' => $ordonnance_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOrdonnance($ordonnance_id, $numero_ordonnance, $numero_ordre, $image_path = null)
    {
        $query = "UPDATE ordonnance SET numero_ordonnance = :numero_ordonnance, `numero_d'ordre` = :numero_ordre, image_path = :image_path 
                  WHERE ordonnance_id = :ordonnance_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':ordonnance_id' => $ordonnance_id,
            ':numero_ordonnance' => $numero_ordonnance,
            ':numero_ordre' => $numero_ordre,
            ':image_path' => $image_path // Peut être null
        ]);
    }

    public function deleteOrdonnance($ordonnance_id)
    {
        // Supprimer d'abord les enregistrements liés dans les tables de jonction
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM ordonnance_produit WHERE ordonnance_id = :ordonnance_id");
            $stmt->execute([':ordonnance_id' => $ordonnance_id]);

            $stmt = $this->db->prepare("DELETE FROM ordonnance WHERE ordonnance_id = :ordonnance_id");
            $stmt->execute([':ordonnance_id' => $ordonnance_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    public function getAllOrdonnancesWithVenteInfo()
{
    $query = "SELECT o.ordonnance_id, o.numero_ordonnance, o.`numero_d'ordre`, 
                     vo.vente_id, v.date, v.is_deleted
              FROM ordonnance o
              LEFT JOIN vente_ordonnance vo ON o.ordonnance_id = vo.ordonnance_id
              LEFT JOIN vente v ON vo.vente_id = v.vente_id
              ORDER BY o.ordonnance_id DESC";
    
    $stmt = $this->db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getAllOrdonnancesPaginated($offset, $limit) {
    $sql = "SELECT o.*, v.date, v.is_deleted, vo.vente_id 
            FROM ordonnance o 
            LEFT JOIN vente_ordonnance vo ON o.ordonnance_id = vo.ordonnance_id 
            LEFT JOIN vente v ON vo.vente_id = v.vente_id 
            ORDER BY o.ordonnance_id DESC 
            LIMIT :limit OFFSET :offset";
            
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getTotalOrdonnances() {
    $sql = "SELECT COUNT(*) FROM ordonnance";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}

public function searchOrdonnancesPaginated($searchTerm, $offset = 0, $limit = 10) {
    $sql = "SELECT o.*, v.date, v.is_deleted, vo.vente_id 
            FROM ordonnance o 
            LEFT JOIN vente_ordonnance vo ON o.ordonnance_id = vo.ordonnance_id 
            LEFT JOIN vente v ON vo.vente_id = v.vente_id 
            WHERE o.numero_ordonnance LIKE :search 
               OR o.`numero_d'ordre` LIKE :search 
               OR vo.vente_id LIKE :search 
            ORDER BY o.ordonnance_id DESC 
            LIMIT :limit OFFSET :offset";
            
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getTotalSearchResults($searchTerm) {
    $sql = "SELECT COUNT(*) 
            FROM ordonnance o 
            LEFT JOIN vente_ordonnance vo ON o.ordonnance_id = vo.ordonnance_id 
            LEFT JOIN vente v ON vo.vente_id = v.vente_id 
            WHERE o.numero_ordonnance LIKE :search 
               OR o.`numero_d'ordre` LIKE :search 
               OR vo.vente_id LIKE :search";
               
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn();
}
}
