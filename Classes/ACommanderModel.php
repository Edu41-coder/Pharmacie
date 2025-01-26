<?php

require_once(__DIR__ . '/Config/Database_Mongo.php');

use MongoDB\Exception\InvalidArgumentException;

class ACommanderModel
{
    private $db; // Instance de MongoDB\Database

    public function __construct($bdd)
    {
        if ($bdd instanceof MongoDB\Database) {
            $this->db = $bdd;
        } else {
            throw new Exception('Invalid MongoDB connection');
        }
    }
    /**
     * Enregistre une liste de produits dans une collection MongoDB.
     * 
     * @param array $products Liste des produits à enregistrer.
     * @param string|null $collectionName Nom de la collection (optionnel).
     * @return bool True si l'enregistrement a réussi, false sinon.
     */
    public function saveProductsToOrder($products, $collectionName = null)
    {
        if (empty($products)) {
            return false; // Retourne false si la liste de produits est vide
        }
        if ($collectionName === null) {
            $collectionName = 'a_commander_' . time();
        }
        $collection = $this->db->selectCollection($collectionName);
        try {
            $result = $collection->insertMany($products);
            return $result->getInsertedCount() > 0;
        } catch (\Exception $e) {
            // Log l'erreur si nécessaire
            error_log("Erreur lors de l'insertion dans MongoDB: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Charge tous les produits à commander depuis une collection MongoDB spécifique
     * @param string $collectionName Nom de la collection
     * @return array Liste des produits à commander
     */
    public function loadProductsToOrder($collectionName = 'a_commander')
    {
        $collection = $this->db->selectCollection($collectionName);
        $cursor = $collection->find();
        return iterator_to_array($cursor);
    }


    /**
     * Vérifie si les modifications de SQL ont été enregistrées dans MongoDB
     * @param int $sqlLastModified Timestamp de la dernière modification en SQL
     * @return bool True si les modifications ont été enregistrées, false sinon
     */
    public function areModificationsSaved($sqlLastModified)
    {
        $lastCollection = $this->getLastCollection();
        if ($lastCollection) {
            // Récupérer le dernier document pour obtenir le champ createdAt
            $latestDocument = $this->db->selectCollection($lastCollection)
                ->findOne([], ['sort' => ['createdAt' => -1]]); // Tri descendant pour obtenir le plus récent

            if ($latestDocument && isset($latestDocument['createdAt'])) {
                // Convertir la chaîne de date en timestamp
                $dateTime = DateTime::createFromFormat('d-m-Y_H-i-s', $latestDocument['createdAt']);
                if ($dateTime) {
                    $latestMongoTimestamp = $dateTime->getTimestamp();
                    return $latestMongoTimestamp >= strtotime($sqlLastModified);
                }
            }
        }
        return false; // Si aucune collection n'est trouvée, retourner false
    }
    /**
     * Récupère les collections triées par date de création dans l'ordre descendant
     * @return array Liste des noms de collections triées
     */
    public function getSortedCollections()
    {
        $collections = $this->db->listCollections();
        $collectionTimestamps = [];

        foreach ($collections as $collection) {
            $collectionName = $collection->getName();
            if (strpos($collectionName, 'a_commander_') === 0) {
                // Récupérer le premier document pour obtenir le champ createdAt
                $firstDocument = $this->db->selectCollection($collectionName)
                    ->findOne([], ['sort' => ['createdAt' => 1]]); // Tri ascendant pour obtenir le plus ancien

                if ($firstDocument && isset($firstDocument['createdAt'])) {
                    // Convertir la chaîne de date en timestamp
                    $dateTime = DateTime::createFromFormat('d-m-Y_H-i-s', $firstDocument['createdAt']);
                    if ($dateTime) {
                        $collectionTimestamps[$collectionName] = $dateTime->getTimestamp();
                    }
                }
            }
        }

        // Trier les collections par date de création (timestamp) dans l'ordre descendant
        arsort($collectionTimestamps);

        return array_keys($collectionTimestamps); // Retourner les noms des collections triées
    }

    public function getLastCollection()
    {
        $collections = $this->db->listCollections();
        $latestCollection = null;
        $latestTimestamp = 0;

        foreach ($collections as $collection) {
            $collectionName = $collection->getName();
            if (strpos($collectionName, 'a_commander_') === 0) {
                // Récupérer le dernier document pour obtenir le champ createdAt
                $latestDocument = $this->db->selectCollection($collectionName)
                    ->findOne([], ['sort' => ['createdAt' => -1]]); // Tri descendant pour obtenir le plus récent

                if ($latestDocument && isset($latestDocument['createdAt'])) {
                    // Convertir la chaîne de date en timestamp
                    $dateTime = DateTime::createFromFormat('d-m-Y_H-i-s', $latestDocument['createdAt']);
                    if ($dateTime) {
                        $timestamp = $dateTime->getTimestamp();
                        if ($timestamp > $latestTimestamp) {
                            $latestTimestamp = $timestamp;
                            $latestCollection = $collectionName; // On garde le nom de la collection
                        }
                    }
                }
            }
        }

        return $latestCollection; // Retourne le nom de la dernière collection
    }
    public function countProductsInLastCollection()
    {
        $lastCollection = $this->getLastCollection();
        if ($lastCollection) {
            return $this->db->selectCollection($lastCollection)->countDocuments();
        }
        return 0;
    }
    public function getLastSaveTimestamp()
    {
        $lastCollection = $this->getLastCollection();
        if ($lastCollection) {
            $latestDocument = $this->db->selectCollection($lastCollection)
                ->findOne([], ['sort' => ['createdAt' => -1]]);
            if ($latestDocument && isset($latestDocument['createdAt'])) {
                $dateTime = DateTime::createFromFormat('d-m-Y_H-i-s', $latestDocument['createdAt']);
                if ($dateTime) {
                    return $dateTime->getTimestamp();
                }
            }
        }
        return null;
    }
    public function updateProductsToOrder($products, $collectionName)
{
    if (empty($products) || empty($collectionName)) {
        return ['success' => false, 'message' => 'Données invalides pour la mise à jour'];
    }
    
    $collection = $this->db->selectCollection($collectionName);
    
    try {
        // Supprimer tous les documents existants
        $collection->deleteMany([]);
        
        // Insérer les nouveaux documents
        $result = $collection->insertMany($products);
        
        // Mettre à jour le champ createdAt
        $newTimestamp = date('d-m-Y_H-i-s');
        $collection->updateMany(
            [],
            ['$set' => ['createdAt' => $newTimestamp]]
        );
        
        return [
            'success' => true,
            'message' => 'Mise à jour réussie',
            'count' => $result->getInsertedCount(),
            'timestamp' => $newTimestamp
        ];
    } catch (\Exception $e) {
        error_log("Erreur lors de la mise à jour dans MongoDB: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()];
    }
}

}
