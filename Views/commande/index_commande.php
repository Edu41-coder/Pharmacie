<?php
$pageTitle = "Liste des Commandes";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/index_commande.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<link href="/Pharmacie_S/css/select2.min.css" rel="stylesheet" />
<script src="/Pharmacie_S/js/select2.min.js"></script>
<script src="/Pharmacie_S/js/sort_commande.js"></script>
<style>
    /* Styles existants */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 20px 0;
    }
    .pagination .active {
        background-color: #4CAF50;
        color: white;
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

    /* Style pour les en-têtes triables */
    .user-table th {
        background-color: #333333 !important;
        color: white !important;
        padding: 8px;
        text-align: center;
        padding-right: 25px;
        position: relative;
    }

    /* Survol des en-têtes */
    .user-table th[data-sort]:hover {
        background-color: #4a4a4a !important;
        cursor: pointer;
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
    th.sort-asc::after { 
        content: "\\f0de" !important;
        font-family: "Font Awesome 5 Free" !important;
        transform: translateY(-50%) rotate(180deg) !important;
    }

    th.sort-desc::after { 
        content: "\\f0dd" !important;
        font-family: "Font Awesome 5 Free" !important;
        transform: translateY(-50%) !important;
    }

    /* Styles de priorité maximale pour les lignes */
    .full-width-container .user-table tbody tr:nth-child(even) { 
        background-color: #f9f9f9 !important; 
    }

    .full-width-container .user-table tbody tr:hover { 
        background-color: #e3f2fd !important; 
        transition: background-color 0.2s ease !important;
    }

    /* Style pour la table */
    .user-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .user-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-a-commander-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/commande/index_commande.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<div class="full-width-container">
    <h1 class="centered-content">Liste des Commandes</h1>

    <?php if (!empty($error)): ?>
        <p class="centered-content" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p class="centered-content" style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <div class="centered-content">
        <div class="action-buttons">
            <a href="/Pharmacie_S/Views/commande/create_commande.php" class="back-link">
                <i class="fas fa-plus"></i> Ajouter une Commande
            </a>
            <button onclick="window.print();" class="print-button">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
        <!-- Formulaire de filtres -->
        <form method="GET" action="" class="mb-4">
            <div class="form-row mb-3">
                <div class="form-group mr-3">
                    <label for="statut">Statut</label>
                    <select name="statut" id="statut" class="form-control">
                        <option value="">Tous</option>
                        <option value="En attente" <?php echo $statut === 'En attente' ? 'selected' : ''; ?>>En attente</option>
                        <option value="En cours" <?php echo $statut === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="Livrée" <?php echo $statut === 'Livrée' ? 'selected' : ''; ?>>Livrée</option>
                        <option value="Annulée" <?php echo $statut === 'Annulée' ? 'selected' : ''; ?>>Annulée</option>
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

            <div class="form-row justify-content-center mb-3">
                <button type="button" class="btn btn-secondary" id="reset-filters">Réinitialiser les filtres</button>
            </div>

            <input type="hidden" name="sortColumn" value="<?php echo htmlspecialchars($sortColumn); ?>">
            <input type="hidden" name="sortOrder" value="<?php echo htmlspecialchars($sortOrder); ?>">
        </form>
    </div>
    <table class="user-table" id="commandes-table">
        <thead>
            <tr>
                <th data-sort="commande_id" class="<?php echo $sortColumn === 'commande_id' ? 'sort-' . strtolower($sortOrder) : ''; ?>">
                    ID Commande
                </th>
                <th data-sort="date_commande" class="<?php echo $sortColumn === 'date_commande' ? 'sort-' . strtolower($sortOrder) : ''; ?>">
                    Date Commande
                </th>
                <th data-sort="statut" class="<?php echo $sortColumn === 'statut' ? 'sort-' . strtolower($sortOrder) : ''; ?>">
                    Statut
                </th>
                <th data-sort="total" class="<?php echo $sortColumn === 'total' ? 'sort-' . strtolower($sortOrder) : ''; ?>">
                    Total
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($commandes) && is_array($commandes)): ?>
                <?php foreach ($commandes as $commande): ?>
                    <tr data-id="<?php echo htmlspecialchars($commande['commande_id']); ?>">
                        <td><?php echo htmlspecialchars($commande['commande_id']); ?></td>
                        <td><?php echo htmlspecialchars($commande['date_commande']); ?></td>
                        <td><?php echo htmlspecialchars($commande['statut']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($commande['total'], 2, ',', ' ')); ?> €</td>
                        <td>
                            <a href="/Pharmacie_S/Views/commande/show_commande.php?id=<?php echo htmlspecialchars($commande['commande_id']); ?>"
                                title="Voir"><i class="fas fa-eye"></i></a>
                            <a href="/Pharmacie_S/Views/commande/edit_commande.php?id=<?php echo htmlspecialchars($commande['commande_id']); ?>"
                                title="Modifier"><i class="fas fa-edit"></i></a>
                            <a href="/Pharmacie_S/Views/commande/delete_commande.php?commande_id=<?php echo htmlspecialchars($commande['commande_id']); ?>"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');"
                                title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Aucune commande trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1<?php echo !empty($queryString) ? '&' . $queryString : ''; ?>"
                    class="back-link" title="Première page">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?page=<?php echo ($page - 1) . (!empty($queryString) ? '&' . $queryString : ''); ?>"
                    class="back-link" title="Page précédente">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);

            for ($i = $start; $i <= $end; $i++): ?>
                <a href="?page=<?php echo $i . (!empty($queryString) ? '&' . $queryString : ''); ?>"
                    class="back-link <?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo ($page + 1) . (!empty($queryString) ? '&' . $queryString : ''); ?>"
                    class="back-link" title="Page suivante">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?page=<?php echo $totalPages . (!empty($queryString) ? '&' . $queryString : ''); ?>"
                    class="back-link" title="Dernière page">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="centered-content mt-4">
        <a href="/Pharmacie_S/index.php" class="back-link-gray">
            <i class="fas fa-arrow-left"></i> Retour à l'accueil
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Réinitialiser les filtres
        document.getElementById("reset-filters").addEventListener("click", function() {
            document.getElementById("statut").value = "";
            document.getElementById("date_debut").value = "";
            document.getElementById("date_fin").value = "";
            window.location.href = window.location.pathname;
        });

        // Soumission automatique du formulaire lors du changement des filtres
        document.getElementById("statut").addEventListener("change", function() {
            this.form.submit();
        });

        document.getElementById("date_debut").addEventListener("change", function() {
            if (document.getElementById("date_fin").value) {
                this.form.submit();
            }
        });

        document.getElementById("date_fin").addEventListener("change", function() {
            if (document.getElementById("date_debut").value) {
                this.form.submit();
            }
        });
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>