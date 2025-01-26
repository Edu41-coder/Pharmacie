<?php

require_once(__DIR__ . '/Config/Database_Mongo.php');
require_once(__DIR__ . '/Produit.php');

use MongoDB\Exception\InvalidArgumentException;

class FactureModel
{
    private $db;

    public function __construct($bdd)
    {
        if ($bdd instanceof MongoDB\Database) {
            $this->db = $bdd;
        } else {
            throw new Exception('Invalid MongoDB connection');
        }
    }

    /**
     * Enregistre une facture dans une collection MongoDB.
     * 
     * @param array $venteData Données de la vente
     * @param array $produitsData Données des produits vendus
     * @param array $paiementsData Données des paiements
     * @return bool True si l'enregistrement a réussi, false sinon
     */
    public function saveFacture($venteData, $produitsData, $paiementsData)
    {
        $collectionName = 'facture_' . time();
        $collection = $this->db->selectCollection($collectionName);
        $produitModel = new Produit();

        $facture = [
            'vente' => [
                'vente_id' => $venteData['vente_id'],
                'client_id' => $venteData['client_id'],
                'client_nom' => $venteData['client_nom'] ?? 'Client de passage',
                'client_prenom' => $venteData['client_prenom'] ?? '',
                'date_vente' => $venteData['date'] ?? date('Y-m-d H:i:s'),
                'montant_total' => round($venteData['montant'], 2),
                'montant_regle' => round($venteData['montant_regle'], 2),
                'montant_a_rembourser' => round($venteData['a_rembourser'], 2)
            ],
            'produits' => [],
            'paiements' => array_map(function ($paiement) {
                $paiementData = [
                    'mode' => $paiement['mode'],
                    'montant' => round($paiement['montant'], 2)
                ];
                if (isset($paiement['numero_cheque'])) {
                    $paiementData['numero_cheque'] = $paiement['numero_cheque'];
                }
                return $paiementData;
            }, $paiementsData),
            'createdAt' => date('d-m-Y_H-i-s')
        ];

        foreach ($produitsData as $produit) {
            $produitDetails = $produitModel->getProduitById($produit['produit_id']);
            $prix_unitaire = round($produit['prix_unitaire'], 2);
            $quantite = $produit['quantite'];
            $montant_total = $prix_unitaire * $quantite;
            $taux_remboursement = $produitDetails['taux_remboursement'] ?? 0;

            $facture['produits'][] = [
                'produit_id' => $produit['produit_id'],
                'nom' => $produitDetails['nom'],
                'quantite' => $quantite,
                'prix_unitaire' => $prix_unitaire,
                'montant_total' => round($montant_total, 2),
                'taux_remboursement' => $taux_remboursement,
                'montant_a_rembourser' => round($produit['montant_a_rembourser'], 2)
            ];
        }

        try {
            $result = $collection->insertOne($facture);
            return $result->getInsertedCount() > 0;
        } catch (\Exception $e) {
            error_log("Erreur lors de l'insertion de la facture dans MongoDB: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Charge une facture spécifique depuis MongoDB
     * @param string $factureId ID de la facture (nom de la collection)
     * @return array|null Données de la facture ou null si non trouvée
     */
    public function loadFacture($factureId)
    {
        $collection = $this->db->selectCollection($factureId);
        return $collection->findOne();
    }

    /**
     * Récupère les collections de factures triées par date de création dans l'ordre descendant
     * @return array Liste des noms de collections triées
     */
    public function getSortedFactures()
    {
        $collections = $this->db->listCollections();
        $factureTimestamps = [];

        foreach ($collections as $collection) {
            $collectionName = $collection->getName();
            if (strpos($collectionName, 'facture_') === 0) {
                $facture = $this->db->selectCollection($collectionName)->findOne();
                if ($facture && isset($facture['createdAt'])) {
                    $dateTime = DateTime::createFromFormat('d-m-Y_H-i-s', $facture['createdAt']);
                    if ($dateTime) {
                        $factureTimestamps[$collectionName] = $dateTime->getTimestamp();
                    }
                }
            }
        }

        arsort($factureTimestamps);
        return array_keys($factureTimestamps);
    }

    /**
     * Récupère la dernière facture enregistrée
     * @return array|null Données de la dernière facture ou null si aucune facture
     */
    public function getLastFacture()
    {
        $sortedFactures = $this->getSortedFactures();
        if (!empty($sortedFactures)) {
            return $this->loadFacture($sortedFactures[0]);
        }
        return null;
    }

    /**
     * Compte le nombre de factures enregistrées
     * @return int Nombre de factures
     */
    public function countFactures()
    {
        return count($this->getSortedFactures());
    }

    public function getFactureByVenteId($venteId)
    {
        $collections = $this->db->listCollections();
        foreach ($collections as $collection) {
            $collectionName = $collection->getName();
            if (strpos($collectionName, 'facture_') === 0) {
                $facture = $this->db->selectCollection($collectionName)->findOne(['vente.vente_id' => $venteId]);
                if ($facture) {
                    return $facture;
                }
            }
        }
        return null;
    }
}
