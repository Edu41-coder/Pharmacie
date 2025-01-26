<?php
$pageTitle = "Liste des Clients";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/styles.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/jquery-ui.css">
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<script src="/Pharmacie_S/js/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="/Pharmacie_S/js/search_client.js"></script>
<script src="/Pharmacie_S/js/sort_clients.js"></script>
<script src="/Pharmacie_S/js/mobile.js"></script>
<style>
    .centered-content {
        text-align: center;
        margin-bottom: 20px;
    }
    .search-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 50%;
        margin: 20px auto;
    }
    .search-container select,
    .search-container input {
        width: 100%;
        max-width: 300px;
        margin-bottom: 10px;
    }
    .action-buttons {
        text-align: center;
        margin-bottom: 20px;
    }

    /* Styles pour la responsivité */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 1rem;
        width: 100%;
        position: relative;
    }

    .table-responsive::after {
        content: '→';
        position: fixed;
        right: 10px;
        top: 50%;
        background: rgba(0,0,0,0.5);
        color: white;
        padding: 10px;
        border-radius: 50%;
        animation: pulse 2s infinite;
        display: none;
    }

    @media screen and (max-width: 768px) {
        .table-responsive.has-scroll::after {
            display: block;
        }
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
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
        document.body.className = "index-clients-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/clients/index_clients.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<div class="full-width-container">
    <h1 class="centered-content">Liste des Clients</h1>
    <?php if (!empty($error)): ?>
        <p class="centered-content" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p class="centered-content" style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <div class="action-buttons">
        <a href="/Pharmacie_S/Views/clients/create_client.php" class="back-link"><i class="fas fa-plus"></i> Ajouter un Client</a>
    </div>

    <!-- Champ de recherche -->
    <div class="search-container">
        <select id="search-criteria">
            <option value="all">Tous les clients</option>
            <option value="name">Nom et Prénom</option>
            <option value="email">Email</option>
            <option value="phone">Téléphone</option>
            <option value="carte_vitale">Numéro de Carte Vitale</option>
        </select>
        <input type="text" id="search-input" placeholder="Rechercher...">
    </div>

    <div class="table-responsive">
        <table class="user-table" id="clients-table">
            <thead>
                <tr>
                    <th data-column="client_id">ID</th>
                    <th data-column="nom">Nom</th>
                    <th data-column="prenom">Prénom</th>
                    <th data-column="email">Email</th>
                    <th data-column="telephone">Téléphone</th>
                    <th data-column="numero_carte_vitale">Numéro de Carte Vitale</th>
                    <th data-column="cheques_impayes">Chèques Impayés</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($clients) && is_array($clients)): ?>
                    <?php foreach ($clients as $client): ?>
                        <tr data-id="<?php echo htmlspecialchars($client['client_id']); ?>">
                            <td><?php echo htmlspecialchars($client['client_id']); ?></td>
                            <td><?php echo htmlspecialchars($client['nom']); ?></td>
                            <td><?php echo htmlspecialchars($client['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($client['email']); ?></td>
                            <td><?php echo htmlspecialchars($client['telephone']); ?></td>
                            <td><?php echo htmlspecialchars($client['numero_carte_vitale']); ?></td>
                            <td><?php echo $client['cheques_impayes'] ? 'Oui' : 'Non'; ?></td>
                            <td>
                                <a href="/Pharmacie_S/Views/clients/show_client.php?id=<?php echo htmlspecialchars($client['client_id']); ?>"><i class="fas fa-eye"></i></a>
                                <a href="/Pharmacie_S/Views/clients/edit_client.php?id=<?php echo htmlspecialchars($client['client_id']); ?>"><i class="fas fa-edit"></i></a>
                                <a href="/Pharmacie_S/Views/clients/delete_client.php?id=<?php echo htmlspecialchars($client['client_id']); ?>"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Aucun client trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=1&sort=<?php echo htmlspecialchars($sortColumn); ?>&direction=<?php echo htmlspecialchars($sortDirection); ?>"
                    class="back-link"
                    title="Première page">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?page=<?php echo ($currentPage - 1); ?>&sort=<?php echo htmlspecialchars($sortColumn); ?>&direction=<?php echo htmlspecialchars($sortDirection); ?>"
                    class="back-link"
                    title="Page précédente">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $currentPage - 2);
            $end = min($totalPages, $currentPage + 2);

            for ($i = $start; $i <= $end; $i++): ?>
                <a href="?page=<?php echo $i; ?>&sort=<?php echo htmlspecialchars($sortColumn); ?>&direction=<?php echo htmlspecialchars($sortDirection); ?>"
                    class="back-link <?php echo $i === $currentPage ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo ($currentPage + 1); ?>&sort=<?php echo htmlspecialchars($sortColumn); ?>&direction=<?php echo htmlspecialchars($sortDirection); ?>"
                    class="back-link"
                    title="Page suivante">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?page=<?php echo $totalPages; ?>&sort=<?php echo htmlspecialchars($sortColumn); ?>&direction=<?php echo htmlspecialchars($sortDirection); ?>"
                    class="back-link"
                    title="Dernière page">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="centered-link">
        <a href="/Pharmacie_S/index.php" class="back-link-gray">Retour à l'accueil</a>
    </div>
</div>

<script>
    window.currentClients = <?php echo json_encode($clients); ?>;
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>