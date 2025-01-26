<?php

require_once __DIR__ . '/Config/Database.php';

class ACommander
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function isTableEmpty()
    {
        $query = "SELECT COUNT(*) FROM a_commander";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }

    public function getLastModified()
    {
        $query = "SELECT MAX(last_modified) FROM a_commander";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : null;
    }
    public function createACommander($produit_id, $quantite)
    {
        $existing = $this->getACommanderByProduitId($produit_id);
        if ($existing) {
            $newQuantite = $existing['quantite'] + $quantite;
            return $this->updateACommander($produit_id, $newQuantite);
        } else {
            $query = "INSERT INTO a_commander (produit_id, quantite) 
                  VALUES (:produit_id, :quantite)";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':produit_id' => $produit_id,
                ':quantite' => $quantite
            ]);

            if ($result) {
                $this->updateLastModifiedTimestamp();
            }

            return $result;
        }
    }

    public function getACommanderByProduitId($produit_id)
    {
        $query = "SELECT * FROM a_commander WHERE produit_id = :produit_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllACommanders()
    {
        $query = "SELECT a.*, p.nom, i.stock, p.alerte 
              FROM a_commander a 
              JOIN produit p ON a.produit_id = p.produit_id 
              JOIN inventaire i ON a.produit_id = i.produit_id 
              WHERE p.declencher_alerte = 'oui' AND i.stock < p.alerte";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCommandersWithoutConditions()
    {
        $query = "SELECT a.*, p.nom, i.stock, p.alerte 
                  FROM a_commander a 
                  JOIN produit p ON a.produit_id = p.produit_id 
                  JOIN inventaire i ON a.produit_id = i.produit_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateACommander($produit_id, $quantite)
    {
        $query = "UPDATE a_commander 
                  SET quantite = :quantite 
                  WHERE produit_id = :produit_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':produit_id' => $produit_id,
            ':quantite' => $quantite
        ]);

        if ($result) {
            $this->updateLastModifiedTimestamp();
        }

        return $result;
    }
    public function getAllProduitIds()
    {
        $query = "SELECT produit_id FROM a_commander";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Supprime un produit à commander et met à jour le timestamp de dernière modification.
     */
    public function deleteACommander($produit_id)
    {
        $query = "DELETE FROM a_commander WHERE produit_id = :produit_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([':produit_id' => $produit_id]);

        if ($result) {
            $this->updateLastModifiedTimestamp();
        }

        return $result;
    }

    public function exists($produit_id)
    {
        $query = "SELECT COUNT(*) FROM a_commander WHERE produit_id = :produit_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id]);
        return $stmt->fetchColumn() > 0;
    }
    public function deleteAllACommanders()
    {
        $query = "DELETE FROM a_commander";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute();
        if ($result) {
            $this->updateAfterMongoLoad();
        }
        return $result;
    }
    public function updateAfterMongoLoad($timestamp = null)
    {
        $timestamp = $timestamp ?: date('Y-m-d H:i:s');
        $query = "UPDATE a_commander SET last_mongo_load = :last_mongo_load, last_modified = :last_modified";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':last_mongo_load' => $timestamp,
            ':last_modified' => $timestamp
        ]);
    }
    public function updateLastMongoLoad()
    {
        $query = "UPDATE a_commander SET last_mongo_load = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($query);
        return $stmt->execute();
    }

    public function getLastMongoLoad()
    {
        $query = "SELECT MAX(last_mongo_load) as last_load FROM a_commander";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['last_load'];
    }
    /**
     * Vérifie si la table a_commander a été modifiée depuis le dernier chargement MongoDB.
     *
     * @return bool Retourne true si des modifications ont été détectées, false sinon.
     */
    public function hasBeenModifiedSinceLastMongoLoad()
    {
        // Requête SQL pour déterminer si des modifications ont eu lieu
        $query = "SELECT 
            CASE 
                -- Si la table est vide, on considère qu'il y a eu une modification
                -- (tous les éléments ont peut-être été supprimés)
                WHEN COUNT(*) = 0 THEN TRUE
                -- Si last_modified existe et est plus récent que last_mongo_load
                -- (ou si last_mongo_load n'existe pas), il y a eu une modification
                WHEN MAX(last_modified) IS NOT NULL AND 
                     (MAX(last_mongo_load) IS NULL OR MAX(last_modified) > MAX(last_mongo_load)) 
                THEN TRUE
                -- Dans tous les autres cas, pas de modification
                ELSE FALSE
            END as modified,
            -- Récupère aussi les timestamps pour un usage potentiel ultérieur
            MAX(last_modified) as last_modified,
            MAX(last_mongo_load) as last_mongo_load
          FROM a_commander";

        // Prépare et exécute la requête
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        // Récupère le résultat
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // Retourne un booléen indiquant si des modifications ont été détectées
        return (bool)$result['modified'];
    }

    public function countACommander()
    {
        $query = "SELECT COUNT(*) FROM a_commander";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function updateLastModifiedTimestamp()
    { {
            $timestamp = date('Y-m-d H:i:s');
            $query = "UPDATE a_commander SET last_modified = :timestamp";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([':timestamp' => $timestamp]);

            // Optionnel : vérifier si des lignes ont été affectées
            if ($result && $stmt->rowCount() > 0) {
                // Des lignes ont été mises à jour
                return true;
            } elseif ($result) {
                // La requête a réussi, mais aucune ligne n'a été modifiée (peut-être que la table est vide)
                return true;
            } else {
                // Une erreur s'est produite
                return false;
            }
        }
    }
    /**
     * Obtient le nombre total de produits à commander
     * @return int Nombre total de produits
     */
    public function getTotalACommander()
    {
        $sql = "SELECT COUNT(*) as total FROM a_commander";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int)$stmt->fetch()['total'];
    }
    public function getACommanderPaginesEtTries($offset, $limit, $sortColumn = 'produit_id', $sortDirection = 'asc')
{
    // Liste des colonnes autorisées pour le tri
    $allowedColumns = [
        'produit_id',
        'nom',
        'stock',
        'alerte',
        'quantite'
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
    $sql = "SELECT ac.*, p.nom, p.alerte, i.stock 
            FROM a_commander ac 
            JOIN produit p ON ac.produit_id = p.produit_id 
            LEFT JOIN inventaire i ON ac.produit_id = i.produit_id";

    // Ajout du tri
    $orderBy = match ($sortColumn) {
        'nom' => "p.nom " . $sortDirection,
        'stock' => "COALESCE(i.stock, 0) " . $sortDirection,
        'alerte' => "p.alerte " . $sortDirection,
        'quantite' => "ac.quantite " . $sortDirection,
        default => "ac.produit_id " . $sortDirection
    };

    $sql .= " ORDER BY " . $orderBy;
    $sql .= " LIMIT :offset, :limit";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}
}
