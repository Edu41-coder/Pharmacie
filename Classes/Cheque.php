<?php

require_once __DIR__ . '/Config/Database.php';

class Cheque
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createCheque($numero_cheque, $client_id, $montant, $etat = 'en_attente')
    {
        $query = "INSERT INTO cheque (numero_cheque, client_id, montant, etat) 
                  VALUES (:numero_cheque, :client_id, :montant, :etat)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':numero_cheque' => $numero_cheque,
            ':client_id' => $client_id,
            ':montant' => $montant,
            ':etat' => $etat
        ]);
        return $this->db->lastInsertId();
    }

    public function getChequeById($cheque_id)
    {
        $query = "SELECT * FROM cheque WHERE cheque_id = :cheque_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':cheque_id' => $cheque_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateCheque($cheque_id, $numero_cheque, $client_id, $montant, $etat)
    {
        $query = "UPDATE cheque SET 
              numero_cheque = :numero_cheque, 
              client_id = :client_id, 
              montant = :montant, 
              etat = :etat 
              WHERE cheque_id = :cheque_id";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ':cheque_id' => $cheque_id,
            ':numero_cheque' => $numero_cheque,
            ':client_id' => $client_id,
            ':montant' => $montant,
            ':etat' => $etat
        ]);
    }
    public function getChequesForClient($client_id)
    {
        $query = "SELECT * FROM cheque WHERE client_id = :client_id ORDER BY cheque_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':client_id' => $client_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChequesEnAttente()
    {
        $query = "SELECT c.*, cl.nom, cl.prenom 
                  FROM cheque c 
                  JOIN client cl ON c.client_id = cl.client_id 
                  WHERE c.etat = 'en_attente'
                  ORDER BY c.cheque_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteCheque($cheque_id)
    {
        $query = "DELETE FROM cheque WHERE cheque_id = :cheque_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':cheque_id' => $cheque_id]);
    }

    public function getChequesForVente($vente_id)
    {
        $query = "SELECT c.* 
                  FROM cheque c 
                  JOIN vente_paiement vp ON c.cheque_id = vp.cheque_id 
                  WHERE vp.vente_id = :vente_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':vente_id' => $vente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalMontantCheques($etat = null)
    {
        $query = "SELECT SUM(montant) as total FROM cheque";
        $params = [];
        if ($etat) {
            $query .= " WHERE etat = :etat";
            $params[':etat'] = $etat;
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getChequesForPeriod($start_date, $end_date, $etat = null)
    {
        $query = "SELECT c.*, cl.nom, cl.prenom 
                  FROM cheque c 
                  JOIN client cl ON c.client_id = cl.client_id 
                  WHERE c.date_creation BETWEEN :start_date AND :end_date";
        $params = [':start_date' => $start_date, ':end_date' => $end_date];

        if ($etat) {
            $query .= " AND c.etat = :etat";
            $params[':etat'] = $etat;
        }

        $query .= " ORDER BY c.date_creation DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllCheques($includeVenteInfo = true)
    {
        $query = "SELECT c.*, vp.vente_id, v.is_deleted as vente_is_deleted 
              FROM cheque c
              LEFT JOIN vente_paiement vp ON c.cheque_id = vp.cheque_id
              LEFT JOIN vente v ON vp.vente_id = v.vente_id";

        if ($includeVenteInfo) {
            $query .= " ORDER BY c.date_creation DESC";
        } else {
            $query .= " WHERE vp.vente_id IS NULL ORDER BY c.date_creation DESC";
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateChequeEtat($cheque_id, $etat)
    {
        $query = "UPDATE cheque SET etat = :etat WHERE cheque_id = :cheque_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':etat' => $etat,
            ':cheque_id' => $cheque_id
        ]);
    }
    public function getChequesWithFilters($etat = null, $dateDebut = null, $dateFin = null, $includeDeletedVentes = false)
    {
        $query = "SELECT c.*, vp.vente_id, vp.date_paiement, v.is_deleted as vente_is_deleted,
                         cl.nom as client_nom, cl.prenom as client_prenom
                  FROM cheque c
                  LEFT JOIN vente_paiement vp ON c.cheque_id = vp.cheque_id
                  LEFT JOIN vente v ON vp.vente_id = v.vente_id
                  LEFT JOIN client cl ON v.client_id = cl.client_id
                  WHERE 1=1";
        $params = [];
        if ($etat) {
            $query .= " AND c.etat = :etat";
            $params[':etat'] = $etat;
        }
        if ($dateDebut) {
            $query .= " AND vp.date_paiement >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }
        if ($dateFin) {
            $query .= " AND vp.date_paiement <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }
        if (!$includeDeletedVentes) {
            $query .= " AND (v.is_deleted = 0 OR v.is_deleted IS NULL)";
        }
        $query .= " ORDER BY vp.date_paiement DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function searchCheques($searchTerm, $criteria, $etat = null, $dateDebut = null, $dateFin = null, $includeDeletedVentes = false)
    {
        $query = "SELECT c.*, v.is_deleted as vente_is_deleted, vp.vente_id, vp.montant as montant_paiement,
                         cl.nom as client_nom, cl.prenom as client_prenom
                  FROM cheque c 
                  LEFT JOIN vente_paiement vp ON c.cheque_id = vp.cheque_id
                  LEFT JOIN vente v ON vp.vente_id = v.vente_id 
                  LEFT JOIN client cl ON c.client_id = cl.client_id
                  WHERE 1=1";
        $params = [];

        if (!empty($etat)) {
            $query .= " AND c.etat = :etat";
            $params[':etat'] = $etat;
        }
        if (!empty($dateDebut)) {
            $query .= " AND vp.date_paiement >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }
        if (!empty($dateFin)) {
            $query .= " AND vp.date_paiement <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }
        if (!$includeDeletedVentes) {
            $query .= " AND (v.is_deleted = 0 OR v.is_deleted IS NULL)";
        }

        if (!empty($searchTerm)) {
            switch ($criteria) {
                case 'numero_cheque':
                    $query .= " AND c.numero_cheque LIKE :searchTerm";
                    $params[':searchTerm'] = "%$searchTerm%";
                    break;
                case 'client':
                    $query .= " AND (cl.nom LIKE :searchTermNom OR cl.prenom LIKE :searchTermPrenom)";
                    $params[':searchTermNom'] = "%$searchTerm%";
                    $params[':searchTermPrenom'] = "%$searchTerm%";
                    break;
                case 'vente_id':
                    $query .= " AND vp.vente_id LIKE :searchTerm";
                    $params[':searchTerm'] = "%$searchTerm%";
                    break;
                default:
                    $query .= " AND (c.numero_cheque LIKE :searchTerm1 OR 
                               cl.nom LIKE :searchTerm2 OR cl.prenom LIKE :searchTerm3 OR 
                               vp.vente_id LIKE :searchTerm4)";
                    $params[':searchTerm1'] = "%$searchTerm%";
                    $params[':searchTerm2'] = "%$searchTerm%";
                    $params[':searchTerm3'] = "%$searchTerm%";
                    $params[':searchTerm4'] = "%$searchTerm%";
            }
        }

        error_log("Requête SQL finale : " . $query);
        error_log("Paramètres finaux : " . json_encode($params));

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Nombre de résultats : " . count($results));
            if (!empty($results)) {
                error_log("Premier résultat : " . json_encode($results[0], JSON_PRETTY_PRINT));
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Erreur PDO lors de l'exécution de la requête : " . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Récupère une liste paginée des chèques avec filtres
     */
    public function getChequesPagines($offset, $limit, $etat = null, $dateDebut = null, $dateFin = null, $includeDeletedVentes = false, $sortColumn = 'cheque_id', $sortOrder = 'DESC')
{
    $sql = "SELECT ch.*, cl.nom as client_nom, cl.prenom as client_prenom,
        vp.date_paiement, vp.vente_id, v.is_deleted as vente_is_deleted
        FROM cheque ch 
        LEFT JOIN client cl ON ch.client_id = cl.client_id 
        LEFT JOIN vente_paiement vp ON ch.cheque_id = vp.cheque_id
        LEFT JOIN vente v ON vp.vente_id = v.vente_id
        WHERE 1=1";
    $params = [];
    if ($etat) {
        $sql .= " AND ch.etat = :etat";
        $params[':etat'] = $etat;
    }
    if ($dateDebut) {
        $sql .= " AND DATE(vp.date_paiement) >= :date_debut";
        $params[':date_debut'] = $dateDebut;
    }
    if ($dateFin) {
        $sql .= " AND DATE(vp.date_paiement) <= :date_fin";
        $params[':date_fin'] = $dateFin;
    }
    if (!$includeDeletedVentes) {
        $sql .= " AND (v.is_deleted = 0 OR v.is_deleted IS NULL)";
    }
    $sql .= " ORDER BY $sortColumn $sortOrder LIMIT :offset, :limit";
    $params[':offset'] = (int)$offset;
    $params[':limit'] = (int)$limit;
    $stmt = $this->db->prepare($sql);
    foreach ($params as $key => $value) {
        if ($key === ':offset' || $key === ':limit') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    return $stmt->fetchAll();
}  

    /**
     * Compte le nombre total de chèques avec filtres
     */
    public function getTotalCheques($etat = null, $dateDebut = null, $dateFin = null, $includeDeletedVentes = false)
    {
        $sql = "SELECT COUNT(DISTINCT ch.cheque_id) as total 
            FROM cheque ch 
            LEFT JOIN vente_paiement vp ON ch.cheque_id = vp.cheque_id
            LEFT JOIN vente v ON vp.vente_id = v.vente_id
            WHERE 1=1";

        $params = [];

        if ($etat) {
            $sql .= " AND ch.etat = :etat";
            $params[':etat'] = $etat;
        }

        if ($dateDebut) {
            $sql .= " AND DATE(vp.date_paiement) >= :date_debut";
            $params[':date_debut'] = $dateDebut;
        }

        if ($dateFin) {
            $sql .= " AND DATE(vp.date_paiement) <= :date_fin";
            $params[':date_fin'] = $dateFin;
        }

        if (!$includeDeletedVentes) {
            $sql .= " AND (v.is_deleted = 0 OR v.is_deleted IS NULL)";
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int)$stmt->fetch()['total'];
    }
}
