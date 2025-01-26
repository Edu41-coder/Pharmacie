<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

// Récupérer et valider les paramètres
$sort = $_GET['sort'] ?? 'produit_id';
$direction = $_GET['direction'] ?? 'asc';

// Liste des colonnes autorisées pour le tri
$allowedColumns = [
    'produit_id', 
    'nom_produit', 
    'quantite_stock', 
    'seuil_alerte', 
    'declencher_alerte',
    'date_derniere_entree',
    'date_derniere_sortie'
];

// Validation des paramètres
if (!in_array($sort, $allowedColumns)) {
    $sort = 'produit_id';
}
$direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

try {
    // Requête SQL pour récupérer les données triées
    $query = "SELECT i.*, p.nom as nom_produit 
              FROM inventaire i 
              JOIN produits p ON i.produit_id = p.produit_id 
              ORDER BY $sort $direction";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $inventaire = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Envoyer la réponse
    header('Content-Type: application/json');
    echo json_encode($inventaire);
    
} catch (PDOException $e) {
    // En cas d'erreur
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Erreur lors du tri de l\'inventaire']);
}