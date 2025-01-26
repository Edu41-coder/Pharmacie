<?php
$pageTitle = "Détails de l'Ordonnance";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<style>
    .ordonnance-details {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .ordonnance-image {
        max-width: 100%;
        height: auto;
        margin-top: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .button-container {
        text-align: center;
        margin-top: 20px;
    }
    .print-button, .back-link-gray {
        display: inline-block;
        margin: 10px;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s;
        border: none;
        cursor: pointer;
    }
    .print-button:hover {
        background-color: #45a049;
    }
    .back-link-gray {
        background-color: #6c757d;
    }
    .back-link-gray:hover {
        background-color: #5a6268;
    }
    @media print {
        @page {
            size: A4;
            margin: 1cm;
        }
        body * {
            visibility: hidden;
        }
        #printable, #printable * {
            visibility: visible;
        }
        #printable {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            page-break-after: always;
        }
        #printable img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "ordonnances-page";
    });
    function printOrdonnance() {
        window.print();
    }
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/mes_documents/show_ordonnance.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<div class="product-details-container">
    <?php if (isset($ordonnance)): ?>
        <div class="ordonnance-details">
            <h1>Détails de l'Ordonnance</h1>
            <p><strong>Numéro d'Ordonnance:</strong> <?php echo htmlspecialchars($ordonnance['numero_ordonnance']); ?></p>
            <p><strong>Numéro d'Ordre:</strong> <?php echo htmlspecialchars($ordonnance["numero_d'ordre"]); ?></p>
            <?php
            if (!empty($ordonnance['image_path'])):
                $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $ordonnance['image_path']);
                $relativePath = str_replace('\\', '/', $relativePath);
            ?>
                <div id="printable">
                    <img src="<?php echo htmlspecialchars($relativePath); ?>" alt="Image de l'ordonnance" class="ordonnance-image">
                </div>
            <?php else: ?>
                <p>Aucune image disponible pour cette ordonnance.</p>
            <?php endif; ?>

            <div class="button-container">
                <button onclick="printOrdonnance()" class="print-button">Imprimer l'Ordonnance</button>
                <a href="/Pharmacie_S/Views/mes_documents/mes_ordonnances.php" class="back-link-gray">Retour aux Ordonnances</a>
            </div>
        </div>
    <?php else: ?>
        <p>Aucune ordonnance trouvée.</p>
    <?php endif; ?>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>