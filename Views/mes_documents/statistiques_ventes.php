<?php
$pageTitle = "Statistiques des ventes";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<style>
    .grid-container {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f8f9fa;
        width: 95%;
        margin-left: auto;
        margin-right: auto;
    }
    .grid-item {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 15px;
        background-color: white;
        width: 95%;
        margin-left: auto;
        margin-right: auto;
    }
    .card {
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .table-container {
        width: 100%;
        overflow-x: auto;
    }
    .table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 600px;
    }
    .table th,
    .table td {
        border: 1px solid #dee2e6;
        padding: 12px;
        text-align: center;
    }
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        background-color: #343a40;
        color: white;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.05);
    }
    .col-utilisateur {
        width: 60%;
    }
    .col-ventes {
        width: 40%;
    }        
    .text-center {
        text-align: center;
    }
</style>
EOT;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

$vente = new Vente();

// Récupérer les dates de début et de fin si elles sont définies
$dateDebut = isset($_GET['date_debut']) ? $_GET['date_debut'] : null;
$dateFin = isset($_GET['date_fin']) ? $_GET['date_fin'] : null;

// Récupérer les statistiques des ventes
$statistiques = $vente->getStatistiquesVentes($dateDebut, $dateFin);

// Calculer le total général des ventes
$totalGeneral = array_sum(array_column($statistiques, 'total_ventes'));
?>

<script>
    document.body.className = "index-produits-page";
</script>
<div class="container-fluid grid-container">
    <h1 class="mb-4 text-center">Statistiques des Ventes</h1>

    <div class="row grid-item">
        <div class="col-12">
            <form method="GET" action="" class="filter-form mb-4">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="date_debut">Date de début:</label>
                        <input type="date" id="date_debut" name="date_debut" class="form-control"
                            value="<?php echo htmlspecialchars($dateDebut); ?>">
                    </div>

                    <div class="form-group col-md-4">
                        <label for="date_fin">Date de fin:</label>
                        <input type="date" id="date_fin" name="date_fin" class="form-control"
                            value="<?php echo htmlspecialchars($dateFin); ?>">
                    </div>
                    <div class="form-group col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">Filtrer</button>
                        <a href="?<?php echo http_build_query(array_diff_key($_GET, array_flip(['date_debut', 'date_fin']))); ?>"
                            class="btn btn-secondary">Réinitialiser le filtre</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row grid-item">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center">Résultats des ventes</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th class="col-utilisateur">Utilisateur</th>
                                    <th class="col-ventes">Somme des ventes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statistiques as $stat): ?>
                                    <tr>
                                        <td class="col-utilisateur">
                                            <?php echo htmlspecialchars($stat['prenom'] . ' ' . $stat['nom']); ?>
                                        </td>
                                        <td class="col-ventes">
                                            <?php echo number_format($stat['total_ventes'], 2); ?> €
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-info">
                                    <th class="col-utilisateur">Total Général</th>
                                    <th class="col-ventes">
                                        <?php echo number_format($totalGeneral, 2); ?> €
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4 grid-item">
        <div class="col-12">
            <a href="/Pharmacie_S/Views/mes_documents/index_mes_documents.php" class="back-link-gray">
                <i class="fas fa-arrow-left"></i> Retour aux documents
            </a>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>
