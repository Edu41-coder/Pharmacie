<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_facture'])) {
    $venteId = isset($_POST['vente_id']) ? intval($_POST['vente_id']) : 0;

    if ($venteId <= 0) {
        $_SESSION['error'] = "ID de vente invalide.";
        header('Location: /Pharmacie_S/Views/mes_documents/mes_ventes.php');
        exit;
    }

    try {
        $mongoDb = Database_Mongo::getInstance()->getBdd();
        $factureModel = new FactureModel($mongoDb);
        $venteModel = new Vente();
        $produitModel = new Produit();
        $parametreModel = new Parametre();
        $clientModel = new Client();

        $vente = $venteModel->getVenteById($venteId);

        if (!$vente) {
            throw new Exception("Vente non trouvée.");
        }

        // Récupérer la valeur de la TVA
        $tva = $parametreModel->getParametre('TVA');

        // Récupérer les détails des produits
        $produitsData = $venteModel->getProduitsVente($venteId);
        foreach ($produitsData as &$produit) {
            $produitDetails = $produitModel->getProduitById($produit['produit_id']);
            $produit['prix_unitaire'] = $produitDetails['prix_vente_ht'] * (1 + ($tva / 100));
            $produit['montant_total'] = $produit['prix_unitaire'] * $produit['quantite'];
            $produit['taux_remboursement'] = $produitDetails['taux_remboursement'];
            $produit['montant_a_rembourser'] = $produit['montant_total'] * ($produitDetails['taux_remboursement'] / 100);
            $produit['nom'] = $produitDetails['nom'];
        }

        // Récupérer les détails des paiements
        $paiementsData = $venteModel->getPaiementsByVenteId($venteId);
        $paiementsFacture = [];
        foreach ($paiementsData as $paiement) {
            $modes = explode(',', $paiement['mode_paiement']);
            $montantTotal = $paiement['montant'];
            $nombreModes = count($modes);

            foreach ($modes as $mode) {
                $paiementInfo = [
                    'mode' => trim($mode),
                    'montant' => $montantTotal / $nombreModes // Répartition égale du montant entre les modes
                ];
                if ($mode === 'cheque' && !empty($paiement['numero_cheque'])) {
                    $paiementInfo['numero_cheque'] = $paiement['numero_cheque'];
                }
                $paiementsFacture[] = $paiementInfo;
            }
        }

        // Récupérer les informations du client
        $clientInfo = $clientModel->getClientById($vente['client_id']);

        // Préparer les données de la vente
        $venteData = [
            'vente_id' => $venteId,
            'client_id' => $vente['client_id'],
            'client_nom' => $clientInfo['nom'] ?? 'Client de passage',
            'client_prenom' => $clientInfo['prenom'] ?? '',
            'date' => $vente['date'],
            'montant' => $vente['montant'],
            'montant_regle' => $vente['montant_regle'],
            'a_rembourser' => $vente['a_rembourser'],
            'commentaire' => $vente['commentaire'] ?? '',
        ];

        // Créer la facture
        if ($factureModel->saveFacture($venteData, $produitsData, $paiementsFacture)) {
            $_SESSION['success'] = "Facture créée avec succès.";
        } else {
            throw new Exception("Erreur lors de la création de la facture.");
        }

        header('Location: /Pharmacie_S/Views/mes_documents/factures.php?id=' . $venteId);
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: /Pharmacie_S/Views/mes_documents/mes_ventes.php');
        exit;
    }
}

$_SESSION['error'] = "Requête invalide.";
header('Location: /Pharmacie_S/Views/mes_documents/mes_ventes.php');
exit;