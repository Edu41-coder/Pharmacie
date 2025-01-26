<?php
$pageTitle = "Détails de la facture";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

$venteId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($venteId <= 0) {
    echo "ID de vente invalide.";
    exit;
}

$mongoDb = Database_Mongo::getInstance()->getBdd();
$factureModel = new FactureModel($mongoDb);
$facture = $factureModel->getFactureByVenteId($venteId);
?>

<div class="container mt-4">
    <h1 class="mb-4">Facture pour la vente #<?php echo $venteId; ?></h1>

    <?php if ($facture): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Détails de la facture</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Numéro de facture :</strong> <?php echo $facture['_id']; ?></p>
                        <p><strong>ID de vente :</strong> <?php echo $facture['vente']['vente_id']; ?></p>
                        <p><strong>ID client :</strong> <?php echo $facture['vente']['client_id']; ?></p>
                        <p><strong>Nom du client :</strong> <?php echo $facture['vente']['client_nom']; ?></p>
                        <p><strong>Prénom du client :</strong> <?php echo $facture['vente']['client_prenom']; ?></p>
                        <p><strong>Date de vente :</strong> <?php echo $facture['vente']['date_vente'] ?: 'Non spécifiée'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Montant total :</strong> <?php echo number_format($facture['vente']['montant_total'], 2, ',', ' '); ?> €</p>
                        <p><strong>Montant réglé :</strong> <?php echo number_format($facture['vente']['montant_regle'], 2, ',', ' '); ?> €</p>
                        <p><strong>Montant à rembourser :</strong> <?php echo number_format($facture['vente']['montant_a_rembourser'], 2, ',', ' '); ?> €</p>
                    </div>
                </div>

                <h4 class="mt-4">Produits</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Montant total</th>
                            <th>Montant à rembourser</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facture['produits'] as $produit): ?>
                            <tr>
                                <td><?php echo $produit['nom']; ?></td>
                                <td><?php echo $produit['quantite']; ?></td>
                                <td><?php echo number_format($produit['prix_unitaire'], 2, ',', ' '); ?> €</td>
                                <td><?php echo number_format($produit['montant_total'], 2, ',', ' '); ?> €</td>
                                <td><?php echo number_format($produit['montant_a_rembourser'], 2, ',', ' '); ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h4 class="mt-4">Paiements</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Mode de paiement</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facture['paiements'] as $paiement): ?>
                            <tr>
                                <td><?php echo ucfirst($paiement['mode']); ?></td>
                                <td><?php echo number_format($paiement['montant'], 2, ',', ' '); ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p class="mt-3"><strong>Date de création :</strong> <?php echo $facture['createdAt']; ?></p>
            </div>
            <div class="card-footer">
                <a href="/Pharmacie_S/Views/mes_documents/mes_ventes.php" class="back-link-gray">
                    <i class="fas fa-arrow-left"></i> Retour à mes ventes
                </a>
                <button onclick="printFacture()" class="btn btn-primary float-right">
                    <i class="fas fa-print"></i> Imprimer Facture
                </button>
            </div>
        </div>

        <script>
        function printFacture() {
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Facture - Vente #<?php echo $venteId; ?></title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { width: 100%; max-width: 800px; margin: 0 auto; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>Facture pour la vente #<?php echo $venteId; ?></h1>
                        <div>
                            <p><strong>Numéro de facture :</strong> <?php echo $facture['_id']; ?></p>
                            <p><strong>ID de vente :</strong> <?php echo $facture['vente']['vente_id']; ?></p>
                            <p><strong>ID client :</strong> <?php echo $facture['vente']['client_id']; ?></p>
                            <p><strong>Nom du client :</strong> <?php echo $facture['vente']['client_nom']; ?></p>
                            <p><strong>Prénom du client :</strong> <?php echo $facture['vente']['client_prenom']; ?></p>
                            <p><strong>Date de vente :</strong> <?php echo $facture['vente']['date_vente'] ?: 'Non spécifiée'; ?></p>
                            <p><strong>Montant total :</strong> <?php echo number_format($facture['vente']['montant_total'], 2, ',', ' '); ?> €</p>
                            <p><strong>Montant réglé :</strong> <?php echo number_format($facture['vente']['montant_regle'], 2, ',', ' '); ?> €</p>
                            <p><strong>Montant à rembourser :</strong> <?php echo number_format($facture['vente']['montant_a_rembourser'], 2, ',', ' '); ?> €</p>
                        </div>
                        <h4>Produits</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix unitaire</th>
                                    <th>Montant total</th>
                                    <th>Montant à rembourser</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($facture['produits'] as $produit): ?>
                                    <tr>
                                        <td><?php echo $produit['nom']; ?></td>
                                        <td><?php echo $produit['quantite']; ?></td>
                                        <td><?php echo number_format($produit['prix_unitaire'], 2, ',', ' '); ?> €</td>
                                        <td><?php echo number_format($produit['montant_total'], 2, ',', ' '); ?> €</td>
                                        <td><?php echo number_format($produit['montant_a_rembourser'], 2, ',', ' '); ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <h4>Paiements</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Mode de paiement</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($facture['paiements'] as $paiement): ?>
                                    <tr>
                                        <td><?php echo ucfirst($paiement['mode']); ?></td>
                                        <td><?php echo number_format($paiement['montant'], 2, ',', ' '); ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p><strong>Date de création :</strong> <?php echo $facture['createdAt']; ?></p>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
        </script>

    <?php else: ?>
        <div class="alert alert-info">
            <p>Aucune facture n'existe pour cette vente.</p>
            <form action="/Pharmacie_S/PHP/mes_documents/create_facture.php" method="post" class="mt-3">
                <input type="hidden" name="vente_id" value="<?php echo $venteId; ?>">
                <button type="submit" name="creer_facture" class="btn btn-warning">Créer une facture</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>