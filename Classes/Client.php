<?php

require_once __DIR__ . '/Config/Database.php';

class Client
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crée un nouveau client
     * @param string $nom Nom du client
     * @param string $prenom Prénom du client
     * @param string $email Email du client
     * @param string $telephone Téléphone du client
     * @param string $adresse Adresse du client
     * @param string $commentaire Commentaire sur le client
     * @param string $numero_carte_vitale Numéro de carte vitale du client
     * @param int $cheques_impayes Indicateur de chèques impayés
     * @return bool True si l'insertion a réussi, false sinon
     */
    public function createClient($nom, $prenom, $email, $telephone, $adresse, $commentaire, $numero_carte_vitale, $cheques_impayes)
    {
        $query = "INSERT INTO client (nom, prenom, email, telephone, adresse, commentaire, numero_carte_vitale, cheques_impayes) 
                  VALUES (:nom, :prenom, :email, :telephone, :adresse, :commentaire, :numero_carte_vitale, :cheques_impayes)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':adresse' => $adresse,
            ':commentaire' => $commentaire,
            ':numero_carte_vitale' => $numero_carte_vitale,
            ':cheques_impayes' => $cheques_impayes
        ]);
    }

    /**
     * Récupère un client par son ID
     * @param int $id ID du client
     * @return array|false Données du client ou false si non trouvé
     */
    public function getClientById($id)
    {
        $query = "SELECT * FROM client WHERE client_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour les informations d'un client
     * @param int $id ID du client
     * @param array $data Données à mettre à jour
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateClient($id, $data)
    {
        $allowedFields = ['nom', 'prenom', 'email', 'telephone', 'adresse', 'commentaire', 'numero_carte_vitale', 'cheques_impayes'];
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

        $query = "UPDATE client SET " . implode(', ', $setFields) . " WHERE client_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Supprime un client
     * @param int $id ID du client
     * @return bool True si la suppression a réussi, false sinon
     */
    public function deleteClient($id)
    {
        $query = "DELETE FROM client WHERE client_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Récupère tous les clients
     * @return array Liste de tous les clients
     */
    public function getAllClients()
    {
        $query = "SELECT * FROM client";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function searchClients($searchTerm, $criteria = 'all')
    {
        error_log("Début de searchClients avec searchTerm: $searchTerm et criteria: $criteria");

        $query = "SELECT * FROM client WHERE ";
        $params = [];

        switch ($criteria) {
            case 'name':
                $query .= "(nom LIKE :term_nom OR prenom LIKE :term_prenom)";
                $params[':term_nom'] = '%' . $searchTerm . '%';
                $params[':term_prenom'] = '%' . $searchTerm . '%';
                break;
            case 'email':
                $query .= "email LIKE :term_email";
                $params[':term_email'] = '%' . $searchTerm . '%';
                break;
            case 'phone':
                $query .= "telephone LIKE :term_phone";
                $params[':term_phone'] = '%' . $searchTerm . '%';
                break;
            case 'carte_vitale':
                $query .= "numero_carte_vitale LIKE :term_carte_vitale";
                $params[':term_carte_vitale'] = '%' . $searchTerm . '%';
                break;
            default:
                $query .= "(nom LIKE :term_all OR prenom LIKE :term_all OR email LIKE :term_all OR telephone LIKE :term_all OR numero_carte_vitale LIKE :term_all)";
                $params[':term_all'] = '%' . $searchTerm . '%';
                break;
        }

        error_log("Requête SQL: $query");
        error_log("Paramètres: " . print_r($params, true));

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Nombre de résultats: " . count($results));
            return $results;
        } catch (PDOException $e) {
            error_log("Erreur PDO dans searchClients: " . $e->getMessage());
            throw $e; // Rethrow the exception to be caught in the calling code
        }
    }
    public function updateChequesImpayes()
    {
        $query = "UPDATE client c
                  SET c.cheques_impayes = 
                    CASE 
                      WHEN EXISTS (
                        SELECT 1 
                        FROM cheque ch
                        JOIN vente_paiement vp ON ch.cheque_id = vp.cheque_id
                        JOIN vente v ON vp.vente_id = v.vente_id
                        WHERE ch.client_id = c.client_id 
                          AND ch.etat = 'refuse' 
                          AND v.is_deleted = 0
                      ) THEN 1
                      ELSE 0
                    END";

        $stmt = $this->db->prepare($query);
        return $stmt->execute();
    }

    public function searchClientsByCarteVitale($searchTerm)
    {
        $query = "SELECT * FROM client WHERE numero_carte_vitale LIKE :term";
        $stmt = $this->db->prepare($query);

        // Assurez-vous que le paramètre est correctement lié
        $param = '%' . $searchTerm . '%';
        $stmt->bindParam(':term', $param, PDO::PARAM_STR); // Ajout de PDO::PARAM_STR pour plus de sécurité

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllClientsPaginated($offset, $limit)
    {
        $sql = "SELECT * FROM client ORDER BY client_id LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalClients()
    {
        $sql = "SELECT COUNT(*) FROM client";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Pour la recherche paginée (utile avec les filtres)
    public function searchClientsPaginated($searchTerm, $criteria = 'all', $offset = 0, $limit = 10)
    {
        $sql = "SELECT * FROM client WHERE 1=1";
        $params = [];

        if ($searchTerm) {
            switch ($criteria) {
                case 'name':
                    $sql .= " AND (nom LIKE :search OR prenom LIKE :search)";
                    $params[':search'] = "%$searchTerm%";
                    break;
                case 'email':
                    $sql .= " AND email LIKE :search";
                    $params[':search'] = "%$searchTerm%";
                    break;
                case 'phone':
                    $sql .= " AND telephone LIKE :search";
                    $params[':search'] = "%$searchTerm%";
                    break;
                case 'carte_vitale':
                    $sql .= " AND numero_carte_vitale LIKE :search";
                    $params[':search'] = "%$searchTerm%";
                    break;
                case 'all':
                    $sql .= " AND (nom LIKE :search OR prenom LIKE :search OR email LIKE :search OR telephone LIKE :search OR numero_carte_vitale LIKE :search)";
                    $params[':search'] = "%$searchTerm%";
                    break;
            }
        }

        $sql .= " ORDER BY client_id LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalSearchResults($searchTerm, $criteria = 'all')
    {
        $sql = "SELECT COUNT(*) FROM client WHERE 1=1";
        $params = [];

        if ($searchTerm) {
            switch ($criteria) {
                case 'name':
                    $sql .= " AND (nom LIKE :search OR prenom LIKE :search)";
                    $params[':search'] = "%$searchTerm%";
                    break;
                case 'email':
                    $sql .= " AND email LIKE :search";
                    $params[':search'] = "%$searchTerm%";
                    break;
                case 'phone':
                    $sql .= " AND telephone LIKE :search";
                    $params[':search'] = "%$searchTerm%";
                    break;
                case 'carte_vitale':
                    $sql .= " AND numero_carte_vitale LIKE :search";
                    $params[':search'] = "%$searchTerm%";
                    break;
                case 'all':
                    $sql .= " AND (nom LIKE :search OR prenom LIKE :search OR email LIKE :search OR telephone LIKE :search OR numero_carte_vitale LIKE :search)";
                    $params[':search'] = "%$searchTerm%";
                    break;
            }
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    public function getAllClientsPaginesEtTries($offset, $limit, $sortColumn = 'client_id', $sortDirection = 'asc')
    {
        // Liste des colonnes autorisées pour le tri
        $allowedColumns = [
            'client_id',
            'nom',
            'prenom',
            'email',
            'telephone',
            'numero_carte_vitale',
            'cheques_impayes'
        ];

        // Nettoyage et validation des paramètres
        $sortColumn = trim(strtolower($sortColumn));
        $sortDirection = trim(strtoupper($sortDirection));

        // Vérification de la colonne de tri
        if (!in_array($sortColumn, $allowedColumns)) {
            $sortColumn = 'client_id';
        }

        // Vérification de la direction du tri
        if (!in_array($sortDirection, ['ASC', 'DESC'])) {
            $sortDirection = 'ASC';
        }

        // Construction de la requête
        $sql = "SELECT c.* FROM (
        SELECT 
            client.*,
            CASE 
                WHEN cheques_impayes = 1 THEN 1
                ELSE 0
            END as cheques_impayes_order
        FROM client
    ) c ";

        // Construction de la clause ORDER BY
        $orderBy = match ($sortColumn) {
            'cheques_impayes' => "cheques_impayes_order " . $sortDirection . ", c.nom ASC",
            default => "c." . $sortColumn . " " . $sortDirection
        };

        $sql .= " ORDER BY " . $orderBy;
        $sql .= " LIMIT :offset, :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
    /**
     * Recherche des clients pour le select avec formatage des résultats
     * 
     * @param string $searchTerm Terme de recherche
     * @return array Liste des clients formatée pour le select
     */
    public function searchClientsForSelect($searchTerm)
    {
        // Nettoyage et validation du terme de recherche
        $searchTerm = trim($searchTerm);
        if (empty($searchTerm)) {
            return [];
        }

        // Préparation des termes de recherche
        $searchTermLike = "%" . $this->escapeLikeString($searchTerm) . "%";

        // Log pour débogage
        error_log("Termes de recherche : " . print_r([$searchTermLike], true));

        $query = "SELECT 
                client_id,
                nom,
                prenom,
                cheques_impayes
              FROM client 
              WHERE (
                  nom LIKE :searchTerm_nom  -- Changement ici
                  OR prenom LIKE :searchTerm_prenom  -- Changement ici
              )
              ORDER BY 
                nom ASC,
                prenom ASC
              LIMIT 10";

        try {
            $stmt = $this->db->prepare($query);
            // Liaison des paramètres avec des noms uniques
            $stmt->bindValue(':searchTerm_nom', $searchTermLike, PDO::PARAM_STR);
            $stmt->bindValue(':searchTerm_prenom', $searchTermLike, PDO::PARAM_STR);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log des résultats avant formatage
            error_log("Résultats bruts : " . print_r($results, true));

            // Formater les résultats pour l'affichage
            return array_map(function ($client) {
                return [
                    'client_id' => (int)$client['client_id'],
                    'nom' => htmlspecialchars($client['nom'], ENT_QUOTES, 'UTF-8'),
                    'prenom' => htmlspecialchars($client['prenom'], ENT_QUOTES, 'UTF-8'),
                    'display' => sprintf(
                        "%s %s%s",
                        htmlspecialchars($client['nom'], ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($client['prenom'], ENT_QUOTES, 'UTF-8'),
                        $client['cheques_impayes'] ? ' ⚠️' : ''
                    ),
                    'has_warning' => (bool)$client['cheques_impayes']
                ];
            }, $results);
        } catch (PDOException $e) {
            // Log détaillé de l'erreur
            error_log("Erreur dans searchClientsForSelect: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            return [];
        }
    }
    /**
     * Échappe les caractères spéciaux dans une chaîne pour LIKE
     * 
     * @param string $value Chaîne à échapper
     * @return string Chaîne échappée
     */
    private function escapeLikeString($value)
    {
        return str_replace(['%', '_'], ['\%', '\_'], $value);
    }
    
}
