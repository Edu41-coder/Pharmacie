<?php
$pageTitle = "modifier vente";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Instancier le modèle Vente
$venteModel = new Vente();

// Récupérer l'ID de la vente à modifier depuis l'URL
$venteIdAModifier = isset($_GET['id']) ? $_GET['id'] : null;

if ($venteIdAModifier) {
    // Tentative de récupération de la vente
    $vente = $venteModel->getVenteById($venteIdAModifier);

    // Vérification si la vente existe
    if ($vente) {
        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $commentaire = $_POST['commentaire'];

            // Mise à jour du commentaire
            $venteModel->updateVenteCommentaire($venteIdAModifier, $commentaire);

            // Stocker un message de succès dans la session
            $_SESSION['success'] = "Commentaire de la vente modifié avec succès.";

            // Récupérer à nouveau la vente mise à jour
            $vente = $venteModel->getVenteById($venteIdAModifier);
        }
    } else {
        // Gérer le cas où la vente n'est pas trouvée
        $_SESSION['error'] = "Vente non trouvée.";
        header('Location: /Pharmacie_S/Views/mes_documents/mes_ventes.php');
        exit();
    }
} else {
    // Gérer le cas où l'ID n'est pas spécifié
    $_SESSION['error'] = "ID de vente non spécifié.";
    header('Location: /Pharmacie_S/Views/mes_documents/mes_ventes.php');
    exit();
}

// Affichage de la vue pour l'édition de la vente
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<script>
    document.body.className = "index-produits-page";
</script>
<h1>Modifier une Vente</h1>
<?php if (!empty($_SESSION['error'])): ?>
    <p style="color: red;"><?php echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']); ?></p>
<?php endif; ?>
<?php if (!empty($_SESSION['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_SESSION['success']);
                                unset($_SESSION['success']); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/Views/mes_documents/edit_vente.php?id=<?php echo htmlspecialchars($vente['vente_id']); ?>" method="post" class="register-form">
    <label for="vente_id">ID Vente:</label>
    <input type="text" id="vente_id" name="vente_id" value="<?php echo htmlspecialchars($vente['vente_id']); ?>" readonly>

    <label for="client_id">Client ID:</label>
    <input type="text" id="client_id" name="client_id" value="<?php echo htmlspecialchars($vente['client_id']); ?>" readonly>

    <label for="user_id">User ID:</label>
    <input type="text" id="user_id" name="user_id" value="<?php echo htmlspecialchars($vente['user_id']); ?>" readonly>

    <label for="date">Date:</label>
    <input type="text" id="date" name="date" value="<?php echo htmlspecialchars($vente['date']); ?>" readonly>

    <label for="montant">Montant:</label>
    <input type="text" id="montant" name="montant" value="<?php echo htmlspecialchars($vente['montant']); ?>" readonly>

    <label for="montant_regle">Montant Réglé:</label>
    <input type="text" id="montant_regle" name="montant_regle" value="<?php echo htmlspecialchars($vente['montant_regle']); ?>" readonly>

    <label for="a_rembourser">À Rembourser:</label>
    <input type="text" id="a_rembourser" name="a_rembourser" value="<?php echo htmlspecialchars($vente['a_rembourser']); ?>" readonly>

    <label for="commentaire">Commentaire:</label>
    <textarea id="commentaire" name="commentaire"><?php echo htmlspecialchars($vente['commentaire']); ?></textarea>

    <button type="submit">Modifier</button>
</form>
<a href="/Pharmacie_S/Views/mes_documents/mes_ventes.php" class="back-link-gray">Retour à la liste des ventes</a>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>