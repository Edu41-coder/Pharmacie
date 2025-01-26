<?php
$pageTitle = "Inventaire des Produits";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<link href="/Pharmacie_S/css/select2.min.css" rel="stylesheet" />
<script src="/Pharmacie_S/js/select2.min.js"></script>
<script src="/Pharmacie_S/js/axios.min.js"></script>
<script src="/Pharmacie_S/js/sort_inventaire.js"></script>
<script src="/Pharmacie_S/js/search_inventaire.js"></script>
<script>
    $(document).ready(function() {
        $('#search-inventory').select2({
            placeholder: 'Rechercher un produit...',
            allowClear: true,
            width: '100%'
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-produits-page";
        initializeInventorySearch();
    });
</script>
<style>
    /* Style pour les en-têtes triables */
    .user-table th[data-column] {
        background-color: #333333;
        color: white;
        cursor: pointer;
        position: relative;
        padding-right: 25px;  /* Espace pour l'icône */
    }

    /* Survol des en-têtes */
    .user-table th[data-column]:hover {
        background-color: #4a4a4a;
    }

    /* Style pour les icônes de tri */
    .user-table th[data-column] .fas {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
    }

    /* Styles pour les états de tri */
    .th-sort-asc .fas {
        transform: translateY(-50%) rotate(180deg);
    }

    .th-sort-desc .fas {
        transform: translateY(-50%) rotate(0deg);
    }

    /* Styles de priorité maximale pour les lignes */
    .index-produits-container .user-table tbody tr:nth-child(even) { 
        background-color: #f9f9f9 !important; 
    }

    .index-produits-container .user-table tbody tr:hover { 
        background-color: #e3f2fd !important; 
        transition: background-color 0.2s ease !important;
    }

    .stock-warning {
        color: #ff0000 !important; /* Rouge */
        font-weight: bold;
        background-color: #ffe6e6; /* Fond rouge clair */
    }
</style>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/inventaire/index_inventaire.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<div class="index-produits-container">
    <div class="container">
        <h1>Inventaire des Produits</h1>

        <?php if (!empty($message)): ?>
            <p style="color: <?php echo strpos($message, 'Erreur') !== false ? 'red' : 'green'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($lastChangeMessage)): ?>
            <p style="color: blue;">
                <?php echo $lastChangeMessage; ?>
            </p>
        <?php endif; ?>

        <a href="/Pharmacie_S/Views/inventaire/create_inventaire.php" class="back-link">
            <i class="fas fa-plus"></i> Ajouter un Produit à l'Inventaire
        </a>

        <div class="search-container">
            <select id="search-inventory" style="width: 100%;">
                <option></option>
                <?php foreach ($allInventaire as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['produit_id']); ?>">
                        <?php echo htmlspecialchars($item['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <table class="user-table" id="inventory-table">
            <thead>
                <tr>
                    <th data-column="produit_id">ID Produit</th>
                    <th data-column="nom">Nom du Produit</th>
                    <th data-column="stock">Stock</th>
                    <th data-column="alerte">Alerte</th>
                    <th data-column="declencher_alerte">Déclencher Alerte</th>
                    <th data-column="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($inventaire) && is_array($inventaire)): ?>
                    <?php foreach ($inventaire as $item): ?>
                        <tr data-id="<?php echo htmlspecialchars($item['produit_id']); ?>">
                            <td><?php echo htmlspecialchars($item['produit_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['nom']); ?></td>
                            <td><?php echo htmlspecialchars($item['stock']); ?></td>
                            <td><?php echo htmlspecialchars($item['alerte']); ?></td>
                            <td><?php echo htmlspecialchars($item['declencher_alerte']); ?></td>
                            <td>
                                <a href="/Pharmacie_S/Views/inventaire/edit_inventaire.php?id=<?php echo htmlspecialchars($item['produit_id']); ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/Pharmacie_S/Views/inventaire/delete_inventaire.php?id=<?php echo htmlspecialchars($item['produit_id']); ?>">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Aucun produit trouvé dans l'inventaire.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=1&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>"
                            class="back-link" title="Première page">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?page=<?php echo $currentPage - 1; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>"
                            class="back-link" title="Page précédente">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);

                    for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>"
                            class="back-link <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>"
                            class="back-link" title="Page suivante">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?page=<?php echo $totalPages; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>"
                            class="back-link" title="Dernière page">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="centered-link">
            <a href="/Pharmacie_S/index.php" class="back-link-gray">Retour à l'accueil</a>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ajoute une classe pour les stocks bas
        const stockCells = document.querySelectorAll('td:nth-child(3)');
        stockCells.forEach(cell => {
            const stock = parseInt(cell.textContent);
            const alerteCell = cell.parentElement.querySelector('td:nth-child(4)');
            const alerte = parseInt(alerteCell.textContent);

            if (stock <= alerte) {
                cell.classList.add('stock-warning');
            }
        });
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>