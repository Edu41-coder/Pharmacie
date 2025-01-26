<?php
$pageTitle = "Mes Ordonnances";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/mes_ventes.css">
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<script src="/Pharmacie_S/js/sort_ordonnances.js"></script>
<style>
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
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "ordonnances-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/mes_documents/mes_ordonnances.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<div class="full-width-container">
    <h1 class="centered-content">Mes Ordonnances</h1>
    
    <?php if (!empty($error)): ?>
        <p class="centered-content" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p class="centered-content" style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <table class="user-table" id="ordonnances-table">
        <thead>
            <tr>
                <th data-sort="ordonnance_id">ID Ordonnance</th>
                <th data-sort="numero_ordonnance">Numéro Ordonnance</th>
                <th data-sort="numero_d'ordre">Numéro d'Ordre</th>
                <th data-sort="vente_id">ID Vente</th>
                <th data-sort="date">Date Vente</th>
                <th data-sort="is_deleted">Supprimé</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($ordonnances) && is_array($ordonnances)): ?>
                <?php foreach ($ordonnances as $ordonnance): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ordonnance['ordonnance_id']); ?></td>
                        <td><?php echo htmlspecialchars($ordonnance['numero_ordonnance']); ?></td>
                        <td><?php echo htmlspecialchars($ordonnance["numero_d'ordre"]); ?></td>
                        <td><?php echo htmlspecialchars($ordonnance['vente_id']); ?></td>
                        <td><?php echo htmlspecialchars($ordonnance['date']); ?></td>
                        <td><?php echo $ordonnance['is_deleted'] ? 'Oui' : 'Non'; ?></td>
                        <td>
                            <a href="/Pharmacie_S/Views/mes_documents/show_ordonnance.php?id=<?php echo htmlspecialchars($ordonnance['ordonnance_id']); ?>" 
                               title="Voir l'ordonnance">
                                <i class="fas fa-file-medical"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Aucune ordonnance trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1" class="back-link" title="Première page">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?page=<?php echo ($page - 1); ?>" class="back-link" title="Page précédente">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            
            for ($i = $start; $i <= $end; $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="back-link <?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo ($page + 1); ?>" class="back-link" title="Page suivante">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?page=<?php echo $totalPages; ?>" class="back-link" title="Dernière page">
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
    // Pour maintenir la fonctionnalité de tri
    window.currentOrdonnances = <?php echo json_encode($ordonnances); ?>;
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>