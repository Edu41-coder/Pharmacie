<?php
$pageTitle = "Modifier l'État du Chèque";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "cheques-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Instancier le modèle Cheque
$chequeModel = new Cheque();

// Récupérer l'ID du chèque à modifier depuis l'URL
$chequeIdAModifier = isset($_GET['id']) ? $_GET['id'] : null;

if ($chequeIdAModifier) {
    // Tentative de récupération du chèque
    $cheque = $chequeModel->getChequeById($chequeIdAModifier);

    // Vérification si le chèque existe
    if ($cheque) {
        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $etat = $_POST['etat'];

            // Validation simplifiée (vous pouvez ajouter plus de validations si nécessaire)
            if (in_array($etat, ['en_attente', 'valide', 'refuse'])) {
                if ($chequeModel->updateChequeEtat($chequeIdAModifier, $etat)) {
                    // Stocker un message de succès dans la session
                    $_SESSION['success'] = "État du chèque modifié avec succès.";

                    // Récupérer à nouveau le chèque mis à jour
                    $cheque = $chequeModel->getChequeById($chequeIdAModifier);
                } else {
                    $_SESSION['error'] = "Erreur lors de la modification de l'état du chèque.";
                }
            } else {
                // Gérer les erreurs de validation ici
                $_SESSION['error'] = "État du chèque invalide.";
            }
        }
    } else {
        // Gérer le cas où le chèque n'est pas trouvé
        $_SESSION['error'] = "Chèque non trouvé.";
        header('Location: /Pharmacie_S/Views/comptabilité/index_cheques.php');
        exit();
    }
} else {
    // Gérer le cas où l'ID n'est pas spécifié
    $_SESSION['error'] = "ID de chèque non spécifié.";
    header('Location: /Pharmacie_S/Views/comptabilité/index_cheques.php');
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<h1>Modifier l'État du Chèque</h1>
<?php if (!empty($_SESSION['error'])): ?>
    <p style="color: red;"><?php echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']); ?></p>
<?php endif; ?>
<?php if (!empty($_SESSION['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_SESSION['success']);
                                unset($_SESSION['success']); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/Views/comptabilité/edit_cheque.php?id=<?php echo htmlspecialchars($cheque['cheque_id']); ?>" method="post" class="register-form">
    <label for="numero_cheque">Numéro de Chèque:</label>
    <input type="text" id="numero_cheque" name="numero_cheque" value="<?php echo htmlspecialchars($cheque['numero_cheque']); ?>" readonly>

    <label for="montant">Montant:</label>
    <input type="number" id="montant" name="montant" value="<?php echo htmlspecialchars($cheque['montant']); ?>" step="0.01" min="0" readonly>

    <label for="etat">État:</label>
    <select id="etat" name="etat" required>
        <option value="en_attente" <?php echo $cheque['etat'] == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
        <option value="valide" <?php echo $cheque['etat'] == 'valide' ? 'selected' : ''; ?>>Validé</option>
        <option value="refuse" <?php echo $cheque['etat'] == 'refuse' ? 'selected' : ''; ?>>Refusé</option>
    </select>

    <button type="submit">Modifier</button>
</form>
<a href="/Pharmacie_S/Views/comptabilité/index_cheques.php" class="back-link-gray">
    <i class="fas fa-arrow-left"></i> Retour à l'index cheques
</a>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>