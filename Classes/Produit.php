<?php

require_once __DIR__ . '/Config/Database.php';

class Produit
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    public function isConnected()
    {
        return $this->db !== null;
    }
    // Create
    public function createProduit($nom, $description, $prix_vente_ht, $prescription, $taux_remboursement, $alerte, $declencher_alerte)
    {
        if ($taux_remboursement !== null && ($taux_remboursement < 0 || $taux_remboursement > 100)) {
            error_log("Erreur : Le taux de remboursement doit être entre 0 et 100 ou null.");
            return false;
        }
        $query = "INSERT INTO produit (nom, description, prix_vente_ht, prescription, taux_remboursement, alerte, declencher_alerte, is_deleted) 
                  VALUES (:nom, :description, :prix_vente_ht, :prescription, :taux_remboursement, :alerte, :declencher_alerte, 0)";
        $stmt = $this->db->prepare($query);
        if ($stmt->execute([
            ':nom' => $nom,
            ':description' => $description,
            ':prix_vente_ht' => $prix_vente_ht,
            ':prescription' => $prescription,
            ':taux_remboursement' => $taux_remboursement,
            ':alerte' => $alerte,
            ':declencher_alerte' => $declencher_alerte
        ])) {
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("Erreur lors de l'insertion du produit: " . $errorInfo[2]);
            return false;
        }
    }

    // Read
    public function getProduitById($id, $includeDeleted = false)
    {
        $query = "SELECT * FROM produit WHERE produit_id = :id";
        if (!$includeDeleted) {
            $query .= " AND is_deleted = 0";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllProduits($includeDeleted = false)
    {
        $query = "SELECT * FROM produit";
        if (!$includeDeleted) {
            $query .= " WHERE is_deleted = 0";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update
    public function updateProduit($id, $nom, $description, $prix_vente_ht, $prescription, $taux_remboursement, $alerte, $declencher_alerte, $is_deleted)
    {
        if ($taux_remboursement !== null && ($taux_remboursement < 0 || $taux_remboursement > 100)) {
            error_log("Erreur : Le taux de remboursement doit être entre 0 et 100 ou null.");
            return false;
        }

        $query = "UPDATE produit SET 
              nom = :nom, 
              description = :description, 
              prix_vente_ht = :prix_vente_ht, 
              prescription = :prescription, 
              taux_remboursement = :taux_remboursement, 
              alerte = :alerte, 
              declencher_alerte = :declencher_alerte,
              is_deleted = :is_deleted
              WHERE produit_id = :id";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':nom' => $nom,
            ':description' => $description,
            ':prix_vente_ht' => $prix_vente_ht,
            ':prescription' => $prescription,
            ':taux_remboursement' => $taux_remboursement,
            ':alerte' => $alerte,
            ':declencher_alerte' => $declencher_alerte,
            ':is_deleted' => $is_deleted
        ]);
    }
    // Soft Delete
    public function softDeleteProduit($id)
    {
        $query = "UPDATE produit SET is_deleted = 1 WHERE produit_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    // Restore
    public function restoreProduit($id)
    {
        $query = "UPDATE produit SET is_deleted = 0 WHERE produit_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    // Méthodes pour gérer la table d'association ordonnance_produit
    public function addOrdonnanceProduit($ordonnance_id, $produit_id)
    {
        $query = "INSERT INTO ordonnance_produit (ordonnance_id, produit_id) 
                  SELECT :ordonnance_id, :produit_id 
                  FROM produit WHERE produit_id = :produit_id AND is_deleted = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':ordonnance_id' => $ordonnance_id,
            ':produit_id' => $produit_id
        ]);
    }

    public function getOrdonnanceProduits($ordonnance_id)
    {
        $query = "SELECT p.produit_id, p.nom FROM produit p
                  JOIN ordonnance_produit op ON p.produit_id = op.produit_id
                  WHERE op.ordonnance_id = :ordonnance_id AND p.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ordonnance_id' => $ordonnance_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteOrdonnanceProduit($ordonnance_id, $produit_id)
    {
        $query = "DELETE op FROM ordonnance_produit op
                  JOIN produit p ON p.produit_id = op.produit_id
                  WHERE op.ordonnance_id = :ordonnance_id AND op.produit_id = :produit_id AND p.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':ordonnance_id' => $ordonnance_id,
            ':produit_id' => $produit_id
        ]);
    }
    public function getTotalProduits($includeDeleted = false)
    {
        $sql = "SELECT COUNT(*) as total FROM produit";
        if (!$includeDeleted) {
            $sql .= " WHERE is_deleted = 0";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    public function getProduitsPagines($offset, $limit, $includeDeleted = false)
    {
        $sql = "SELECT * FROM produit";
        if (!$includeDeleted) {
            $sql .= " WHERE is_deleted = 0";
        }
        $sql .= " ORDER BY produit_id LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
    public function getProduitsPaginesEtTries($offset, $limit, $sortColumn = 'produit_id', $sortDirection = 'asc', $includeDeleted = false)
    {
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = [
            'produit_id',
            'nom',
            'prix_vente_ht',
            'prix_vente_ttc',
            'prescription',
            'taux_remboursement',
            'alerte',
            'declencher_alerte',
            'is_deleted'
        ];

        // Nettoyage et validation des paramètres
        $sortColumn = trim(strtolower($sortColumn));
        $sortDirection = trim(strtoupper($sortDirection));

        // Vérification de la colonne de tri
        if (!in_array($sortColumn, $allowedColumns)) {
            $sortColumn = 'produit_id';
        }

        // Vérification de la direction du tri
        if (!in_array($sortDirection, ['ASC', 'DESC'])) {
            $sortDirection = 'ASC';
        }

        // Construction de la requête
        $sql = "SELECT p.* FROM (
            SELECT 
                produit.*,
                produit.prix_vente_ht * (1 + (SELECT valeur FROM parametres WHERE nom = 'TVA')/100) as prix_vente_ttc,
                CASE 
                    WHEN declencher_alerte = 'oui' THEN 1
                    ELSE 0
                END as declencher_alerte_order,
                CASE 
                    WHEN prescription = 'oui' THEN 1
                    ELSE 0
                END as prescription_order
            FROM produit
            " . (!$includeDeleted ? "WHERE is_deleted = 0" : "") . "
        ) p ";

        // Construction de la clause ORDER BY
        $orderBy = match ($sortColumn) {
            'declencher_alerte' => "declencher_alerte_order " . $sortDirection . ", p.nom ASC",
            'prescription' => "prescription_order " . $sortDirection . ", p.nom ASC",
            'prix_vente_ttc' => "prix_vente_ttc " . $sortDirection,
            default => "p." . $sortColumn . " " . $sortDirection
        };

        $sql .= " ORDER BY " . $orderBy;
        $sql .= " LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
    // Ajouter ces nouvelles méthodes à la classe Produit

    public function searchProduits($searchTerm, $isAdmin = false)
    {
        $query = "SELECT p.*, 
              p.prix_vente_ht * (1 + (SELECT valeur FROM parametres WHERE nom = 'TVA')/100) as prix_vente_ttc
              FROM produit p 
              WHERE p.nom LIKE :searchTerm";

        if (!$isAdmin) {
            $query .= " AND p.is_deleted = 0";
        }

        $query .= " ORDER BY p.nom ASC";

        $stmt = $this->db->prepare($query);
        $searchTerm = "%$searchTerm%";
        $stmt->bindParam(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllProduitsForSearch($isAdmin = false)
    {
        $query = "SELECT produit_id, nom 
              FROM produit";

        if (!$isAdmin) {
            $query .= " WHERE is_deleted = 0";
        }

        $query .= " ORDER BY nom ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
