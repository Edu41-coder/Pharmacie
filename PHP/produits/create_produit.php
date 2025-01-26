<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

$auth = new Authentification();
$user = $auth->checkAdminAuthentication();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Nettoyage et validation des entrées avec protection XSS
        $nom = htmlspecialchars(trim($_POST['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $prix_vente_ht = filter_var(
            trim($_POST['prix_vente_ht'] ?? ''),
            FILTER_SANITIZE_NUMBER_FLOAT,
            FILTER_FLAG_ALLOW_FRACTION
        );
        $prescription = htmlspecialchars(trim($_POST['prescription'] ?? ''), ENT_QUOTES, 'UTF-8');
        $taux_remboursement = filter_var(
            trim($_POST['taux_remboursement'] ?? ''),
            FILTER_SANITIZE_NUMBER_FLOAT
        );
        $alerte = filter_var(
            trim($_POST['alerte'] ?? ''),
            FILTER_SANITIZE_NUMBER_INT
        );
        $declencher_alerte = htmlspecialchars(trim($_POST['declencher_alerte'] ?? ''), ENT_QUOTES, 'UTF-8');

        // Validation du nom
        if (empty($nom)) {
            throw new Exception("Le nom du produit est obligatoire");
        }
        if (strlen($nom) > 100) {
            throw new Exception("Le nom du produit ne doit pas dépasser 100 caractères");
        }
        // Validation supplémentaire pour les caractères autorisés
        if (!preg_match('/^[A-Za-z0-9\s\-]+$/', $nom)) {
            throw new Exception("Le nom ne doit contenir que des lettres, des chiffres, des espaces et des tirets");
        }

        // Validation du prix
        if (!is_numeric($prix_vente_ht)) {
            throw new Exception("Le prix doit être un nombre");
        }
        if ($prix_vente_ht < 0) {
            throw new Exception("Le prix ne peut pas être négatif");
        }
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $prix_vente_ht)) {
            throw new Exception("Le prix doit avoir maximum 2 décimales");
        }

        // Validation de la prescription
        if (!in_array($prescription, ['oui', 'non'], true)) {
            throw new Exception("La valeur de prescription est invalide");
        }

        // Validation du taux de remboursement
        if (!empty($taux_remboursement)) {
            if (!is_numeric($taux_remboursement) || 
                $taux_remboursement < 0 || 
                $taux_remboursement > 100 ||
                !preg_match('/^\d+$/', $taux_remboursement)) {
                throw new Exception("Le taux de remboursement doit être un nombre entier entre 0 et 100");
            }
            $taux_remboursement = (int)$taux_remboursement;
        } else {
            $taux_remboursement = null;
        }

        // Validation de l'alerte
        if (!empty($alerte)) {
            if (!is_numeric($alerte) || 
                $alerte < 0 || 
                !preg_match('/^\d+$/', $alerte)) {
                throw new Exception("L'alerte doit être un nombre entier positif");
            }
            $alerte = (int)$alerte;
        } else {
            $alerte = null;
        }

        // Validation du déclenchement d'alerte
        if (!in_array($declencher_alerte, ['oui', 'non'], true)) {
            throw new Exception("La valeur de déclenchement d'alerte est invalide");
        }

        // Validation de la description (optionnelle)
        if (!empty($description) && strlen($description) > 500) {
            throw new Exception("La description ne doit pas dépasser 500 caractères");
        }

        // Si toutes les validations sont passées, créer le produit
        $produitModel = new Produit();
        if ($produitModel->createProduit(
            $nom, 
            $description ?: null, 
            $prix_vente_ht, 
            $prescription,
            $taux_remboursement,
            $alerte,
            $declencher_alerte
        )) {
            $_SESSION['success'] = "Produit créé avec succès.";
        } else {
            throw new Exception("Erreur lors de la création du produit");
        }

    } catch (Exception $e) {
        $_SESSION['error'] = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }

    header('Location: /Pharmacie_S/Views/produits/create_produit.php');
    exit();
}

// Récupération et nettoyage des messages de session
if (isset($_SESSION['error'])) {
    $error = htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8');
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8');
    unset($_SESSION['success']);
}