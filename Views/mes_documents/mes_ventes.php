<?php
$pageTitle = "Mes ventes";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/mes_ventes.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<link href="/Pharmacie_S/css/select2.min.css" rel="stylesheet" />
<script src="/Pharmacie_S/js/select2.min.js"></script>
<style>
    /* Styles de base */
    .filter-row {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .date-input {
        width: 150px;
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    .filter-select {
        min-width: 150px;
        padding: 5px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }

    /* Style pour les en-têtes triables */
    .user-table th {
        background-color: #333333;
        color: white;
        padding: 8px;
        text-align: center;
        padding-right: 25px;
    }

    /* Survol des en-têtes */
    .user-table th[data-sort]:hover {
        background-color: #4a4a4a;
    }

    .user-table th[data-sort] {
        cursor: pointer;
        position: relative;
    }

    /* Style pour les icônes de tri */
    .user-table th[data-sort]::after {
        font-family: "Font Awesome 5 Free";
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 900;
    }

    /* Styles pour les états de tri */
    .th-sort-asc::after { 
        content: "\\f0de";
        transform: translateY(-50%) rotate(180deg);
    }

    .th-sort-desc::after { 
        content: "\\f0dd";
        transform: translateY(-50%) rotate(0deg);
    }

    /* Styles de priorité maximale pour les lignes */
    .full-width-container .user-table tbody tr:nth-child(even) { 
        background-color: #f9f9f9 !important; 
    }

    .full-width-container .user-table tbody tr:hover { 
        background-color: #e3f2fd !important; 
        transition: background-color 0.2s ease !important;
    }

    /* Style pour les lignes supprimées */
    .deleted-row {
        background-color: #ffebee !important;
    }

    .deleted-row:hover {
        background-color: #ffcdd2 !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-produits-page";
    });
</script>
EOT;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

$auth = new Authentification();
$user = $auth->getCurrentUser();
$venteModel = new Vente();
$userModel = new User();

// Paramètres de pagination et de tri
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Nombre d'éléments par page
$offset = ($page - 1) * $limit;
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$sortDirection = isset($_GET['direction']) ? $_GET['direction'] : 'desc';

// Récupérer les paramètres de filtrage
$includeDeleted = isset($_GET['include_deleted']) ? $_GET['include_deleted'] == '1' : false;
$filteredUserId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$dateDebut = !empty($_GET['date_debut']) ? $_GET['date_debut'] : null;
$dateFin = !empty($_GET['date_fin']) ? $_GET['date_fin'] : null;

// Vérifier si le bouton de réinitialisation a été cliqué
$resetFilters = isset($_GET['reset_filters']);
// Réinitialiser tous les filtres si le bouton est cliqué
if ($resetFilters) {
    $includeDeleted = false;
    $filteredUserId = null;
    $dateDebut = null;
    $dateFin = null;
    $sortColumn = 'date';
    $sortDirection = 'desc';

    if ($user['role_id'] == 1) { // Admin
        $ventes = $venteModel->getAllVentesPaginesEtTries($offset, $limit, false, $sortColumn, $sortDirection);
        $totalVentes = $venteModel->getTotalVentes(false);
    } else { // Pharmacien ou employé
        $ventes = $venteModel->getVentesByUserIdPaginesEtTries($user['user_id'], $offset, $limit, false, $sortColumn, $sortDirection);
        $totalVentes = $venteModel->getTotalVentesByUserId($user['user_id'], false);
    }
} else {
    if ($user['role_id'] == 1) { // Admin
        if ($filteredUserId) {
            $ventes = $venteModel->getVentesByUserIdAndDateRangePaginesEtTries($filteredUserId, $dateDebut, $dateFin, $includeDeleted, $offset, $limit, $sortColumn, $sortDirection);
            $totalVentes = $venteModel->getTotalVentesByUserIdAndDateRange($filteredUserId, $dateDebut, $dateFin, $includeDeleted);
        } else {
            $ventes = $venteModel->getAllVentesByDateRangePaginesEtTries($dateDebut, $dateFin, $includeDeleted, $offset, $limit, $sortColumn, $sortDirection);
            $totalVentes = $venteModel->getTotalVentesByDateRange($dateDebut, $dateFin, $includeDeleted);
        }
    } else { // Pharmacien ou employé
        $ventes = $venteModel->getVentesByUserIdAndDateRangePaginesEtTries($user['user_id'], $dateDebut, $dateFin, false, $offset, $limit, $sortColumn, $sortDirection);
        $totalVentes = $venteModel->getTotalVentesByUserIdAndDateRange($user['user_id'], $dateDebut, $dateFin, false);
    }
}

// Calcul du nombre total de pages
$totalPages = ceil($totalVentes / $limit);
?>
<div class="full-width-container">
    <h1 class="centered-content">Mes Ventes</h1>

    <?php if (!empty($error)): ?>
        <p class="centered-content" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p class="centered-content" style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <section id="mes-ventes">
        <div class="centered-content">
            <form method="GET" action="">
                <!-- Première ligne avec les dates et l'affichage -->
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="date_debut">Date de début:</label>
                        <input type="date" id="date_debut" name="date_debut"
                            value="<?php echo $dateDebut; ?>"
                            class="date-input">
                    </div>

                    <div class="filter-group">
                        <label for="date_fin">Date de fin:</label>
                        <input type="date" id="date_fin" name="date_fin"
                            value="<?php echo $dateFin; ?>"
                            class="date-input">
                    </div>

                    <?php if ($user['role_id'] == 1): ?>
                        <div class="filter-group">
                            <label for="include_deleted">Afficher :</label>
                            <select name="include_deleted" id="include_deleted" class="filter-select">
                                <option value="0" <?php echo !$includeDeleted ? 'selected' : ''; ?>>Ventes actives</option>
                                <option value="1" <?php echo $includeDeleted ? 'selected' : ''; ?>>Toutes les ventes</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Deuxième ligne avec la sélection d'utilisateur et les boutons -->
                <div class="filter-row">
                    <?php if ($user['role_id'] == 1): ?>
                        <div class="filter-group">
                            <label for="user_id">Utilisateur :</label>
                            <select name="user_id" id="user_id" class="filter-select">
                                <option value="">Tous les utilisateurs</option>
                                <?php
                                $users = $userModel->getAllUsers();
                                foreach ($users as $u):
                                ?>
                                    <option value="<?php echo $u['user_id']; ?>"
                                        <?php echo $filteredUserId == $u['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortColumn); ?>">
                    <input type="hidden" name="direction" value="<?php echo htmlspecialchars($sortDirection); ?>">

                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                        <button type="button" class="btn btn-secondary"
                            onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'">
                            Réinitialiser les filtres
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php if ($totalPages > 0): ?>
            <table class="user-table" id="ventes-table">
                <thead>
                    <tr>
                        <th data-sort="vente_id">ID Vente</th>
                        <th data-sort="client_id">Client ID</th>
                        <th data-sort="user_id">User ID</th>
                        <th data-sort="date">Date</th>
                        <th data-sort="montant">Montant</th>
                        <?php if ($user['role_id'] == 1): ?>
                            <th data-sort="is_deleted">Statut</th>
                        <?php endif; ?>
                        <th>Actions</th>
                        <th>Facture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventes as $vente): ?>
                        <tr class="<?php echo $vente['is_deleted'] ? 'deleted-row' : ''; ?>">
                            <td><?php echo htmlspecialchars($vente['vente_id']); ?></td>
                            <td><?php echo htmlspecialchars($vente['client_id']); ?></td>
                            <td><?php echo htmlspecialchars($vente['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($vente['date']); ?></td>
                            <td><?php echo htmlspecialchars($vente['montant']); ?> €</td>
                            <?php if ($user['role_id'] == 1): ?>
                                <td><?php echo $vente['is_deleted'] ? 'Supprimé' : 'Actif'; ?></td>
                            <?php endif; ?>
                            <td>
                                <a href="/Pharmacie_S/Views/mes_documents/show_vente.php?id=<?php echo $vente['vente_id']; ?>"
                                    title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/Pharmacie_S/Views/mes_documents/edit_vente.php?id=<?php echo $vente['vente_id']; ?>"
                                    title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user['role_id'] == 1): ?>
                                    <?php if ($vente['is_deleted']): ?>
                                        <a href="/Pharmacie_S/Php/mes_documents/restore_vente.php?id=<?php echo $vente['vente_id']; ?>"
                                            onclick="return confirm('Êtes-vous sûr de vouloir restaurer cette vente ?');"
                                            title="Restaurer">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="/Pharmacie_S/Views/mes_documents/delete_vente.php?id=<?php echo $vente['vente_id']; ?>"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vente ?');"
                                            title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/Pharmacie_S/Views/mes_documents/factures.php?id=<?php echo $vente['vente_id']; ?>"
                                    title="Facture">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container centered-content">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?><?php echo $filteredUserId ? '&user_id=' . $filteredUserId : ''; ?><?php echo $dateDebut ? '&date_debut=' . $dateDebut : ''; ?><?php echo $dateFin ? '&date_fin=' . $dateFin : ''; ?>"
                                class="back-link" title="Première page">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?page=<?php echo ($page - 1); ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?><?php echo $filteredUserId ? '&user_id=' . $filteredUserId : ''; ?><?php echo $dateDebut ? '&date_debut=' . $dateDebut : ''; ?><?php echo $dateFin ? '&date_fin=' . $dateFin : ''; ?>"
                                class="back-link" title="Page précédente">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?><?php echo $filteredUserId ? '&user_id=' . $filteredUserId : ''; ?><?php echo $dateDebut ? '&date_debut=' . $dateDebut : ''; ?><?php echo $dateFin ? '&date_fin=' . $dateFin : ''; ?>"
                                class="back-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?><?php echo $filteredUserId ? '&user_id=' . $filteredUserId : ''; ?><?php echo $dateDebut ? '&date_debut=' . $dateDebut : ''; ?><?php echo $dateFin ? '&date_fin=' . $dateFin : ''; ?>"
                                class="back-link" title="Page suivante">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?page=<?php echo $totalPages; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?><?php echo $includeDeleted ? '&include_deleted=1' : ''; ?><?php echo $filteredUserId ? '&user_id=' . $filteredUserId : ''; ?><?php echo $dateDebut ? '&date_debut=' . $dateDebut : ''; ?><?php echo $dateFin ? '&date_fin=' . $dateFin : ''; ?>"
                                class="back-link" title="Dernière page">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="centered-content">Aucune vente trouvée.</p>
        <?php endif; ?>
    </section>

    <div class="centered-content">
        <a href="/Pharmacie_S/Views/mes_documents/index_mes_documents.php" class="back-link-gray">
            Retour aux Documents
        </a>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Initialisation de Select2 pour le sélecteur d'utilisateur
        $('#user_id').select2({
            placeholder: 'Sélectionner un utilisateur',
            allowClear: true
        });

        // Gestion du tri des colonnes
        $('#ventes-table th[data-sort]').click(function() {
            const column = $(this).data('sort');
            let direction = 'asc';

            // Si la colonne est déjà triée, inverser la direction
            if ($(this).hasClass('th-sort-asc')) {
                direction = 'desc';
            }

            // Construire l'URL avec les paramètres existants
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort', column);
            urlParams.set('direction', direction);

            // Rediriger vers la nouvelle URL
            window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
        });

        // Ajouter les indicateurs de tri
        const currentSort = '<?php echo $sortColumn; ?>';
        const currentDirection = '<?php echo $sortDirection; ?>';
        if (currentSort) {
            const th = $(`th[data-sort="${currentSort}"]`);
            th.addClass(currentDirection === 'asc' ? 'th-sort-asc' : 'th-sort-desc');
        }
    });
</script>

<script src="/Pharmacie_S/js/sort_ventes.js"></script>

<?php
// Affichage des messages de session
if (isset($_SESSION['error'])) {
    echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success']) . '</p>';
    unset($_SESSION['success']);
}

// Inclusion du footer
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php';
?>