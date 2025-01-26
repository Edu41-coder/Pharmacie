<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Nettoyage et validation des entrées
        $nom = htmlspecialchars(trim($_POST['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
        $prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $telephone = htmlspecialchars(trim($_POST['telephone'] ?? ''), ENT_QUOTES, 'UTF-8');
        $adresse = htmlspecialchars(trim($_POST['adresse'] ?? ''), ENT_QUOTES, 'UTF-8');
        $commentaire = htmlspecialchars(trim($_POST['commentaire'] ?? ''), ENT_QUOTES, 'UTF-8');
        $numero_carte_vitale = htmlspecialchars(trim($_POST['numero_carte_vitale'] ?? ''), ENT_QUOTES, 'UTF-8');
        $cheques_impayes = 0;

        // Validation du nom
        if (empty($nom)) {
            throw new Exception("Le nom est obligatoire");
        }
        if (strlen($nom) > 100) {
            throw new Exception("Le nom ne doit pas dépasser 100 caractères");
        }
        if (!preg_match('/^[A-Za-zÀ-ÿ\s\-]+$/', $nom)) {
            throw new Exception("Le nom ne doit contenir que des lettres, espaces et tirets");
        }

        // Validation du prénom
        if (empty($prenom)) {
            throw new Exception("Le prénom est obligatoire");
        }
        if (strlen($prenom) > 100) {
            throw new Exception("Le prénom ne doit pas dépasser 100 caractères");
        }
        if (!preg_match('/^[A-Za-zÀ-ÿ\s\-]+$/', $prenom)) {
            throw new Exception("Le prénom ne doit contenir que des lettres, espaces et tirets");
        }

        // Validation de l'email
        if (empty($email)) {
            throw new Exception("L'email est obligatoire");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("L'email fourni n'est pas valide");
        }
        if (strlen($email) > 255) {
            throw new Exception("L'email ne doit pas dépasser 255 caractères");
        }

        // Validation du téléphone (optionnel)
        if (!empty($telephone)) {
            if (!preg_match('/^[0-9\+\-\s]{10,15}$/', $telephone)) {
                throw new Exception("Le format du numéro de téléphone n'est pas valide");
            }
        } else {
            $telephone = null;
        }

        // Validation de l'adresse (optionnelle)
        if (!empty($adresse)) {
            if (strlen($adresse) > 500) {
                throw new Exception("L'adresse ne doit pas dépasser 500 caractères");
            }
        } else {
            $adresse = null;
        }

        // Validation du commentaire (optionnel)
        if (!empty($commentaire)) {
            if (strlen($commentaire) > 1000) {
                throw new Exception("Le commentaire ne doit pas dépasser 1000 caractères");
            }
        } else {
            $commentaire = null;
        }

        // Validation du numéro de carte vitale (optionnel)
        if (!empty($numero_carte_vitale)) {
            if (!preg_match('/^[0-9]{15}$/', $numero_carte_vitale)) {
                throw new Exception("Le numéro de carte vitale doit contenir 15 chiffres");
            }
        } else {
            $numero_carte_vitale = null;
        }

        // Création du client
        $clientModel = new Client();
        if ($clientModel->createClient(
            $nom,
            $prenom,
            $email,
            $telephone,
            $adresse,
            $commentaire,
            $numero_carte_vitale,
            $cheques_impayes
        )) {
            $success = "Client créé avec succès.";
        } else {
            $error = "Erreur lors de la création du client";
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Conservation des données du formulaire en cas d'erreur
if (!empty($error)) {
    $_SESSION['form_data'] = $_POST;
}

// Récupération des données du formulaire précédent si erreur
$formData = [];
if (isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}