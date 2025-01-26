<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';
$item = null;

if (isset($_GET['id'])) {
    $produitId = $_GET['id'];
    $inventaireModel = new Inventaire();
    $item = $inventaireModel->getInventaireById($produitId);

    // Vérifiez si l'item existe
    if (!$item) {
        $error = "Produit non trouvé.";
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stock = $_POST['stock'];

            // Validation
            if (!is_numeric($stock) || $stock < 0) {
                $error = "Le stock doit être un nombre positif ou nul.";
            } else {
                // Utiliser la méthode updateInventaire pour mettre à jour le stock
                if ($inventaireModel->updateInventaire($produitId, $stock)) {
                    $success = "Produit mis à jour dans l'inventaire avec succès.";
                    $item['stock'] = $stock; // Mettre à jour le stock dans $item
                } else {
                    $error = "Erreur lors de la mise à jour du produit dans l'inventaire.";
                }
            }

            // Stocker les messages dans la session
            if (!empty($error)) {
                $_SESSION['error'] = $error;
            }
            if (!empty($success)) {
                $_SESSION['success'] = $success;
            }

            // Redirection correcte
            header('Location: /Pharmacie_S/Views/inventaire/edit_inventaire.php?id=' . $produitId);
            exit();
        }
    }
} else {
    $error = "Aucun ID de produit spécifié.";
}

// Stocker l'item dans la session pour la vue
$_SESSION['item'] = $item;

// Récupérer les messages de session
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}