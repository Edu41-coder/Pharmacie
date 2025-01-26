<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
$chequeObj = new Cheque();
$clientObj = new Client();

// Traitement de la mise à jour des chèques impayés
if (isset($_POST['update_cheques_impayes'])) {
    if ($clientObj->updateChequesImpayes()) {
        $_SESSION['success'] = "Mise à jour des chèques impayés effectuée avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour des chèques impayés.";
    }
    // Rediriger pour éviter la soumission multiple du formulaire
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
$pageTitle = "Gestion des Chèques";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<script type="module" src="/Pharmacie_S/js/search_cheques.js"></script>
<script type="module" src="/Pharmacie_S/js/sort_cheques.js"></script>
<style>
.index-cheques-container {
        width: 95%;
        max-width: 1800px; 
        margin: 0 auto;
        padding: 20px;
    }

    .container {
        width: 100%;
        max-width: none; /* Supprime la largeur maximale par défaut de Bootstrap */
        padding: 0;
    }
    .cheques-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .cheques-table th, .cheques-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    .cheques-table th { background-color: #f2f2f2; cursor: pointer; }
    .cheques-table tr:nth-child(even) { background-color: #f9f9f9; }
    .cheques-table tr:hover { background-color: #f5f5f5; }
    .form-row { display: flex; flex-wrap: wrap; margin-bottom: 10px; }
    .form-group { margin-right: 15px; }
    th.sort-asc::after { content: " ▲"; }
    th.sort-desc::after { content: " ▼"; }
    .search-container { margin-bottom: 20px; }
    #search-criteria, #search-input { padding: 5px; margin-right: 10px; }
    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
        padding: 8px 16px;
        margin-bottom: 15px;
        cursor: pointer;
        border-radius: 4px;
    }
    .btn-warning:hover {
        background-color: #e0a800;
        border-color: #d39e00;
    }
    @media print {
        .pagination, .search-container, .form-row, .action-buttons {
            display: none !important;
        }
    }
        .form-row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}

.form-group {
    margin-right: 1rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 0.5rem;
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.mr-3 {
    margin-right: 1rem !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}
    .justify-content-center {
    justify-content: center !important;
}
.index-cheques-container .cheques-table tbody tr:hover { 
        background-color: #e3f2fd !important; 
        transition: background-color 0.2s ease !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "cheques-page";
    });
</script>
EOT;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

// Configuration de la pagination
$itemsPerPage = 13;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Traitement des filtres
$etat = $_GET['etat'] ?? null;
$dateDebut = $_GET['date_debut'] ?? null;
$dateFin = $_GET['date_fin'] ?? null;
$includeDeletedVentes = isset($_GET['include_deleted']) && $_GET['include_deleted'] == '1';

// Get sorting parameters from URL
$sortColumn = $_GET['sortColumn'] ?? 'cheque_id';
$sortOrder = $_GET['sortOrder'] ?? 'DESC';

// Récupération des chèques filtrés avec pagination et tri
$cheques = $chequeObj->getChequesPagines($offset, $itemsPerPage, $etat, $dateDebut, $dateFin, $includeDeletedVentes, $sortColumn, $sortOrder);
$totalCheques = $chequeObj->getTotalCheques($etat, $dateDebut, $dateFin, $includeDeletedVentes);
$totalPages = ceil($totalCheques / $itemsPerPage);

$filtersActive = !empty($etat) || !empty($dateDebut) || !empty($dateFin) || $includeDeletedVentes;
?>
<script>
    window.currentCheques = <?php echo json_encode($cheques); ?>;
</script>

<div class="index-cheques-container">
    <div class="container">
        <h1>Gestion des Chèques</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <p style="color: green; padding: 10px; margin: 10px 0;">
                <?php echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']); ?>
            </p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red; padding: 10px; margin: 10px 0;">
                <?php echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="" class="mb-3">
            <button type="submit" name="update_cheques_impayes" class="btn btn-warning">
                <i class="fas fa-sync-alt"></i> Mettre à jour les chèques impayés
            </button>
        </form>

        <form method="GET" action="" class="mb-4">
            <!-- Première ligne des filtres -->
            <div class="form-row mb-3">
                <div class="form-group mr-3">
                    <label for="include_deleted">Inclure ventes supprimées</label>
                    <input type="checkbox" name="include_deleted" id="include_deleted" value="1"
                        <?php echo $includeDeletedVentes ? 'checked' : ''; ?>>
                </div>
                <div class="form-group mr-3">
                    <label for="etat">État du chèque</label>
                    <select name="etat" id="etat" class="form-control">
                        <option value="">Tous</option>
                        <?php
                        $etats = ['en_attente' => 'En attente', 'valide' => 'Validé', 'refuse' => 'Refusé'];
                        foreach ($etats as $value => $label) {
                            echo "<option value=\"$value\"" . ($etat == $value ? ' selected' : '') . ">$label</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label for="date_debut">Date de début</label>
                    <input type="date" name="date_debut" id="date_debut" class="form-control"
                        value="<?php echo $dateDebut; ?>">
                </div>
                <div class="form-group mr-3">
                    <label for="date_fin">Date de fin</label>
                    <input type="date" name="date_fin" id="date_fin" class="form-control"
                        value="<?php echo $dateFin; ?>">
                </div>
            </div>

            <!-- Deuxième ligne pour le bouton réinitialiser -->
            <div class="form-row justify-content-center mb-3">
                <div class="form-group">
                    <button type="button" class="btn btn-secondary" id="reset-filters">Réinitialiser les filtres</button>
                </div>
            </div>

            <input type="hidden" name="sortColumn" value="<?php echo htmlspecialchars($sortColumn); ?>">
            <input type="hidden" name="sortOrder" value="<?php echo htmlspecialchars($sortOrder); ?>">
        </form>

        <div class="search-container">
            <select id="search-criteria" style="width: 200px;">
                <option value="all">Tous les critères</option>
                <option value="numero_cheque">Numéro de chèque</option>
                <option value="client">Client</option>
                <option value="vente_id">ID de vente</option>
            </select>
            <input type="text" id="search-input" placeholder="Rechercher..." style="width: 300px;">
        </div>

        <table class="cheques-table" id="cheques-table">
            <thead>
                <tr>
                    <th data-sort="cheque_id" class="<?php echo $sortColumn === 'cheque_id' ? 'sort-' . $sortOrder : ''; ?>">ID Chèque</th>
                    <th data-sort="numero_cheque" class="<?php echo $sortColumn === 'numero_cheque' ? 'sort-' . $sortOrder : ''; ?>">Numéro Chèque</th>
                    <th data-sort="client_nom" class="<?php echo $sortColumn === 'client_nom' ? 'sort-' . $sortOrder : ''; ?>">Client</th>
                    <th data-sort="montant" class="<?php echo $sortColumn === 'montant' ? 'sort-' . $sortOrder : ''; ?>">Montant</th>
                    <th data-sort="etat" class="<?php echo $sortColumn === 'etat' ? 'sort-' . $sortOrder : ''; ?>">État</th>
                    <th data-sort="vente_id" class="<?php echo $sortColumn === 'vente_id' ? 'sort-' . $sortOrder : ''; ?>">Vente ID</th>
                    <th data-sort="vente_is_deleted" class="<?php echo $sortColumn === 'vente_is_deleted' ? 'sort-' . $sortOrder : ''; ?>">Vente supprimée</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cheques)): ?>
                    <tr>
                        <td colspan="8">Pas de chèques qui accomplissent ce critère.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cheques as $cheque): ?>
                        <tr>
                            <td><?php echo $cheque['cheque_id']; ?></td>
                            <td><?php echo $cheque['numero_cheque']; ?></td>
                            <td><?php echo $cheque['client_nom'] . ' ' . $cheque['client_prenom']; ?></td>
                            <td><?php echo $cheque['montant']; ?> €</td>
                            <td><?php echo $cheque['etat']; ?></td>
                            <td><?php echo $cheque['vente_id'] ?? 'N/A'; ?></td>
                            <td><?php echo $cheque['vente_is_deleted'] ? 'Oui' : 'Non'; ?></td>
                            <td>
                                <a href='/Pharmacie_S/Views/comptabilité/edit_cheque.php?id=<?php echo $cheque['cheque_id']; ?>'
                                    class='btn btn-sm btn-primary'>
                                    <i class='fas fa-edit'></i> Modifier
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=1&sortColumn=<?php echo $sortColumn; ?>&sortOrder=<?php echo $sortOrder; ?><?php
                                                                                                                echo "&etat=$etat&date_debut=$dateDebut&date_fin=$dateFin" .
                                                                                                                    ($includeDeletedVentes ? '&include_deleted=1' : ''); ?>"
                        class="back-link" title="Première page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?php echo ($currentPage - 1); ?>&sortColumn=<?php echo $sortColumn; ?>&sortOrder=<?php echo $sortOrder; ?><?php
                                                                                                                                                echo "&etat=$etat&date_debut=$dateDebut&date_fin=$dateFin" .
                                                                                                                                                    ($includeDeletedVentes ? '&include_deleted=1' : ''); ?>"
                        class="back-link" title="Page précédente">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&sortColumn=<?php echo $sortColumn; ?>&sortOrder=<?php echo $sortOrder; ?><?php
                                                                                                                                echo "&etat=$etat&date_debut=$dateDebut&date_fin=$dateFin" .
                                                                                                                                    ($includeDeletedVentes ? '&include_deleted=1' : ''); ?>"
                        class="back-link <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo ($currentPage + 1); ?>&sortColumn=<?php echo $sortColumn; ?>&sortOrder=<?php echo $sortOrder; ?><?php
                                                                                                                                                echo "&etat=$etat&date_debut=$dateDebut&date_fin=$dateFin" .
                                                                                                                                                    ($includeDeletedVentes ? '&include_deleted=1' : ''); ?>"
                        class="back-link" title="Page suivante">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?php echo $totalPages; ?>&sortColumn=<?php echo $sortColumn; ?>&sortOrder=<?php echo $sortOrder; ?><?php
                                                                                                                                        echo "&etat=$etat&date_debut=$dateDebut&date_fin=$dateFin" .
                                                                                                                                            ($includeDeletedVentes ? '&include_deleted=1' : ''); ?>"
                        class="back-link" title="Dernière page">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="/Pharmacie_S/Views/comptabilité/index_comptabilité.php" class="back-link-gray">
                <i class="fas fa-arrow-left"></i> Retour à l'index comptabilité
            </a>
        </div>
    </div>
</div>

<script type="module">
    let currentCheques = <?php echo json_encode($cheques); ?>;
    let filtersActive = <?php echo json_encode($filtersActive); ?>;

    import {
        updateTableWithPHPData,
        performSearch
    } from '/Pharmacie_S/js/chequeUtils.js';

    // Mise à jour de la fonction pour inclure les paramètres de tri
    function updateURLWithSort(column, order) {
        const url = new URL(window.location.href);
        url.searchParams.set('sortColumn', column);
        url.searchParams.set('sortOrder', order);
        window.location.href = url.toString();
    }

    // Gestionnaire de tri pour les en-têtes de colonnes
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const column = th.dataset.sort;
            const currentOrder = th.classList.contains('sort-asc') ? 'desc' : 'asc';
            updateURLWithSort(column, currentOrder);
        });
    });

    // Fonction pour mettre à jour le tableau avec les suggestions
    window.updateTableWithPHPData = function(suggestions) {
        const tbody = document.querySelector("#cheques-table tbody");
        if (!tbody) {
            console.error("Tbody non trouvé");
            return;
        }
        tbody.innerHTML = suggestions.length ? generateTableRowsJS(suggestions) :
            '<tr><td colspan="8">Pas de chèques qui accomplissent ce critère.</td></tr>';
    };

    // Réinitialiser les filtres
    document.getElementById("reset-filters").addEventListener("click", function() {
        document.getElementById("etat").value = "";
        document.getElementById("include_deleted").checked = false;
        document.getElementById("date_debut").value = "";
        document.getElementById("date_fin").value = "";
        window.location.href = window.location.pathname;
    });

    // Événements pour la recherche et les filtres
    document.getElementById("search-input").addEventListener("input", function() {
        if (this.value.length >= 2) {
            performSearch(this, document.getElementById("search-criteria"))
                .then(data => updateTableWithPHPData(data))
                .catch(error => {
                    console.error("Erreur lors de la recherche:", error);
                    updateTableWithPHPData([]);
                });
        } else {
            updateTableWithPHPData(currentCheques);
        }
    });

    // Événements pour les filtres avec maintien des paramètres de tri
    const submitFormWithSort = () => {
        const form = document.querySelector('form.mb-4');
        if (form) form.submit();
    };

    document.getElementById("etat").addEventListener("change", submitFormWithSort);
    document.getElementById("include_deleted").addEventListener("change", submitFormWithSort);
    document.getElementById("date_debut").addEventListener("change", function() {
        if (document.getElementById("date_fin").value) submitFormWithSort();
    });
    document.getElementById("date_fin").addEventListener("change", function() {
        if (document.getElementById("date_debut").value) submitFormWithSort();
    });
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>