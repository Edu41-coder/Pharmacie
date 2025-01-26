<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$produitModel = new Produit();
$produits = $produitModel->getAllProduits();

$inventaireModel = new Inventaire();
$inventaireProduits = $inventaireModel->getAllInventaireProducts();

$inventaireProduitIds = array_column($inventaireProduits, 'produit_id');

$isInventaireVide = empty($inventaireProduits);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_all'])) {
        if (!$isInventaireVide) {
            $message = "L'inventaire doit être vide pour pouvoir le créer.";
        } else {
            $success = true;
            foreach ($produits as $produit) {
                if (!$inventaireModel->createInventaire($produit['produit_id'], 10)) {
                    $success = false;
                    break;
                }
            }
            $message = $success ? "Inventaire créé avec succès pour tous les produits avec une quantité de 10." : "Erreur lors de la création de l'inventaire.";
        }
    } elseif (isset($_POST['add_single'])) {
        $produit_id = $_POST['produit_id'];
        $stock = $_POST['stock'];

        if (!is_numeric($stock) || $stock < 0) {
            $message = "Le stock doit être un nombre entier positif ou nul.";
        } else {
            if ($inventaireModel->createOrUpdateInventaire($produit_id, $stock)) {
                $message = "Produit ajouté à l'inventaire avec succès.";
            } else {
                $message = "Erreur lors de l'ajout du produit à l'inventaire. Veuillez vérifier les logs pour plus de détails.";
            }
        }
    }
    
    // Redirection vers la même page pour afficher le message
    header('Location: ' . $_SERVER['PHP_SELF'] . '?message=' . urlencode($message));
    exit();
}

// Récupération du message depuis l'URL
$message = isset($_GET['message']) ? $_GET['message'] : '';

// Obtenir les informations sur la dernière modification
$lastChange = $inventaireModel->getLastModification();

// Ces variables seront utilisées dans le fichier de vue
?>