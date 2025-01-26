<?php
$pageTitle = "Ventes du client";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/mes_ventes.css">
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<style>
    /* Styles de base */
    .form-row { 
        display: flex; 
        flex-wrap: wrap; 
        margin-bottom: 10px; 
    }
    .form-group { 
        margin-right: 15px; 
    }
    .search-container { 
        margin-bottom: 20px; 
    }

    /* Style pour la table */
    .cheques-table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-top: 20px; 
    }

    /* Style pour les en-têtes */
    .cheques-table th {
        background-color: #333333;
        color: white;
        padding: 8px;
        text-align: center;
        padding-right: 25px;
    }

     /* Survol des en-têtes */
    .cheques-table th[data-sort]:hover {
        background-color: #4a4a4a;
    }

    /* Style pour les en-têtes triables */
    .cheques-table th[data-sort] {
        cursor: pointer;
        position: relative;
    }

    /* Style pour les icônes de tri */
    .cheques-table th[data-sort]::after {
        font-family: "Font Awesome 5 Free";
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 900;
    }

    /* Styles pour les états de tri */
    .cheques-table th[data-sort].th-sort-asc::after {
        content: "\\f0de";
        transform: translateY(-50%) rotate(180deg);
    }

    .cheques-table th[data-sort].th-sort-desc::after {
        content: "\\f0dd";
        transform: translateY(-50%) rotate(0deg);
    }

    /* Style pour les cellules */
    .cheques-table td { 
        border: 1px solid #ddd; 
        padding: 8px; 
        text-align: center; 
    }
     /* Styles de priorité maximale */
    #ventes-client .cheques-table tbody tr:nth-child(even) { 
        background-color: #f9f9f9 !important; 
    }

    #ventes-client .cheques-table tbody tr:hover { 
        background-color: #e3f2fd !important; 
        transition: background-color 0.2s ease !important;
    /* Styles responsives */
@media screen and (max-width: 768px) {
    .form-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-group {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .cheques-table {
        min-width: 500px;
    }
    
    .cheques-table th,
    .cheques-table td {
        padding: 6px;
        font-size: 14px;
    }
    
    .pagination {
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
    }
    
    .back-link {
        padding: 5px 10px;
        font-size: 14px;

    }

</style>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

$auth = new Authentification();
$user = $auth->getCurrentUser();
$venteModel = new Vente();
$clientModel = new Client();

// Récupérer l'ID du client
$clientId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$clientId) {
    $_SESSION['error'] = "ID de client non spécifié.";
    header('Location: /Pharmacie_S/Views/clients/index_clients.php');
    exit();
}

$client = $clientModel->getClientById($clientId);

if (!$client) {
    $_SESSION['error'] = "Client non trouvé.";
    header('Location: /Pharmacie_S/Views/clients/index_clients.php');
    exit();
}

// Configuration de la pagination et du tri
$itemsPerPage = 30;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$sortDirection = isset($_GET['direction']) ? $_GET['direction'] : 'desc';

// Récupérer les paramètres de filtrage
$includeDeleted = isset($_GET['include_deleted']) ? $_GET['include_deleted'] == '1' : false;
$dateDebut = !empty($_GET['date_debut']) ? $_GET['date_debut'] : null;
$dateFin = !empty($_GET['date_fin']) ? $_GET['date_fin'] : null;

// Vérifier si le bouton de réinitialisation a été cliqué
if (isset($_GET['reset_filters'])) {
    $dateDebut = null;
    $dateFin = null;
    $includeDeleted = false;
}
// Récupération des ventes
try {
    $ventes = $venteModel->getVentesClientPaginesEtTries(
        $clientId,
        $offset,
        $itemsPerPage,
        $sortColumn,
        $sortDirection,
        $dateDebut,
        $dateFin,
        $includeDeleted
    );

    $totalVentes = $venteModel->getTotalVentesForClientFiltered(
        $clientId,
        $dateDebut,
        $dateFin,
        $includeDeleted
    );
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des ventes.";
    $ventes = [];
    $totalVentes = 0;
}

$totalPages = ceil($totalVentes / $itemsPerPage);
?>

<div class="full-width-container">
    <h1 class="centered-content">Ventes du client : <?php echo htmlspecialchars($client['nom'] . ' ' . $client['prenom']); ?></h1>

    <section id="ventes-client">
        <div class="centered-content">
            <form method="GET" action="" class="form-row">
                <input type="hidden" name="id" value="<?php echo $clientId; ?>">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortColumn); ?>">
                <input type="hidden" name="direction" value="<?php echo htmlspecialchars($sortDirection); ?>">

                <div class="form-group">
                    <label for="date_debut">Date de début:</label>
                    <input type="date" id="date_debut" name="date_debut" value="<?php echo $dateDebut; ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="date_fin">Date de fin:</label>
                    <input type="date" id="date_fin" name="date_fin" value="<?php echo $dateFin; ?>" class="form-control">
                </div>

                <?php if ($user['role_id'] == 1): ?>
                    <div class="form-group">
                        <label for="include_deleted">Afficher :</label>
                        <select name="include_deleted" id="include_deleted" class="form-control">
                            <option value="0" <?php echo !$includeDeleted ? 'selected' : ''; ?>>Ventes actives</option>
                            <option value="1" <?php echo $includeDeleted ? 'selected' : ''; ?>>Toutes les ventes</option>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <button type="submit" name="reset_filters" value="1" class="btn btn-secondary">Réinitialiser</button>
                </div>
            </form>
        </div>

        <table class="cheques-table" id="ventes-table">
            <thead>
                <tr>
                    <th data-sort="user_id" class="<?php echo $sortColumn === 'user_id' ? 'th-sort-' . $sortDirection : ''; ?>">
                        User ID
                    </th>
                    <th data-sort="date" class="<?php echo $sortColumn === 'date' ? 'th-sort-' . $sortDirection : ''; ?>">
                        Date
                    </th>
                    <th data-sort="montant" class="<?php echo $sortColumn === 'montant' ? 'th-sort-' . $sortDirection : ''; ?>">
                        Montant
                    </th>
                    <th data-sort="is_deleted" class="<?php echo $sortColumn === 'is_deleted' ? 'th-sort-' . $sortDirection : ''; ?>">
                        Supprimé
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ventes)): ?>
                    <tr>
                        <td colspan="4">Aucune vente trouvée.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ventes as $vente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vente['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($vente['date']); ?></td>
                            <td><?php echo htmlspecialchars($vente['montant']); ?> €</td>
                            <td><?php echo $vente['is_deleted'] ? 'Oui' : 'Non'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=1&id=<?php echo $clientId; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>&date_debut=<?php echo $dateDebut; ?>&date_fin=<?php echo $dateFin; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?>"
                        class="back-link" title="Première page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?php echo ($currentPage - 1); ?>&id=<?php echo $clientId; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>&date_debut=<?php echo $dateDebut; ?>&date_fin=<?php echo $dateFin; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?>"
                        class="back-link" title="Page précédente">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);

                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&id=<?php echo $clientId; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>&date_debut=<?php echo $dateDebut; ?>&date_fin=<?php echo $dateFin; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?>"
                        class="back-link <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo ($currentPage + 1); ?>&id=<?php echo $clientId; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>&date_debut=<?php echo $dateDebut; ?>&date_fin=<?php echo $dateFin; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?>"
                        class="back-link" title="Page suivante">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?php echo $totalPages; ?>&id=<?php echo $clientId; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>&date_debut=<?php echo $dateDebut; ?>&date_fin=<?php echo $dateFin; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?>"
                        class="back-link" title="Dernière page">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="centered-content mt-4">
        <a href="/Pharmacie_S/Views/clients/show_client.php?id=<?php echo $clientId; ?>" class="back-link-gray">
            <i class="fas fa-arrow-left"></i> Retour aux détails du client
        </a>
    </div>
</div>

<script src="/Pharmacie_S/js/sort_ventes.js"></script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>