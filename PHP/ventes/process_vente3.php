<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/Config/Database_Mongo.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $venteModel = new Vente();
    $inventaireModel = new Inventaire();
    $produitModel = new Produit();
    $ordonnanceModel = new Ordonnance();
    $chequeModel = new Cheque();
    $parametreModel = new Parametre();
    
    // Obtenez l'instance de Database_Mongo et récupérez la connexion à la base de données
    $mongoConnection = Database_Mongo::getInstance();
    $factureModel = new FactureModel($mongoConnection->getBdd());

    $client_id = $_POST['client_id'] != '0' ? $_POST['client_id'] : null;
    $modes_encaissement = isset($_POST['mode_encaissement']) ? $_POST['mode_encaissement'] : [];
    $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : null;
    $creer_facture = isset($_POST['creer_facture']) && $_POST['creer_facture'] == '1';

    $montant_total = 0;
    $montant_regle = 0;
    $montant_a_rembourser = 0;

    // Récupérer la valeur de la TVA
    $tva = $parametreModel->getParametre('TVA');

    // Calculer le montant total, le montant réglé et le montant à rembourser
    foreach ($_POST['produit_id'] as $index => $produit_id) {
        $quantite = $_POST['quantite'][$index];
        $produitDetails = $produitModel->getProduitById($produit_id);
        if ($produitDetails === false) {
            $error = "Produit non trouvé pour l'ID: " . $produit_id;
            break;
        }
        // Calculer le prix TTC
        $prix_ttc = $produitDetails['prix_vente_ht'] * (1 + ($tva / 100));
        $prix_produit = $prix_ttc * $quantite;
        $montant_total += $prix_produit;

        // Calculer le montant à rembourser sur le prix TTC
        $taux_remboursement = $produitDetails['taux_remboursement'] ?? 0;
        $montant_a_rembourser += $prix_produit * ($taux_remboursement / 100);
    }

    if (!empty($error)) {
        $_SESSION['error'] = $error;
        header('Location: /Pharmacie_S/Views/ventes/create_vente.php');
        exit();
    }

    // Calculer le montant réglé
    foreach ($modes_encaissement as $mode) {
        $montant_regle += $_POST['montant_' . $mode];
    }

    // Créer la vente
    $vente_id = $venteModel->createVente($client_id, $_SESSION['user_id'], $montant_total, $montant_regle, $montant_a_rembourser, $commentaire);

    if (!$vente_id) {
        $error = "Erreur lors de la création de la vente.";
    } else {
        $produitsData = [];
        $paiementsData = [];

        // Traiter chaque produit
        foreach ($_POST['produit_id'] as $index => $produit_id) {
            $quantite = $_POST['quantite'][$index];
            $produitDetails = $produitModel->getProduitById($produit_id);

            // Ajouter le produit à la vente
            $venteModel->addProduitToVente($vente_id, $produit_id, $quantite);

            // Mettre à jour l'inventaire
            $inventaireModel->updateStock($produit_id, -$quantite);

            // Préparer les données pour la facture
            $produitsData[] = [
                'produit_id' => $produit_id,
                'quantite' => $quantite,
                'prix_unitaire' => $produitDetails['prix_vente_ht'] * (1 + ($tva / 100)),
                'montant_a_rembourser' => ($produitDetails['prix_vente_ht'] * (1 + ($tva / 100))) * $quantite * ($produitDetails['taux_remboursement'] / 100)
            ];

            // Traiter l'ordonnance si nécessaire
            if ($produitDetails['prescription'] == 'oui') {
                $numero_ordonnance = $_POST['numero_ordonnance'][$index];
                $numero_ordre = $_POST['numero_ordre'][$index];

                // Gérer l'upload de l'image d'ordonnance
                $image_path = null;

                if (isset($_FILES['image_ordonnance']['name'][$index]) && $_FILES['image_ordonnance']['error'][$index] == 0) {
                    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/uploads/ordonnances/';
                    $image_name = uniqid() . '_' . basename($_FILES['image_ordonnance']['name'][$index]);
                    $image_path = $upload_dir . $image_name;

                    $file_type = pathinfo($image_path, PATHINFO_EXTENSION);
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array(strtolower($file_type), $allowed_types)) {
                        if (!move_uploaded_file($_FILES['image_ordonnance']['tmp_name'][$index], $image_path)) {
                            throw new Exception("Erreur lors du téléchargement de l'image pour le produit " . ($index + 1));
                        }
                    } else {
                        throw new Exception("Type de fichier non autorisé pour le produit " . ($index + 1) . ". Veuillez télécharger une image.");
                    }
                }

                // Créer une nouvelle ordonnance pour chaque produit qui en nécessite une
                $ordonnance_id = $ordonnanceModel->createOrdonnance($numero_ordonnance, $numero_ordre, $image_path);
                if (!$ordonnance_id) {
                    throw new Exception("Erreur lors de la création de l'ordonnance pour le produit " . ($index + 1));
                }

                $venteModel->addOrdonnanceToVente($vente_id, $ordonnance_id);
                $ordonnanceModel->addProduitToOrdonnance($ordonnance_id, $produit_id);
            }
        }

        // Traiter les paiements
        $modes_paiement_json = isset($_POST['modes_paiement_json']) ? $_POST['modes_paiement_json'] : '[]';
        $modes_paiement = json_decode($modes_paiement_json, true);

        $montant_total_regle = 0;
        $modes_paiement_combines = [];
        $cheque_id = null;
        foreach ($modes_paiement as $paiement) {
            $mode = $paiement['mode'];
            $montant = $paiement['montant'];
            $montant_total_regle += $montant;

            $modes_paiement_combines[] = $mode;

            $paiementsData[] = [
                'mode' => $mode,
                'montant' => $montant
            ];

            if ($mode == 'cheque') {
                $numero_cheque = $_POST['numero_cheque'];
                $cheque_id = $chequeModel->createCheque($numero_cheque, $client_id, $montant);
                if (!$cheque_id) {
                    $error = "Erreur lors de la création du chèque.";
                    break;
                }
                $paiementsData[count($paiementsData) - 1]['numero_cheque'] = $numero_cheque;
            }
        }

        if (empty($error)) {
            // Mettre à jour le montant réglé dans la table vente
            $venteModel->updateMontantRegle($vente_id, $montant_total_regle);

            // Ajouter une seule entrée dans vente_paiement avec tous les modes de paiement
            $modes_paiement_string = implode(',', array_unique($modes_paiement_combines));
            $venteModel->addPaiementToVente($vente_id, $modes_paiement_string, $montant_total_regle, $cheque_id, $numero_cheque);

            // Enregistrer la facture dans MongoDB seulement si la case est cochée
            if ($creer_facture) {
                $venteData = $venteModel->getVenteById($vente_id);
                $factureModel->saveFacture($venteData, $produitsData, $paiementsData);
                $success = "Vente enregistrée avec succès. La facture a été créée dans MongoDB.";
            } else {
                $success = "Vente enregistrée avec succès.";
            }
        }
    }

    // Stocker les messages dans la session
    if (!empty($error)) {
        $_SESSION['error'] = $error;
    }
    if (!empty($success)) {
        $_SESSION['success'] = $success;
    }

    // Rediriger vers la page de création de vente après le traitement
    header('Location: /Pharmacie_S/Views/ventes/create_vente.php');
    exit();
} else {
    header('Location: /Pharmacie_S/Views/ventes/create_vente.php');
    exit();
}