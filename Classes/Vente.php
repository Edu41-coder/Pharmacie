<?php

require_once __DIR__ . '/Config/Database.php';

class Vente
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crée une nouvelle vente
     * @param int|null $client_id ID du client (null pour client de passage)
     * @param int $user_id ID de l'utilisateur
     * @param float $montant_total Montant total de la vente
     * @param float $montant_regle Montant réglé
     * @param float $montant_a_rembourser Montant à rembourser
     * @param string|null $commentaire Commentaire sur la vente
     * @return int|false ID de la vente créée ou false si échec
     */
    public function createVente($client_id, $user_id, $montant_total, $montant_regle, $montant_a_rembourser, $commentaire = null)
    {
        $query = "INSERT INTO vente (client_id, user_id, montant, montant_regle, a_rembourser, commentaire, is_deleted) 
                   VALUES (:client_id, :user_id, :montant_total, :montant_regle, :a_rembourser, :commentaire, 0)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            ':client_id' => $client_id,
            ':user_id' => $user_id,
            ':montant_total' => $montant_total,
            ':montant_regle' => $montant_regle,
            ':a_rembourser' => $montant_a_rembourser,
            ':commentaire' => $commentaire
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Ajoute un paiement à une vente
     * @param int $vente_id ID de la vente
     * @param string $mode_paiement Mode de paiement
     * @param float $montant Montant du paiement
     * @param int|null $cheque_id ID du chèque (si applicable)
     * @param string|null $numero_cheque Numéro du chèque (si applicable)
     * @return bool True si l'ajout a réussi, false sinon
     */
    public function addPaiementToVente($vente_id, $mode_paiement, $montant, $cheque_id = null, $numero_cheque = null)
    {
        $query = "INSERT INTO vente_paiement (vente_id, mode_paiement, montant, cheque_id, numero_cheque, date_paiement) 
              VALUES (:vente_id, :mode_paiement, :montant, :cheque_id, :numero_cheque, NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':vente_id' => $vente_id,
            ':mode_paiement' => $mode_paiement,
            ':montant' => $montant,
            ':cheque_id' => $cheque_id,
            ':numero_cheque' => $numero_cheque
        ]);
    }

    public function updateMontantRegle($vente_id, $montant_regle)
    {
        $query = "UPDATE vente SET montant_regle = :montant_regle WHERE vente_id = :vente_id AND is_deleted = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':vente_id' => $vente_id,
            ':montant_regle' => $montant_regle
        ]);
    }
    public function getVenteById($id, $includeDeleted = false)
    {
        $query = "SELECT v.*, DATE_FORMAT(v.date, '%Y-%m-%d %H:%i:%s') as date_vente 
                  FROM vente v 
                  WHERE v.vente_id = :id" . ($includeDeleted ? "" : " AND v.is_deleted = 0");
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateVente($id, $data)
    {
        $allowedFields = ['client_id', 'user_id', 'montant', 'commentaire'];
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

        $query = "UPDATE vente SET " . implode(', ', $setFields) . " WHERE vente_id = :id AND is_deleted = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function softDeleteVente($id)
    {
        $query = "UPDATE vente SET is_deleted = 1 WHERE vente_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
    public function restoreVente($id)
    {
        $query = "UPDATE vente SET is_deleted = 0 WHERE vente_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function getAllVentes($includeDeleted = false)
    {
        $query = "SELECT * FROM vente WHERE " . ($includeDeleted ? "1=1" : "is_deleted = 0") . " ORDER BY date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentesByUserId($user_id, $includeDeleted = false)
    {
        $query = "SELECT * FROM vente WHERE user_id = :user_id" . ($includeDeleted ? "" : " AND is_deleted = 0") . " ORDER BY date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getVentesByClientId($client_id, $includeDeleted = false)
    {
        $query = "SELECT * FROM vente WHERE client_id = :client_id";
        if (!$includeDeleted) {
            $query .= " AND is_deleted = 0";
        }
        $query .= " ORDER BY date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':client_id' => $client_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getVentesByClientIdAndDateRange($client_id, $dateDebut, $dateFin, $includeDeleted = false)
    {
        $query = "SELECT * FROM vente WHERE client_id = :client_id";
        $params = [':client_id' => $client_id];

        if ($dateDebut) {
            $query .= " AND date >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }
        if ($dateFin) {
            $query .= " AND date <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }
        if (!$includeDeleted) {
            $query .= " AND is_deleted = 0";
        }
        $query .= " ORDER BY date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getVentesByDate($date, $includeDeleted = false)
    {
        $query = "SELECT * FROM vente WHERE DATE(date) = :date" . ($includeDeleted ? "" : " AND is_deleted = 0") . " ORDER BY date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentesOrderedByMontant($order = 'DESC', $includeDeleted = false)
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $query = "SELECT * FROM vente WHERE " . ($includeDeleted ? "1=1" : "is_deleted = 0") . " ORDER BY montant $order, date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addOrdonnanceToVente($vente_id, $ordonnance_id)
    {
        $query = "INSERT INTO vente_ordonnance (vente_id, ordonnance_id) 
                  VALUES (:vente_id, :ordonnance_id)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':vente_id' => $vente_id,
            ':ordonnance_id' => $ordonnance_id
        ]);
    }

    public function addProduitToVente($vente_id, $produit_id, $quantite)
    {
        $query = "INSERT INTO vente_produit (vente_id, produit_id, quantite) 
                  VALUES (:vente_id, :produit_id, :quantite)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':vente_id' => $vente_id,
            ':produit_id' => $produit_id,
            ':quantite' => $quantite
        ]);
    }

    public function updateVenteMontant($vente_id, $montant_total)
    {
        $query = "UPDATE vente SET montant = :montant WHERE vente_id = :vente_id AND is_deleted = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':vente_id' => $vente_id,
            ':montant' => $montant_total
        ]);
    }

    public function getPaiementsByVenteId($vente_id)
    {
        $query = "SELECT vp.*, c.numero_cheque, c.etat as cheque_etat 
                  FROM vente_paiement vp 
                  LEFT JOIN cheque c ON vp.cheque_id = c.cheque_id 
                  WHERE vp.vente_id = :vente_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':vente_id' => $vente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalPaiementsByVenteId($vente_id)
    {
        $query = "SELECT SUM(montant) as total FROM vente_paiement WHERE vente_id = :vente_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':vente_id' => $vente_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function updateVenteMontantEtRemboursement($vente_id, $montant_total, $montant_a_rembourser)
    {
        $sql = "UPDATE vente SET montant = ?, a_rembourser = ? WHERE vente_id = ? AND is_deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$montant_total, $montant_a_rembourser, $vente_id]);
    }
    public function updateVenteCommentaire($venteId, $commentaire)
    {
        try {
            $query = "UPDATE vente SET commentaire = :commentaire WHERE vente_id = :vente_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':commentaire', $commentaire, PDO::PARAM_STR);
            $stmt->bindParam(':vente_id', $venteId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return true;
            } else {
                // Gérer l'erreur si la mise à jour échoue
                error_log("Erreur lors de la mise à jour du commentaire de la vente ID: $venteId");
                return false;
            }
        } catch (PDOException $e) {
            // Gérer les exceptions de base de données
            error_log("Erreur PDO lors de la mise à jour du commentaire de la vente: " . $e->getMessage());
            return false;
        }
    }
    public function getProduitsVente($venteId)
    {
        $query = "SELECT vp.*, p.nom, p.prix_vente_ht 
              FROM vente_produit vp 
              JOIN produit p ON vp.produit_id = p.produit_id 
              WHERE vp.vente_id = :vente_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':vente_id' => $venteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getVentesByUserIdAndDateRange($userId, $dateDebut, $dateFin, $includeDeleted = false)
    {
        $query = "SELECT * FROM vente WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if ($dateDebut && $dateFin) {
            $query .= " AND date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        } elseif ($dateDebut) {
            $query .= " AND date >= :date_debut";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
        } elseif ($dateFin) {
            $query .= " AND date <= :date_fin";
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        if (!$includeDeleted) {
            $query .= " AND is_deleted = 0";
        }

        $query .= " ORDER BY date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllVentesByDateRange($dateDebut, $dateFin, $includeDeleted = false)
    {
        $query = "SELECT * FROM vente WHERE 1=1";
        $params = [];

        if ($dateDebut) {
            $query .= " AND date >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }
        if ($dateFin) {
            $query .= " AND date <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }
        if (!$includeDeleted) {
            $query .= " AND is_deleted = 0";
        }

        $query .= " ORDER BY date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getStatistiquesVentes($dateDebut = null, $dateFin = null)
    {
        $sql = "SELECT v.user_id, u.nom, u.prenom, SUM(v.montant) as total_ventes 
            FROM vente v
            JOIN user u ON v.user_id = u.user_id
            WHERE v.is_deleted = 0";
        $params = [];

        if ($dateDebut && $dateFin) {
            $sql .= " AND v.date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        } elseif ($dateDebut) {
            $sql .= " AND v.date >= :date_debut";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
        } elseif ($dateFin) {
            $sql .= " AND v.date <= :date_fin";
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        $sql .= " GROUP BY v.user_id, u.nom, u.prenom ORDER BY total_ventes DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getVenteByPaiementId($cheque_id)
    {
        $query = "SELECT v.* FROM vente v 
              JOIN vente_paiement vp ON v.vente_id = vp.vente_id 
              WHERE vp.cheque_id = :cheque_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':cheque_id' => $cheque_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getVentesWithCheques($includeDeleted = false)
    {
        $query = "SELECT v.*, vp.cheque_id, vp.numero_cheque, vp.montant as montant_cheque 
              FROM vente v 
              JOIN vente_paiement vp ON v.vente_id = vp.vente_id 
              WHERE vp.mode_paiement = 'cheque'";

        if (!$includeDeleted) {
            $query .= " AND v.is_deleted = 0";
        }

        $query .= " ORDER BY v.date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getMontantTotalVentes($dateDebut = null, $dateFin = null)
    {
        $query = "SELECT SUM(montant) as total FROM vente WHERE is_deleted = 0";
        $params = [];

        if ($dateDebut !== null && $dateFin !== null) {
            $query .= " AND date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    public function getMontantTotalRemboursements($dateDebut = null, $dateFin = null)
    {
        $query = "SELECT SUM(a_rembourser) as total FROM vente WHERE is_deleted = 0";
        $params = [];

        if ($dateDebut !== null && $dateFin !== null) {
            $query .= " AND date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    public function getMontantTotalChequesImpayes($dateDebut = null, $dateFin = null)
    {
        $query = "SELECT SUM(vp.montant) as total 
                  FROM vente_paiement vp 
                  JOIN vente v ON vp.vente_id = v.vente_id 
                  JOIN cheque c ON vp.cheque_id = c.cheque_id 
                  WHERE v.is_deleted = 0 
                  AND vp.mode_paiement = 'cheque' 
                  AND c.etat = 'impaye'";
        $params = [];

        if ($dateDebut !== null && $dateFin !== null) {
            $query .= " AND v.date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    public function getAllVentesPaginated($offset, $limit, $includeDeleted = false)
    {
        $sql = "SELECT * FROM vente WHERE 1=1";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }
        $sql .= " ORDER BY vente_id DESC LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTotalVentes($includeDeleted = false)
    {
        $sql = "SELECT COUNT(*) as total FROM vente WHERE 1=1";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return (int)$stmt->fetch()['total'];
    }

    public function getVentesByUserIdPaginated($userId, $offset, $limit, $includeDeleted = false)
    {
        $sql = "SELECT * FROM vente WHERE user_id = :user_id";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }
        $sql .= " ORDER BY vente_id DESC LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTotalVentesByUserId($userId, $includeDeleted = false)
    {
        $sql = "SELECT COUNT(*) as total FROM vente WHERE user_id = :user_id";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        return (int)$stmt->fetch()['total'];
    }
    public function getVentesByUserIdAndDateRangePaginated($userId, $dateDebut, $dateFin, $includeDeleted = false, $offset, $limit)
    {
        $sql = "SELECT * FROM vente WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if ($dateDebut && $dateFin) {
            $sql .= " AND date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        } elseif ($dateDebut) {
            $sql .= " AND date >= :date_debut";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
        } elseif ($dateFin) {
            $sql .= " AND date <= :date_fin";
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        $sql .= " ORDER BY date DESC LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalVentesByUserIdAndDateRange($userId, $dateDebut, $dateFin, $includeDeleted = false)
    {
        $sql = "SELECT COUNT(*) as total FROM vente WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if ($dateDebut && $dateFin) {
            $sql .= " AND date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        } elseif ($dateDebut) {
            $sql .= " AND date >= :date_debut";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
        } elseif ($dateFin) {
            $sql .= " AND date <= :date_fin";
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int)$stmt->fetch()['total'];
    }

    public function getAllVentesByDateRangePaginated($dateDebut, $dateFin, $includeDeleted = false, $offset, $limit)
    {
        $sql = "SELECT * FROM vente WHERE 1=1";
        $params = [];

        if ($dateDebut && $dateFin) {
            $sql .= " AND date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        } elseif ($dateDebut) {
            $sql .= " AND date >= :date_debut";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
        } elseif ($dateFin) {
            $sql .= " AND date <= :date_fin";
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        $sql .= " ORDER BY date DESC LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalVentesByDateRange($dateDebut, $dateFin, $includeDeleted = false)
    {
        $sql = "SELECT COUNT(*) as total FROM vente WHERE 1=1";
        $params = [];

        if ($dateDebut && $dateFin) {
            $sql .= " AND date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        } elseif ($dateDebut) {
            $sql .= " AND date >= :date_debut";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
        } elseif ($dateFin) {
            $sql .= " AND date <= :date_fin";
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int)$stmt->fetch()['total'];
    }
    public function getVentesByClientIdPaginated($clientId, $includeDeleted = false, $offset = 0, $limit = 10)
    {
        $sql = "SELECT * FROM vente WHERE client_id = :client_id";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }
        $sql .= " ORDER BY date DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentesByClientIdAndDateRangePaginated($clientId, $dateDebut = null, $dateFin = null, $includeDeleted = false, $offset = 0, $limit = 10)
    {
        $sql = "SELECT * FROM vente WHERE client_id = :client_id";
        $params = [':client_id' => $clientId];

        if ($dateDebut) {
            $sql .= " AND DATE(date) >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }
        if ($dateFin) {
            $sql .= " AND DATE(date) <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        $sql .= " ORDER BY date DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalVentesForClient($clientId, $includeDeleted = false)
    {
        $sql = "SELECT COUNT(*) FROM vente WHERE client_id = :client_id";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getTotalVentesForClientFiltered($clientId, $dateDebut = null, $dateFin = null, $includeDeleted = false)
    {
        $sql = "SELECT COUNT(*) FROM vente WHERE client_id = :client_id";
        $params = [':client_id' => $clientId];

        if ($dateDebut) {
            $sql .= " AND DATE(date) >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }
        if ($dateFin) {
            $sql .= " AND DATE(date) <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
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
    public function getVentesClientPaginesEtTries($clientId, $offset, $limit, $sortColumn = 'date', $sortDirection = 'desc', $dateDebut = null, $dateFin = null, $includeDeleted = false)
    {
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = [
            'user_id',
            'date',
            'montant',
            'is_deleted'
        ];

        // Nettoyage et validation des paramètres
        $sortColumn = trim(strtolower($sortColumn));
        $sortDirection = trim(strtoupper($sortDirection));

        // Vérification de la colonne de tri
        if (!in_array($sortColumn, $allowedColumns)) {
            $sortColumn = 'date';
        }

        // Vérification de la direction du tri
        if (!in_array($sortDirection, ['ASC', 'DESC'])) {
            $sortDirection = 'DESC';
        }

        // Construction de la requête
        $sql = "SELECT * FROM vente WHERE client_id = :client_id";
        $params = [':client_id' => $clientId];

        // Ajout des filtres de date
        if ($dateDebut) {
            $sql .= " AND DATE(date) >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }
        if ($dateFin) {
            $sql .= " AND DATE(date) <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        // Ajout du tri
        $sql .= " ORDER BY " . $sortColumn . " " . $sortDirection;

        // Ajout de la pagination
        $sql .= " LIMIT :offset, :limit";
        $params[':offset'] = $offset;
        $params[':limit'] = $limit;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllVentesByDateRangePaginesEtTries($dateDebut, $dateFin, $includeDeleted = false, $offset, $limit, $sortColumn = 'date', $sortDirection = 'DESC')
    {
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = [
            'vente_id',
            'client_id',
            'user_id',
            'date',
            'montant',
            'is_deleted'
        ];

        // Validation des paramètres de tri
        $sortColumn = in_array(strtolower($sortColumn), array_map('strtolower', $allowedColumns))
            ? $sortColumn
            : 'date';
        $sortDirection = in_array(strtoupper($sortDirection), ['ASC', 'DESC'])
            ? strtoupper($sortDirection)
            : 'DESC';

        // Construction de la requête de base
        $sql = "SELECT * FROM vente WHERE 1=1";
        $params = [];

        // Ajout des conditions de date
        if ($dateDebut && $dateFin) {
            $sql .= " AND date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        } elseif ($dateDebut) {
            $sql .= " AND date >= :date_debut";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
        } elseif ($dateFin) {
            $sql .= " AND date <= :date_fin";
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        // Condition pour les ventes supprimées
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        // Ajout du tri
        $sql .= " ORDER BY " . $sortColumn . " " . $sortDirection;

        // Ajout de la pagination
        $sql .= " LIMIT :offset, :limit";

        // Exécution de la requête
        $stmt = $this->db->prepare($sql);

        // Binding des paramètres
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllVentesPaginesEtTries($offset, $limit, $includeDeleted = false, $sortColumn = 'date', $sortDirection = 'DESC')
    {
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = [
            'vente_id',
            'client_id',
            'user_id',
            'date',
            'montant',
            'is_deleted'
        ];

        // Validation des paramètres de tri
        $sortColumn = in_array(strtolower($sortColumn), array_map('strtolower', $allowedColumns)) ? $sortColumn : 'date';
        $sortDirection = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM vente WHERE 1=1";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }
        $sql .= " ORDER BY $sortColumn $sortDirection LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentesByUserIdPaginesEtTries($userId, $offset, $limit, $includeDeleted = false, $sortColumn = 'date', $sortDirection = 'DESC')
    {
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = [
            'vente_id',
            'client_id',
            'user_id',
            'date',
            'montant',
            'is_deleted'
        ];

        // Validation des paramètres de tri
        $sortColumn = in_array(strtolower($sortColumn), array_map('strtolower', $allowedColumns)) ? $sortColumn : 'date';
        $sortDirection = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM vente WHERE user_id = :user_id";
        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }
        $sql .= " ORDER BY $sortColumn $sortDirection LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentesByUserIdAndDateRangePaginesEtTries($userId, $dateDebut, $dateFin, $includeDeleted = false, $offset, $limit, $sortColumn = 'date', $sortDirection = 'DESC')
    {
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = [
            'vente_id',
            'client_id',
            'user_id',
            'date',
            'montant',
            'is_deleted'
        ];

        // Validation des paramètres de tri
        $sortColumn = in_array(strtolower($sortColumn), array_map('strtolower', $allowedColumns)) ? $sortColumn : 'date';
        $sortDirection = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM vente WHERE user_id = :user_id";
        $params = [':user_id' => $userId];

        if ($dateDebut && $dateFin) {
            $sql .= " AND date BETWEEN :date_debut AND :date_fin";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        } elseif ($dateDebut) {
            $sql .= " AND date >= :date_debut";
            $params[':date_debut'] = $dateDebut . ' 00:00:00';
        } elseif ($dateFin) {
            $sql .= " AND date <= :date_fin";
            $params[':date_fin'] = $dateFin . ' 23:59:59';
        }

        if (!$includeDeleted) {
            $sql .= " AND is_deleted = 0";
        }

        $sql .= " ORDER BY $sortColumn $sortDirection LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
