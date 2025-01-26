<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$error = '';
$success = '';

$a_commanderModel = new ACommander();
$inventoryModel = new Inventaire();

$inventaire = $inventoryModel->getAllInventaireProducts();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addAll = $_POST['add_all'] ?? '';
    $productId = $_POST['produit_id'] ?? '';

    $existingItems = $a_commanderModel->getAllACommanders();

    if ($addAll === 'all' && empty($existingItems)) {
        $products = $inventoryModel->getAllInventaireProducts();
        foreach ($products as $product) {
            if (!$a_commanderModel->exists($product['produit_id'])) {
                $a_commanderModel->createACommander($product['produit_id'], 0);
            }
        }
        $success = "Tous les produits ont été ajoutés à la liste à commander avec une quantité de 0.";
    } elseif ($addAll === 'alert' && empty($existingItems)) {
        $alertProducts = $inventoryModel->getProduitsEnAlerte();
        foreach ($alertProducts as $product) {
            if (!$a_commanderModel->exists($product['produit_id'])) {
                $a_commanderModel->createACommander($product['produit_id'], 0);
            }
        }
        $success = "Tous les produits avec alerte ont été ajoutés à la liste à commander avec une quantité de 0.";
    } elseif (!empty($productId)) {
        if (!$a_commanderModel->exists($productId)) {
            $productStock = array_filter($inventaire, function($item) use ($productId) {
                return $item['produit_id'] == $productId;
            });
            $productStock = reset($productStock);

            if ($productStock) {
                $a_commanderModel->createACommander($productId, 0);
                $success = "Le produit a été ajouté à la liste à commander avec une quantité de 0.";
            } else {
                $error = "Produit non trouvé dans l'inventaire.";
            }
        } else {
            $error = "Ce produit est déjà dans la liste à commander.";
        }
    } elseif (!empty($addAll)) {
        $error = "La liste doit être vide pour pouvoir ajouter tous les produits.";
    }
}

$isTableEmpty = $a_commanderModel->isTableEmpty();
$aCommanderProduitIds = $isTableEmpty ? [] : $a_commanderModel->getAllProduitIds();