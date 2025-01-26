<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

$auth = new Authentification();
$user = $auth->checkAdminAuthentication();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $description = !empty($_POST['description']) ? $_POST['description'] : null;
    $prix_vente_ht = $_POST['prix_vente_ht'];
    $prescription = $_POST['prescription'];
    $taux_remboursement = !empty($_POST['taux_remboursement']) ? $_POST['taux_remboursement'] : null;
    $alerte = !empty($_POST['alerte']) ? $_POST['alerte'] : null;
    $declencher_alerte = $_POST['declencher_alerte'];

    // Validation
    if (!is_numeric($prix_vente_ht) || $prix_vente_ht < 0 || !preg_match('/^\d+(\.\d{1,2})?$/', $prix_vente_ht)) {
        $error = "Le prix de vente doit être un nombre décimal avec deux décimales.";
    }  elseif ($taux_remboursement !== null && (!is_numeric($taux_remboursement) || $taux_remboursement < 0 || $taux_remboursement > 100)) {
        $error = "Le taux de remboursement doit être un nombre entre 0 et 100 ou null.";
    }     
     elseif ($alerte !== null && (!is_numeric($alerte) || $alerte < 0)) {
        $error = "L'alerte doit être un entier positif ou nul.";
    } else {
        $produitModel = new Produit();

        if ($produitModel->createProduit($nom, $description, $prix_vente_ht, $prescription, $taux_remboursement, $alerte, $declencher_alerte)) {
            $success = "Produit créé avec succès.";
        } else {
            $error = "Erreur lors de la création du produit. Veuillez vérifier les logs pour plus de détails.";
        }
    }

    // Stockez les messages dans la session
    if (!empty($error)) {
        $_SESSION['error'] = $error;
    }
    if (!empty($success)) {
        $_SESSION['success'] = $success;
    }

    // Redirigez vers la page de création
    header('Location: /Pharmacie_S/Views/produits/create_produit.php');
    exit();
}

// Si ce n'est pas une requête POST, ne faites rien
// Le formulaire sera affiché normalement

// Récupérer les messages de session
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Ne redirigez pas, laissez le script continuer pour afficher le formulaire
?>