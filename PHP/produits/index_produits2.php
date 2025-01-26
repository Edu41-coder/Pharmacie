<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /Pharmacie_S/login.php');
    exit();
}

$userModel = new User();
$role = $userModel->getUserRole($_SESSION['user_id'])['role_id'];

$produitModel = new Produit();

// Déterminer si l'utilisateur est un administrateur
$isAdmin = ($role == 1); 

// Récupérer les produits en fonction du rôle de l'utilisateur
if ($isAdmin) {
    $produits = $produitModel->getAllProduits(true); // Inclure les produits supprimés pour l'admin
} else {
    $produits = $produitModel->getAllProduits(); // Produits non supprimés pour les autres utilisateurs
}

$error = '';
$success = '';

// Récupérer les messages de session
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Inclure la vue appropriée
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Views/produits/index_produits.php';