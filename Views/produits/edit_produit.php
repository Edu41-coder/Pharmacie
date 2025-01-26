<?php
$pageTitle = "Modifier un Produit";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<style>
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    .form-control {
        width: 100%;
        padding: 0.375rem 0.75rem;
    }
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 0.25rem;
    }
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    .white-container {
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = 'index-produits-page';
    });
</script>
EOT;
// Instancier le modèle Produit
$produitModel = new Produit();

// Récupérer l'ID du produit à modifier depuis l'URL
$produitIdAModifier = isset($_GET['id']) ? $_GET['id'] : null;

if ($produitIdAModifier) {
    // Tentative de récupération du produit
    $produit = $produitModel->getProduitById($produitIdAModifier, true);

    // Vérification si le produit existe
    if ($produit) {
        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'];
            $description = $_POST['description'];
            $prix_vente_ht = $_POST['prix_vente_ht'];
            $prescription = $_POST['prescription'];
            $taux_remboursement = !empty($_POST['taux_remboursement']) ? $_POST['taux_remboursement'] : null;
            $alerte = !empty($_POST['alerte']) ? $_POST['alerte'] : null;
            $declencher_alerte = $_POST['declencher_alerte'];
            $is_deleted = isset($_POST['is_deleted']) ? $_POST['is_deleted'] : 0;

            // Validation simplifiée
            if (is_numeric($prix_vente_ht) && $prix_vente_ht >= 0) {
                $produitModel->updateProduit(
                    $produitIdAModifier,
                    $nom,
                    $description,
                    $prix_vente_ht,
                    $prescription,
                    $taux_remboursement,
                    $alerte,
                    $declencher_alerte,
                    $is_deleted
                );

                $_SESSION['success'] = "Produit modifié avec succès.";
                $produit = $produitModel->getProduitById($produitIdAModifier, true);
            } else {
                $_SESSION['error'] = "Le prix de vente doit être un nombre positif.";
            }
        }
    } else {
        $_SESSION['error'] = "Produit non trouvé.";
        header('Location: /Pharmacie_S/Views/produits/index_produits.php');
        exit();
    }
} else {
    $_SESSION['error'] = "ID de produit non spécifié.";
    header('Location: /Pharmacie_S/Views/produits/index_produits.php');
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php'; ?>
<div class="form-container">
    <h1 class="text-center mb-4">Modifier un Produit</h1>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    <div class="white-container">
        <form action="/Pharmacie_S/Views/produits/edit_produit.php?id=<?php echo htmlspecialchars($produit['produit_id']); ?>"
            method="post"
            class="needs-validation">
            <div class="form-group">
                <label for="nom" class="form-label">Nom:</label>
                <input type="text" class="form-control" id="nom" name="nom"
                    value="<?php echo htmlspecialchars($produit['nom']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description:</label>
                <textarea class="form-control" id="description" name="description"
                    rows="3"><?php echo htmlspecialchars($produit['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="prix_vente_ht" class="form-label">Prix Vente HT:</label>
                <input type="number" class="form-control" id="prix_vente_ht" name="prix_vente_ht"
                    value="<?php echo htmlspecialchars($produit['prix_vente_ht']); ?>"
                    step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="prescription" class="form-label">Prescription:</label>
                <select class="form-control" id="prescription" name="prescription" required>
                    <option value="oui" <?php echo $produit['prescription'] == 'oui' ? 'selected' : ''; ?>>Oui</option>
                    <option value="non" <?php echo $produit['prescription'] == 'non' ? 'selected' : ''; ?>>Non</option>
                </select>
            </div>

            <div class="form-group">
                <label for="taux_remboursement" class="form-label">Taux Remboursement (%):</label>
                <input type="number" class="form-control" id="taux_remboursement" name="taux_remboursement"
                    value="<?php echo htmlspecialchars($produit['taux_remboursement']); ?>"
                    min="0" max="100" step="1">
            </div>

            <div class="form-group">
                <label for="alerte" class="form-label">Seuil d'Alerte:</label>
                <input type="number" class="form-control" id="alerte" name="alerte"
                    value="<?php echo htmlspecialchars($produit['alerte']); ?>" min="0">
            </div>

            <div class="form-group">
                <label for="declencher_alerte" class="form-label">Déclencher Alerte:</label>
                <select class="form-control" id="declencher_alerte" name="declencher_alerte" required>
                    <option value="oui" <?php echo $produit['declencher_alerte'] == 'oui' ? 'selected' : ''; ?>>Oui</option>
                    <option value="non" <?php echo $produit['declencher_alerte'] == 'non' ? 'selected' : ''; ?>>Non</option>
                </select>
            </div>

            <div class="form-group">
                <label for="is_deleted" class="form-label">Statut du produit:</label>
                <select class="form-control" id="is_deleted" name="is_deleted">
                    <option value="0" <?php echo $produit['is_deleted'] == 0 ? 'selected' : ''; ?>>Actif</option>
                    <option value="1" <?php echo $produit['is_deleted'] == 1 ? 'selected' : ''; ?>>Supprimé</option>
                </select>
            </div>
            <div class="d-flex justify-content-center gap-3 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>

                <a href="/Pharmacie_S/Views/produits/index_produits.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Validation personnalisée du formulaire
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');

        form.addEventListener('submit', function(event) {
            const prixVenteHt = document.getElementById('prix_vente_ht').value;

            if (prixVenteHt < 0) {
                event.preventDefault();
                alert('Le prix de vente doit être un nombre positif.');
            }
        });

        // Gestion dynamique du taux de remboursement
        const prescriptionSelect = document.getElementById('prescription');
        const tauxRemboursementInput = document.getElementById('taux_remboursement');

        prescriptionSelect.addEventListener('change', function() {
            if (this.value === 'non') {
                tauxRemboursementInput.value = '';
                tauxRemboursementInput.disabled = true;
            } else {
                tauxRemboursementInput.disabled = false;
            }
        });

        // Initialisation de l'état du taux de remboursement
        if (prescriptionSelect.value === 'non') {
            tauxRemboursementInput.disabled = true;
        }
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>