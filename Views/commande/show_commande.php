<?php
$pageTitle = "Détails de la Commande";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.id = "product-details-page";
    });
</script>
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .product-details-container, .product-details-container * {
            visibility: visible;
        }
        .product-details-container {
            position: absolute;
            left: 0;
            top: 0;
        }
        .no-print {
            display: none;
        }
    }
</style>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/commande/show_commande.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<div class="product-details-container">
    <h1>Détails de la Commande</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif ($commande): ?>
        <table class="commande-details-table">
            <tr>
                <th>ID Commande</th>
                <td><?php echo htmlspecialchars($commande['commande_id']); ?></td>
            </tr>
            <tr>
                <th>Date de Commande</th>
                <td><?php echo htmlspecialchars($commande['date_commande']); ?></td>
            </tr>
            <tr>
                <th>Statut</th>
                <td><?php echo htmlspecialchars($commande['statut']); ?></td>
            </tr>
            <tr>
                <th>Total</th>
                <td><?php echo htmlspecialchars($commande['total']); ?> €</td>
            </tr>
        </table>

        <h2>Produits de la Commande</h2>
        <?php if (!empty($produits)): ?>
            <table class="produits-commande-table">
                <thead>
                    <tr>
                        <th>Nom du Produit</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire HT</th>
                        <th>Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $produit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                            <td><?php echo htmlspecialchars($produit['quantite']); ?></td>
                            <td><?php echo htmlspecialchars($produit['prix_vente_ht']); ?> €</td>
                            <td><?php echo htmlspecialchars($produit['quantite'] * $produit['prix_vente_ht']); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun produit dans cette commande.</p>
        <?php endif; ?>

        <button onclick="window.print();" class="print-button no-print"><i class="fas fa-print"></i>Imprimer les détails de la commande</button>
    <?php endif; ?>

    <div class="centered-link no-print">
        <a href="/Pharmacie_S/Views/commande/index_commande.php" class="back-link-gray">Retour à la liste des commandes</a>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>