<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/produits/index_produits.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/Parametre.php';

$parametre = new Parametre();
$tva = $parametre->getParametre('TVA');

$pageTitle = "Liste des Produits";
$additionalHeadContent = <<<EOT
    <link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
    <link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
    <script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
    <script src="/Pharmacie_S/js/search_produit.js"></script>
    <style>
        .centered-content {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-container {
            display: inline-block;
            width: 50%;
            margin: 10px auto;
        }
        #search-product {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .action-buttons {
            margin-bottom: 10px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin: 20px 0;
        }
        .pagination .back-link.active {
            background-color: #007bff;
            color: white;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .user-table th, .user-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .user-table th {
        background-color:  #333333;  /* Gris foncé*/
        color: white; /* Texte blanc */
        cursor: pointer;
        position: relative;
        padding-right: 20px;
        font-weight: bold; /* Pour un meilleur contraste */
    }

    .user-table th[data-column]::after {
        content: '▼';
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8em;
        opacity: 0.7;
        color: white; /* Flèche en blanc */
    }

    .user-table th[data-column].asc::after {
        content: '▲';
        opacity: 1;
        color: white;
    }

    .user-table th[data-column].desc::after {
        content: '▼';
        opacity: 1;
        color: white;
    }

    .user-table th[data-column]:hover {
        background-color:  #4a4a4a;  /* Gris un peu plus clair au survol */
    }

        .user-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .user-table tr:hover {
            background-color: #e3f2fd; 
            transition: background-color 0.2s ease;
        }
        .deleted-product {
            background-color: #ffe6e6 !important;
        }
        .deleted-product:hover {
        background-color: #ffcccc !important;  /* Rouge plus clair au survol */
    }
    </style>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<script>
    document.body.className = 'index-produits-page';
    window.isAdmin = <?php echo json_encode($role == 1); ?>;
    window.tva = <?php echo json_encode($tva); ?>;
</script>

<div class="full-width-container">
    <h1 class="centered-content">Liste des Produits</h1>

    <?php if (!empty($error)): ?>
        <p class="centered-content" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p class="centered-content" style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <div class="centered-content">
        <?php if (isset($role) && $role == 1): ?>
            <div class="action-buttons">
                <a href="/Pharmacie_S/Views/produits/create_produit.php" class="back-link">
                    <i class="fas fa-plus"></i> Ajouter un Produit
                </a>
            </div>
        <?php endif; ?>

        <div class="search-container">
            <input type="text"
                id="search-product"
                class="form-control"
                placeholder="Rechercher un produit (minimum 2 caractères)..."
                autocomplete="off" />
        </div>
    </div>

    <table class="user-table" id="products-table">
        <thead>
            <tr>
                <th data-column="produit_id">ID</th>
                <th data-column="nom">Nom</th>
                <th data-column="prix_vente_ht">Prix Vente HT</th>
                <th data-column="prix_vente_ttc">Prix Vente TTC</th>
                <th data-column="prescription">Prescription</th>
                <th data-column="taux_remboursement">Taux Remboursement</th>
                <th data-column="alerte">Alerte</th>
                <th data-column="declencher_alerte">Déclencher Alerte</th>
                <?php if ($role == 1): ?>
                    <th data-column="supprime">Supprimé</th>
                <?php endif; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($produits) && is_array($produits)): ?>
                <?php foreach ($produits as $produit): ?>
                    <?php $prix_ventettc = isset($produit['prix_vente_ttc']) ? $produit['prix_vente_ttc'] : ($produit['prix_vente_ht'] * (1 + ($tva / 100))); ?>
                    <tr data-id="<?php echo htmlspecialchars($produit['produit_id']); ?>"
                        <?php echo ($role == 1 && $produit['is_deleted']) ? 'class="deleted-product"' : ''; ?>>
                        <td><?php echo htmlspecialchars($produit['produit_id']); ?></td>
                        <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                        <td><?php echo number_format($produit['prix_vente_ht'], 2, ',', ' '); ?> €</td>
                        <td><?php echo number_format($prix_ventettc, 2, ',', ' '); ?> €</td>
                        <td><?php echo htmlspecialchars($produit['prescription']); ?></td>
                        <td><?php echo $produit['taux_remboursement'] ? htmlspecialchars($produit['taux_remboursement']) . '%' : '-'; ?></td>
                        <td><?php echo htmlspecialchars($produit['alerte']); ?></td>
                        <td><?php echo htmlspecialchars($produit['declencher_alerte']); ?></td>
                        <?php if ($role == 1): ?>
                            <td><?php echo $produit['is_deleted'] ? 'Oui' : 'Non'; ?></td>
                        <?php endif; ?>
                        <td>
                            <a href="/Pharmacie_S/Views/produits/show_produit.php?id=<?php echo htmlspecialchars($produit['produit_id']); ?>"
                                title="Voir"><i class="fas fa-eye"></i></a>
                            <?php if ($role == 1): ?>
                                <a href="/Pharmacie_S/Views/produits/edit_produit.php?id=<?php echo htmlspecialchars($produit['produit_id']); ?>"
                                    title="Modifier"><i class="fas fa-edit"></i></a>
                                <?php if (!$produit['is_deleted']): ?>
                                    <a href="/Pharmacie_S/Views/produits/delete_produit.php?id=<?php echo htmlspecialchars($produit['produit_id']); ?>"
                                        title="Supprimer"><i class="fas fa-trash"></i></a>
                                <?php else: ?>
                                    <a href="/Pharmacie_S/PHP/produits/restore_produit.php?id=<?php echo htmlspecialchars($produit['produit_id']); ?>"
                                        title="Restaurer"><i class="fas fa-undo"></i></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $role == 1 ? '10' : '9'; ?>">Aucun produit trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container centered-content">
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=1&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>"
                        class="back-link" title="Première page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?php echo ($currentPage - 1); ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>"
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
                    <a href="?page=<?php echo ($currentPage + 1); ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>"
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

    <div class="centered-content">
        <a href="/Pharmacie_S/index.php" class="back-link-gray">
            <i class="fas fa-arrow-left"></i> Retour à l'accueil
        </a>
    </div>
</div>

<script>
    $(document).ready(function() {
        initializeProductSearch();

        // Ajouter la classe de tri active
        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('sort');
        const currentDirection = urlParams.get('direction');

        if (currentSort) {
            const th = $(`th[data-column="${currentSort}"]`);
            th.addClass(currentDirection === 'desc' ? 'desc' : 'asc');
        }
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>