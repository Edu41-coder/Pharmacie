<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';
$warning = '';

// Configuration de la pagination et du tri
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'produit_id';
$sortDirection = isset($_GET['direction']) ? $_GET['direction'] : 'asc';

// Récupérer les messages d'erreur ou de succès depuis les paramètres d'URL
if (isset($_GET['error'])) {
    $error = urldecode($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = urldecode($_GET['success']);
}

$a_commanderMySql = new ACommander();
$dbMongo = Database_Mongo::getInstance()->getBdd();
$aCommanderMongo = new ACommanderModel($dbMongo);

// Récupérer tous les produits à commander depuis MySQL (pour MongoDB)
$a_commander_mysql = $a_commanderMySql->getAllCommandersWithoutConditions();

// Récupérer les produits paginés et triés pour l'affichage
$totalItems = $a_commanderMySql->getTotalACommander();
$a_commander = $a_commanderMySql->getACommanderPaginesEtTries($offset, $itemsPerPage, $sortColumn, $sortDirection);

// Calculer le nombre total de pages
$totalPages = ceil($totalItems / $itemsPerPage);

$currentCount = $a_commanderMySql->countACommander();
$mongoCount = $aCommanderMongo->countProductsInLastCollection();

$isTableEmpty = $a_commanderMySql->isTableEmpty();
$hasBeenModifiedSinceLastMongoLoad = $a_commanderMySql->hasBeenModifiedSinceLastMongoLoad();
$lastMongoSave = $aCommanderMongo->getLastSaveTimestamp();
$lastMongoLoad = $a_commanderMySql->getLastMongoLoad();

// Récupérer la dernière collection chargée
$lastLoadedCollection = isset($_SESSION['last_loaded_collection']) ? $_SESSION['last_loaded_collection'] : null;

// Enregistrer des produits à commander dans MongoDB
if (isset($_POST['save_to_mongo'])) {
    $date = new DateTime();
    $collectionName = 'a_commander_' . $date->format('d-m-Y_H-i-s');
    $productsToSave = [];

    foreach ($a_commander_mysql as $item) {
        $productsToSave[] = [
            'produit_id' => $item['produit_id'],
            'nom' => $item['nom'],
            'quantite' => $item['quantite'],
            'stock' => $item['stock'] ?? null,
            'alerte' => $item['alerte'] ?? null,
            'createdAt' => $date->format('d-m-Y_H-i-s'),
        ];
    }

    if (empty($productsToSave)) {
        $error = "Aucun produit à enregistrer dans MongoDB. La table MySQL est vide.";
    } else {
        $result = $aCommanderMongo->saveProductsToOrder($productsToSave, $collectionName);
        if ($result) {
            $a_commanderMySql->updateAfterMongoLoad();
            $success = "Produits enregistrés avec succès dans MongoDB (Collection: $collectionName).";
            $lastMongoSave = time();
            $hasBeenModifiedSinceLastMongoLoad = false;
        } else {
            $error = "Erreur lors de l'enregistrement des produits dans MongoDB.";
        }
    }
    
    // Recharger les données paginées après la sauvegarde
    $a_commander = $a_commanderMySql->getACommanderPaginesEtTries($offset, $itemsPerPage, $sortColumn, $sortDirection);
}

// Charger des produits à commander depuis MongoDB
if (isset($_POST['load_from_mongo'])) {
    $selectedCollection = $_POST['selected_collection'] ?? 'a_commander';
    $a_commander_mongo = $aCommanderMongo->loadProductsToOrder($selectedCollection);

    if (!empty($a_commander_mongo)) {
        try {
            $a_commanderMySql->deleteAllACommanders();
            foreach ($a_commander_mongo as $item) {
                $a_commanderMySql->createACommander($item['produit_id'], $item['quantite']);
            }

            $a_commanderMySql->updateAfterMongoLoad();
            $_SESSION['last_loaded_collection'] = $selectedCollection;
            $lastLoadedCollection = $selectedCollection;
            $success = "Données chargées depuis MongoDB (Collection: $selectedCollection) et insérées dans MySQL.";
            $hasBeenModifiedSinceLastMongoLoad = false;
            $lastMongoLoad = date('Y-m-d H:i:s');

            // Recharger les données paginées après le chargement
            $totalItems = $a_commanderMySql->getTotalACommander();
            $a_commander = $a_commanderMySql->getACommanderPaginesEtTries($offset, $itemsPerPage, $sortColumn, $sortDirection);
            $totalPages = ceil($totalItems / $itemsPerPage);
            
            $currentCount = $a_commanderMySql->countACommander();
            $isTableEmpty = $a_commanderMySql->isTableEmpty();
        } catch (Exception $e) {
            $error = "Erreur lors du chargement des données : " . $e->getMessage();
        }
    } else {
        $error = "Aucun produit trouvé dans la collection MongoDB sélectionnée.";
    }
}

// Mettre à jour une collection existante dans MongoDB
if (isset($_POST['update_mongo'])) {
    $selectedCollection = $_POST['selected_collection'];
    $productsToUpdate = [];

    foreach ($a_commander_mysql as $item) {
        $productsToUpdate[] = [
            'produit_id' => $item['produit_id'],
            'nom' => $item['nom'],
            'quantite' => $item['quantite'],
            'stock' => $item['stock'] ?? null,
            'alerte' => $item['alerte'] ?? null,
        ];
    }

    $updateResult = $aCommanderMongo->updateProductsToOrder($productsToUpdate, $selectedCollection);
    if ($updateResult['success']) {
        $a_commanderMySql->updateAfterMongoLoad($updateResult['timestamp']);
        $hasBeenModifiedSinceLastMongoLoad = false;
        $lastMongoSave = time();
        $success = "La collection $selectedCollection a été mise à jour avec succès. {$updateResult['count']} produits mis à jour.";
        
        // Recharger les données paginées après la mise à jour
        $totalItems = $a_commanderMySql->getTotalACommander();
        $a_commander = $a_commanderMySql->getACommanderPaginesEtTries($offset, $itemsPerPage, $sortColumn, $sortDirection);
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        $currentCount = $a_commanderMySql->countACommander();
        $isTableEmpty = $a_commanderMySql->isTableEmpty();
    } else {
        $error = "Erreur lors de la mise à jour de la collection $selectedCollection: {$updateResult['message']}";
    }
}

// Messages d'état
if ($isTableEmpty && empty($error) && empty($success)) {
    $error = "Aucun produit à commander. Veuillez ajouter des produits ou charger une liste depuis MongoDB.";
} elseif ($hasBeenModifiedSinceLastMongoLoad && empty($error) && empty($success)) {
    $warning = "Des modifications ont été effectuées depuis le dernier chargement/sauvegarde MongoDB.";
} elseif (empty($error) && empty($success) && empty($warning)) {
    $success = "Liste de produits à commander prête.";
}

// Récupérer la liste des collections pour l'affichage dans le formulaire
$collections = $aCommanderMongo->getSortedCollections();

// Inclure le fichier de vue pour afficher les produits à commander
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Views/a_commander/index_a_commander.php';