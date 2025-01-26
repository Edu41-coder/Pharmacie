<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/a_commander/index_a_commander.php';

$isTableEmpty = isset($isTableEmpty) ? $isTableEmpty : true;
$collections = isset($collections) ? $collections : [];
$lastLoadedCollection = isset($lastLoadedCollection) ? $lastLoadedCollection : '';
$a_commander = isset($a_commander) ? $a_commander : [];
$error = isset($error) ? $error : '';
$success = isset($success) ? $success : '';
$warning = isset($warning) ? $warning : '';
$currentCount = isset($currentCount) ? $currentCount : 0;
$mongoCount = isset($mongoCount) ? $mongoCount : 0;
$hasBeenModifiedSinceLastMongoLoad = isset($hasBeenModifiedSinceLastMongoLoad) ? $hasBeenModifiedSinceLastMongoLoad : false;
$lastMongoSave = isset($lastMongoSave) ? $lastMongoSave : null;
$lastMongoLoad = isset($lastMongoLoad) ? $lastMongoLoad : null;
$currentPage = isset($currentPage) ? $currentPage : 1;
$totalPages = isset($totalPages) ? $totalPages : 1;
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'produit_id';
$sortDirection = isset($_GET['direction']) ? $_GET['direction'] : 'asc';

$pageTitle = "Produits à Commander";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<style>
    @media print {
        body * { visibility: hidden; }
        #content-wrapper, #content-wrapper * { visibility: visible; }
        #content-wrapper {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .action-buttons, .back-link, .centered-link, .pagination {
            display: none !important;
        }
    }

    /* Style pour les en-têtes triables */
    .user-table th {
        background-color: #333333;
        color: white;
        padding-right: 25px;  /* Espace pour l'icône */
    }

    /* Survol des en-têtes */
    .user-table th[data-column]:hover {
        background-color: #4a4a4a;
    }

    .user-table th[data-column] {
        cursor: pointer;
        position: relative;
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
    .index-a-commander-container .user-table tbody tr:nth-child(even) { 
        background-color: #f9f9f9 !important; 
    }

    .index-a-commander-container .user-table tbody tr:hover { 
        background-color: #e3f2fd !important; 
        transition: background-color 0.2s ease !important;
    }

    .action-buttons {
        margin: 20px 0;
    }

    .action-buttons button,
    .action-buttons select {
        margin: 0 5px;
        padding: 5px 10px;
    }
</style>
<script>
    const isTableEmpty =  Boolean({$isTableEmpty});

    function confirmLoad() {
        return confirm("Le chargement supprimera les données non enregistrées. Voulez-vous continuer avec le chargement ?");
    }

    function confirmSave() {
        return confirm("Voulez-vous vraiment enregistrer ces produits dans MongoDB ?");
    }

    function confirmUpdate() {
        return confirm("Voulez-vous vraiment mettre à jour cette collection dans MongoDB ?");
    }

    function toggleButtons() {
        var saveButton = document.getElementById('saveToMongoButton');
        var loadButton = document.getElementById('loadFromMongoButton');
        var updateButton = document.getElementById('updateMongoButton');
        var collectionSelect = document.getElementById('collectionSelect');
        saveButton.disabled = isTableEmpty;
        loadButton.disabled = collectionSelect.options.length === 0;
        updateButton.disabled = collectionSelect.value === '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleButtons();
        document.getElementById('collectionSelect').onchange = toggleButtons;
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<div id="page-wrapper" style="background-image: url('/Pharmacie_S/images/pharm6.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; min-height: 100vh; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: -1;">
</div>
<div id="content-wrapper" style="position: relative; z-index: 1; padding: 20px;">
    <div class="index-a-commander-container">
        <div class="container">
            <h1>Produits à Commander</h1>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <?php if (!empty($warning)): ?>
                <p style="color: orange;"><?php echo htmlspecialchars($warning); ?></p>
            <?php endif; ?>
            <a href="/Pharmacie_S/Views/a_commander/create_a_commander.php" class="back-link">
                <i class="fas fa-plus"></i> Ajouter un Produit à Commander
            </a>

            <div class="action-buttons">
                <button onclick="window.print();">Imprimer la Liste</button>
                <form method="post" style="display:inline;" onsubmit="return confirmSave();">
                    <button type="submit" name="save_to_mongo" id="saveToMongoButton">Enregistrer nouvelle</button>
                </form>
                <form method="post" style="display:inline;" onsubmit="return confirmLoad();">
                    <select name="selected_collection" id="collectionSelect">
                        <?php foreach ($collections as $collection): ?>
                            <option value="<?php echo htmlspecialchars($collection); ?>"
                                <?php echo ($collection === $lastLoadedCollection) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($collection); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="load_from_mongo" id="loadFromMongoButton">Charger depuis MongoDB</button>
                    <button type="submit" name="update_mongo" id="updateMongoButton" onclick="return confirmUpdate();">
                        Mettre à jour MongoDB
                    </button>
                </form>
            </div>

            <?php if (empty($a_commander)): ?>
                <p>Aucun produit à commander.</p>
            <?php else: ?>
                <table class="user-table" id="a-commander-table">
                    <thead>
                        <tr>
                            <th data-column="produit_id">ID Produit</th>
                            <th data-column="nom">Nom du Produit</th>
                            <th data-column="stock">Stock</th>
                            <th data-column="alerte">Alerte</th>
                            <th data-column="quantite">Quantité</th>
                            <th data-column="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($a_commander as $item): ?>
                            <tr data-id="<?php echo htmlspecialchars($item['produit_id']); ?>">
                                <td><?php echo htmlspecialchars($item['produit_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['nom']); ?></td>
                                <td><?php echo htmlspecialchars($item['stock'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['alerte'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['quantite']); ?></td>
                                <td>
                                    <a href="/Pharmacie_S/Views/a_commander/edit_a_commander.php?id=<?php echo htmlspecialchars($item['produit_id']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/Pharmacie_S/Views/a_commander/delete_a_commander.php?produit_id=<?php echo htmlspecialchars($item['produit_id']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
            <?php endif; ?>

            <div class="centered-link">
                <?php if (isset($currentCount) && isset($mongoCount) && isset($isTableEmpty) && isset($hasBeenModifiedSinceLastMongoLoad)): ?>
                    <div style="background-color: #f0f0f0; padding: 10px; margin-bottom: 10px;">
                        <p>Current Count: <?php echo $currentCount; ?></p>
                        <p>Mongo Count: <?php echo $mongoCount; ?></p>
                        <p>Is Table Empty: <?php echo $isTableEmpty ? 'Yes' : 'No'; ?></p>
                        <p>Has Been Modified Since Last Mongo Load: <?php echo $hasBeenModifiedSinceLastMongoLoad ? 'Yes' : 'No'; ?></p>
                        <p>Last Mongo Save: <?php echo $lastMongoSave ? date('Y-m-d H:i:s', $lastMongoSave) : 'N/A'; ?></p>
                        <p>Last Mongo Load: <?php echo $lastMongoLoad ? $lastMongoLoad : 'N/A'; ?></p>
                    </div>
                <?php endif; ?>
                <a href="/Pharmacie_S/index.php" class="back-link-gray">Retour à l'accueil</a>
            </div>
        </div>
    </div>
</div>

<script src="/Pharmacie_S/js/sort_a_commander.js"></script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>