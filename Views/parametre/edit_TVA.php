<?php
$pageTitle = "modifier TVA";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/parametre/edit_TVA.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<script>
    document.body.className = 'index-tva-page';
</script>

<h1>Modifier la TVA</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/PHP/parametre/edit_TVA.php" method="post" class="register-form">
    <label for="tva">TVA (%):</label>
    <input type="number" id="tva" name="tva" value="<?php echo htmlspecialchars($tva); ?>" required>

    <button type="submit">Modifier</button>
</form>
<a href="/Pharmacie_S/index.php" class="back-link-gray">Retour à l'accueil</a>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>