<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/Config/Database.php';

class Commande
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Create
    public function createCommande()
    {
        $sql = "INSERT INTO commande (date_commande, statut, total) VALUES (NOW(), 'En attente', 0.00)";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function addProduitToCommande($commande_id, $produit_id, $quantite)
    {
        $sql = "INSERT INTO commande_produit (commande_id, produit_id, quantite) VALUES (:commande_id, :produit_id, :quantite)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        $stmt->bindParam(':produit_id', $produit_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Read
    public function getCommandeById($commande_id)
    {
        $sql = "SELECT * FROM commande WHERE commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllCommandes()
    {
        $sql = "SELECT * FROM commande ORDER BY date_commande DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getProduitsForCommande($commande_id)
    {
        $sql = "SELECT cp.*, p.nom, p.prix_vente_ht 
                FROM commande_produit cp
                JOIN produit p ON cp.produit_id = p.produit_id
                WHERE cp.commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update
    public function updateCommande($commande_id, $statut, $total, $date_commande)
    {
        // Vérifier que le statut est valide
        $statuts_valides = ['En attente', 'En cours', 'Livrée', 'Annulée'];
        if (!in_array($statut, $statuts_valides)) {
            return false;
        }
        $sql = "UPDATE commande SET statut = :statut, total = :total, date_commande = :date_commande WHERE commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
        $stmt->bindParam(':total', $total, PDO::PARAM_STR);
        $stmt->bindParam(':date_commande', $date_commande, PDO::PARAM_STR);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateProduitQuantite($commande_id, $produit_id, $quantite)
    {
        $sql = "UPDATE commande_produit SET quantite = :quantite 
                WHERE commande_id = :commande_id AND produit_id = :produit_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        $stmt->bindParam(':produit_id', $produit_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Delete
    public function deleteCommande($commande_id)
    {
        // D'abord, supprimer les entrées dans commande_produit
        $sql = "DELETE FROM commande_produit WHERE commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        $stmt->execute();

        // Ensuite, supprimer la commande elle-même
        $sql = "DELETE FROM commande WHERE commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function removeProduitFromCommande($commande_id, $produit_id)
    {
        $sql = "DELETE FROM commande_produit WHERE commande_id = :commande_id AND produit_id = :produit_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        $stmt->bindParam(':produit_id', $produit_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Utility methods
    public function isTableEmpty()
    {
        $sql = "SELECT COUNT(*) FROM commande";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }

    public function getAllProduitIds($commande_id)
    {
        $sql = "SELECT produit_id FROM commande_produit WHERE commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function exists($commande_id)
    {
        $sql = "SELECT COUNT(*) FROM commande WHERE commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    public function deleteAllCommandes()
    {
        try {
            // Commencer une transaction
            $this->db->beginTransaction();

            // Supprimer d'abord tous les enregistrements de la table commande_produit
            $sql = "DELETE FROM commande_produit";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            // Ensuite, supprimer tous les enregistrements de la table commande
            $sql = "DELETE FROM commande";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            // Valider la transaction
            $this->db->commit();

            // Retourner le nombre de commandes supprimées
            return $stmt->rowCount();
        } catch (PDOException $e) {
            // En cas d'erreur, annuler la transaction
            $this->db->rollBack();
            error_log("Erreur lors de la suppression de toutes les commandes : " . $e->getMessage());
            return false;
        }
    }
    public function calculateCommandeTotal($commandeId)
    {
        $sql = "SELECT SUM(cp.quantite * p.prix_vente_ht) as total
                FROM commande_produit cp
                JOIN produit p ON cp.produit_id = p.produit_id
                WHERE cp.commande_id = :commande_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commandeId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    public function filtrerCommandes($statut = '', $dateDebut = '', $dateFin = '', $recherche = '')
    {
        $sql = "SELECT * FROM commande WHERE 1=1";
        $params = [];

        if (!empty($statut)) {
            $sql .= " AND statut = :statut";
            $params[':statut'] = $statut;
        }

        if (!empty($dateDebut)) {
            $sql .= " AND date_commande >= :dateDebut";
            $params[':dateDebut'] = $dateDebut;
        }

        if (!empty($dateFin)) {
            $sql .= " AND date_commande <= :dateFin";
            $params[':dateFin'] = $dateFin;
        }

        if (!empty($recherche)) {
            $sql .= " AND (commande_id LIKE :recherche OR statut LIKE :recherche)";
            $params[':recherche'] = '%' . $recherche . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getProduitsNotInCommande($commande_id)
    {
        $sql = "SELECT p.* FROM produit p
            WHERE p.produit_id NOT IN (
                SELECT cp.produit_id 
                FROM commande_produit cp 
                WHERE cp.commande_id = :commande_id
            )";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':commande_id', $commande_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateCommandeProduits($commande_id, $produits)
    {
        $this->db->beginTransaction();
        try {
            // Récupérer les produits actuels de la commande
            $produitsActuels = $this->getProduitsForCommande($commande_id);
            $produitsActuelsIds = array_column($produitsActuels, 'produit_id');

            foreach ($produits as $produit_id => $quantite) {
                $produit_id = intval($produit_id);
                $quantite = intval($quantite);

                if ($quantite > 0) {
                    if (in_array($produit_id, $produitsActuelsIds)) {
                        // Mettre à jour la quantité si le produit existe déjà
                        $this->updateProduitQuantite($commande_id, $produit_id, $quantite);
                    } else {
                        // Ajouter le produit s'il n'existe pas
                        $this->addProduitToCommande($commande_id, $produit_id, $quantite);
                    }
                } else {
                    // Supprimer le produit si la quantité est 0 ou négative
                    $this->removeProduitFromCommande($commande_id, $produit_id);
                }
            }

            // Supprimer les produits qui ne sont plus dans la liste
            foreach ($produitsActuelsIds as $produitActuelId) {
                if (!isset($produits[$produitActuelId])) {
                    $this->removeProduitFromCommande($commande_id, $produitActuelId);
                }
            }

            // Recalculer et mettre à jour le total de la commande
            $newTotal = $this->calculateCommandeTotal($commande_id);
            $this->updateCommandeTotal($commande_id, $newTotal);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de la mise à jour des produits de la commande : " . $e->getMessage());
            return false;
        }
    }
    public function updateCommandeTotal($commandeId, $newTotal)
    {
        $sql = "UPDATE commande SET total = :total WHERE commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':total', $newTotal, PDO::PARAM_STR);
        $stmt->bindParam(':commande_id', $commandeId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function getMontantTotalCommandesLivrees($dateDebut = null, $dateFin = null)
    {
        $query = "SELECT SUM(total) as total FROM commande WHERE statut = 'Livrée'";
        $params = [];

        if ($dateDebut !== null && $dateFin !== null) {
            $query .= " AND date_commande BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    public function getTotalCommandes()
    {
        $sql = "SELECT COUNT(DISTINCT c.commande_id) FROM commande c";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getAllCommandesPaginated($offset, $limit, $sortColumn = 'date_commande', $sortOrder = 'DESC')
    {
        $sql = "SELECT c.*, 
                GROUP_CONCAT(CONCAT(p.nom, ' (', cp.quantite, ')') SEPARATOR ', ') as produits
                FROM commande c
                LEFT JOIN commande_produit cp ON c.commande_id = cp.commande_id
                LEFT JOIN produit p ON cp.produit_id = p.produit_id
                GROUP BY c.commande_id
                ORDER BY c." . $sortColumn . " " . $sortOrder . "
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filtrerCommandesPaginated($statut, $dateDebut, $dateFin, $recherche, $offset, $limit, $sortColumn = 'date_commande', $sortOrder = 'DESC')
    {
        $sql = "SELECT c.*, 
                GROUP_CONCAT(CONCAT(p.nom, ' (', cp.quantite, ')') SEPARATOR ', ') as produits
                FROM commande c
                LEFT JOIN commande_produit cp ON c.commande_id = cp.commande_id
                LEFT JOIN produit p ON cp.produit_id = p.produit_id
                WHERE 1=1";
        $params = [];

        if ($statut) {
            $sql .= " AND c.statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($dateDebut) {
            $sql .= " AND DATE(c.date_commande) >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }
        if ($dateFin) {
            $sql .= " AND DATE(c.date_commande) <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }
        if ($recherche) {
            $sql .= " AND (c.commande_id LIKE :recherche 
                     OR p.nom LIKE :recherche 
                     OR c.statut LIKE :recherche)";
            $params[':recherche'] = "%$recherche%";
        }

        $sql .= " GROUP BY c.commande_id
                  ORDER BY c." . $sortColumn . " " . $sortOrder . "
                  LIMIT :limit OFFSET :offset";

        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getTotalCommandesFiltrees($statut, $dateDebut, $dateFin, $recherche)
    {
        $sql = "SELECT COUNT(DISTINCT c.commande_id) 
                FROM commande c
                LEFT JOIN commande_produit cp ON c.commande_id = cp.commande_id
                LEFT JOIN produit p ON cp.produit_id = p.produit_id
                WHERE 1=1";

        $params = [];

        if ($statut) {
            $sql .= " AND c.statut = :statut";
            $params[':statut'] = $statut;
        }

        if ($dateDebut) {
            $sql .= " AND DATE(c.date_commande) >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }

        if ($dateFin) {
            $sql .= " AND DATE(c.date_commande) <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }

        if ($recherche) {
            $sql .= " AND (c.commande_id LIKE :recherche 
                     OR p.nom LIKE :recherche 
                     OR c.statut LIKE :recherche)";
            $params[':recherche'] = "%$recherche%";
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
