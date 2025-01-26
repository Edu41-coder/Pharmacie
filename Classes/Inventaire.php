<?php

require_once __DIR__ . '/Config/Database.php';

class Inventaire
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Créer un nouvel enregistrement d'inventaire
    public function createInventaire($produit_id, $stock)
    {
        // Vérifier d'abord si le produit existe et n'est pas supprimé
        $checkQuery = "SELECT produit_id FROM produit WHERE produit_id = :produit_id AND is_deleted = 0";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([':produit_id' => $produit_id]);
        $existingProduct = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingProduct) {
            return false; // Le produit n'existe pas ou est supprimé
        }

        // Si le produit existe, procéder à l'insertion dans l'inventaire
        $query = "INSERT INTO inventaire (produit_id, stock, last_modified) 
              VALUES (:produit_id, :stock, CURRENT_TIMESTAMP)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':produit_id' => $produit_id,
            ':stock' => $stock
        ]);
    }

    // Lire tous les enregistrements d'inventaire avec les informations du produit
    public function getAllInventaire()
    {
        try {
            $sql = "SELECT 
                    i.produit_id,
                    p.nom,
                    i.stock,
                    p.alerte,
                    p.declencher_alerte
                FROM inventaire i 
                JOIN produit p ON i.produit_id = p.produit_id
                WHERE p.is_deleted = 0
                ORDER BY p.nom ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans getAllInventaire: " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération de l'inventaire");
        }
    }

    // Lire un enregistrement d'inventaire spécifique
    public function getInventaireById($produit_id)
    {
        $query = "SELECT i.produit_id, i.stock, 
                         p.nom, p.alerte, p.declencher_alerte 
                  FROM inventaire i
                  JOIN produit p ON i.produit_id = p.produit_id
                  WHERE i.produit_id = :produit_id 
                  AND p.is_deleted = 0";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour un enregistrement d'inventaire
    public function updateInventaire($produit_id, $stock)
    {
        $query = "UPDATE inventaire i
              JOIN produit p ON i.produit_id = p.produit_id
              SET i.stock = :stock, i.last_modified = CURRENT_TIMESTAMP
              WHERE i.produit_id = :produit_id AND p.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':produit_id' => $produit_id,
            ':stock' => $stock
        ]);
    }
    // Supprimer un enregistrement d'inventaire
    public function deleteInventaire($produit_id)
    {
        // Commencer une transaction
        $this->db->beginTransaction();

        try {
            // Trouver le produit le plus récemment modifié (qui n'est pas celui qu'on va supprimer)
            $findLastModifiedQuery = "SELECT produit_id FROM inventaire 
                                  WHERE produit_id != :produit_id 
                                  ORDER BY last_modified DESC LIMIT 1";
            $findStmt = $this->db->prepare($findLastModifiedQuery);
            $findStmt->execute([':produit_id' => $produit_id]);
            $lastModifiedProduct = $findStmt->fetch(PDO::FETCH_ASSOC);

            // Si on a trouvé un autre produit, mettre à jour son last_modified
            if ($lastModifiedProduct) {
                $updateQuery = "UPDATE inventaire 
                            SET last_modified = CURRENT_TIMESTAMP 
                            WHERE produit_id = :last_modified_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->execute([':last_modified_id' => $lastModifiedProduct['produit_id']]);
            }

            // Procéder à la suppression
            $deleteQuery = "DELETE FROM inventaire WHERE produit_id = :produit_id";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->execute([':produit_id' => $produit_id]);

            // Valider la transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $this->db->rollBack();
            return false;
        }
    }
    public function deleteAllInventaire()
    {
        // Commencer une transaction
        $this->db->beginTransaction();

        try {
            // Enregistrer le timestamp de la suppression totale
            $timestampFile = __DIR__ . '/last_total_deletion_timestamp.txt';
            file_put_contents($timestampFile, date('Y-m-d H:i:s'));

            // Supprimer tous les produits de l'inventaire
            $deleteQuery = "DELETE FROM inventaire";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->execute();

            // Valider la transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $this->db->rollBack();
            return false;
        }
    }

    // Vérifier si un produit existe dans l'inventaire
    public function produitExisteDansInventaire($produit_id)
    {
        $query = "SELECT COUNT(*) FROM inventaire i
                  JOIN produit p ON i.produit_id = p.produit_id
                  WHERE i.produit_id = :produit_id AND p.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id]);
        return $stmt->fetchColumn() > 0;
    }

    // Créer ou mettre à jour un enregistrement d'inventaire
    public function createOrUpdateInventaire($produit_id, $stock)
    {
        if ($this->produitExisteDansInventaire($produit_id)) {
            return $this->updateInventaire($produit_id, $stock);
        } else {
            return $this->createInventaire($produit_id, $stock);
        }
    }

    // Obtenir les produits avec un stock inférieur à leur seuil d'alerte
    public function getProduitsEnAlerte()
    {
        $query = "SELECT i.produit_id, i.stock, p.nom, p.alerte 
                  FROM inventaire i 
                  JOIN produit p ON i.produit_id = p.produit_id 
                  WHERE p.declencher_alerte = 'oui' AND i.stock < p.alerte AND p.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les produits présents dans l'inventaire
    public function getAllInventaireProducts()
    {
        $query = "SELECT i.produit_id, p.nom, i.stock 
                  FROM inventaire i 
                  JOIN produit p ON i.produit_id = p.produit_id
                  WHERE p.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllProduitIds()
    {
        $query = "SELECT i.produit_id 
                  FROM inventaire i
                  JOIN produit p ON i.produit_id = p.produit_id
                  WHERE p.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateStock($produit_id, $quantite)
    {
        // Vérifier le stock actuel
        $currentInventaire = $this->getInventaireById($produit_id);
        if (!$currentInventaire) {
            return false; // Le produit n'existe pas ou est supprimé
        }
        $currentStock = $currentInventaire['stock'];

        // Calculer le nouveau stock
        $newStock = $currentStock + $quantite;

        // Assurer que le stock ne devient jamais négatif
        $newStock = max(0, $newStock);

        $query = "UPDATE inventaire i
                  JOIN produit p ON i.produit_id = p.produit_id
                  SET i.stock = :newStock 
                  WHERE i.produit_id = :produit_id AND p.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':produit_id' => $produit_id,
            ':newStock' => $newStock
        ]);
    }

    public function countInventaireItems()
    {
        $query = "SELECT COUNT(*) FROM inventaire i
                  JOIN produit p ON i.produit_id = p.produit_id
                  WHERE p.is_deleted = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getLastModificationFromDB()
    {
        $query = "SELECT i.produit_id, i.stock, i.last_modified, p.nom 
              FROM inventaire i 
              JOIN produit p ON i.produit_id = p.produit_id 
              ORDER BY i.last_modified DESC 
              LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return [
                'last_modified' => $result['last_modified'],
                'action' => 'modification', // Par défaut, on considère que c'est une modification
                'produit_id' => $result['produit_id'],
                'nom' => $result['nom'],
                'stock' => $result['stock']
            ];
        }

        return null; // Retourne null si aucun résultat n'est trouvé
    }
    public function getLastModification()
    {
        $lastModification = $this->getLastModificationFromDB();
        if (!$lastModification) {
            // Si la table est vide, lire depuis le fichier de suppression totale
            $timestampFile = __DIR__ . '/last_total_deletion_timestamp.txt';
            if (file_exists($timestampFile)) {
                $timestamp = file_get_contents($timestampFile);
                return [
                    'last_modified' => $timestamp,
                    'action' => 'suppression totale',
                    'produit_id' => null
                ];
            }
        }
        return $lastModification;
    }

    public function detectChanges()
    {
        $currentCount = $this->countInventaireItems();
        $lastModification = $this->getLastModification();
        $lastCountFile = __DIR__ . '/last_inventory_count.txt';

        if (file_exists($lastCountFile)) {
            $lastCount = intval(file_get_contents($lastCountFile));
            if ($currentCount < $lastCount) {
                $change = 'suppression';
            } elseif ($currentCount > $lastCount) {
                $change = 'ajout';
            } else {
                $change = 'modification';
            }
        } else {
            $change = 'initial';
        }

        file_put_contents($lastCountFile, $currentCount);

        return [
            'type' => $change,
            'last_modified' => $lastModification['last_modified'] ?? null,
            'produit_id' => $lastModification['produit_id'] ?? null,
            'nom' => $lastModification['nom'] ?? null,
            'stock' => $lastModification['stock'] ?? null
        ];
    }
    // Méthodes à ajouter dans la classe Inventaire

    /**
     * Obtient le nombre total de produits dans l'inventaire
     * @return int Nombre total de produits
     */
    public function getTotalInventaire()
    {
        try {
            $sql = "SELECT COUNT(*) 
                FROM inventaire i 
                JOIN produit p ON i.produit_id = p.produit_id 
                WHERE p.is_deleted = 0";
            return $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur dans getTotalInventaire: " . $e->getMessage());
            throw new Exception("Erreur lors du comptage des éléments de l'inventaire");
        }
    }
    /**
     * Récupère une liste paginée de l'inventaire
     * @param int $offset Position de début
     * @param int $limit Nombre d'éléments par page
     * @return array Liste des produits de l'inventaire
     */
    public function getInventairePagine($offset, $limit)
    {
        $sql = "SELECT i.*, p.nom, p.alerte, p.declencher_alerte 
            FROM inventaire i 
            JOIN produit p ON i.produit_id = p.produit_id 
            ORDER BY i.produit_id 
            LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
    public function getInventairePaginesEtTries($offset, $limit, $sortColumn = 'produit_id', $sortDirection = 'asc')
    {
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = [
            'produit_id',
            'nom',
            'stock',
            'alerte',
            'declencher_alerte'
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

        try {
            // Construction de la requête avec une sous-requête pour le tri
            $sql = "SELECT 
                    i.produit_id,
                    p.nom,
                    i.stock,
                    p.alerte,
                    p.declencher_alerte,
                    CASE 
                        WHEN p.declencher_alerte = 'oui' THEN 1
                        ELSE 0
                    END as declencher_alerte_order
                FROM inventaire i 
                JOIN produit p ON i.produit_id = p.produit_id
                WHERE p.is_deleted = 0 ";

            // Construction de la clause ORDER BY
            $orderBy = match ($sortColumn) {
                'declencher_alerte' => "declencher_alerte_order " . $sortDirection . ", p.nom ASC",
                'nom' => "p.nom " . $sortDirection,
                'stock' => "i.stock " . $sortDirection . ", p.nom ASC",
                'alerte' => "p.alerte " . $sortDirection . ", p.nom ASC",
                default => "i.produit_id " . $sortDirection
            };

            $sql .= " ORDER BY " . $orderBy;
            $sql .= " LIMIT :offset, :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans getInventairePaginesEtTries: " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des données de l'inventaire");
        }
    }

    public function searchInventaireProducts($searchTerm)
    {
        $query = "SELECT i.produit_id, i.stock, p.nom, p.prescription, p.prix_vente_ht, p.taux_remboursement 
                  FROM inventaire i 
                  JOIN produit p ON i.produit_id = p.produit_id
                  WHERE p.nom LIKE :search 
                  AND p.is_deleted = 0 
                  ORDER BY p.nom ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['search' => "%$searchTerm%"]);

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'produit_id' => $row['produit_id'],
                'nom' => $row['nom'] . ' (Stock: ' . $row['stock'] . ')',
                'prescription' => $row['prescription'],
                'prix_vente_ht' => $row['prix_vente_ht'],
                'taux_remboursement' => $row['taux_remboursement'],
                'stock' => $row['stock']
            ];
        }

        return $results;
    }

    public function findProductPosition($productId, $sortColumn = 'produit_id', $sortDirection = 'asc')
    {
        try {
            // Log des paramètres d'entrée
            error_log("Recherche position pour - ID: $productId, Tri: $sortColumn, Direction: $sortDirection");

            $allowedColumns = ['produit_id', 'nom', 'stock', 'alerte', 'declencher_alerte'];
            if (!in_array($sortColumn, $allowedColumns)) {
                $sortColumn = 'produit_id';
            }

            $sortDirection = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

            // D'abord, vérifions si le produit existe
            $checkSql = "SELECT i.produit_id 
                        FROM inventaire i 
                        JOIN produit p ON i.produit_id = p.produit_id 
                        WHERE i.produit_id = :productId AND p.is_deleted = 0";

            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['productId' => $productId]);

            if (!$checkStmt->fetch()) {
                error_log("Produit non trouvé: $productId");
                return false;
            }

            // Construire la requête pour obtenir tous les IDs dans l'ordre
            $sql = "SELECT i.produit_id 
                    FROM inventaire i 
                    JOIN produit p ON i.produit_id = p.produit_id 
                    WHERE p.is_deleted = 0 
                    ORDER BY ";

            // Construire la clause ORDER BY
            $sql .= match ($sortColumn) {
                'declencher_alerte' => "CASE WHEN p.declencher_alerte = 'oui' THEN 1 ELSE 0 END " . $sortDirection . ", p.nom ASC",
                'nom' => "p.nom " . $sortDirection,
                'stock' => "i.stock " . $sortDirection . ", p.nom ASC",
                'alerte' => "p.alerte " . $sortDirection . ", p.nom ASC",
                default => "i.produit_id " . $sortDirection
            };

            error_log("SQL Query: " . $sql);

            // Exécuter la requête
            $stmt = $this->db->query($sql);
            $allIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Trouver la position dans le tableau
            $position = array_search($productId, $allIds);

            error_log("Position trouvée: " . ($position !== false ? $position : 'non trouvé'));
            error_log("Nombre total d'éléments: " . count($allIds));

            return $position;
        } catch (PDOException $e) {
            error_log("Erreur dans findProductPosition: " . $e->getMessage());
            return false;
        }
    }
}
