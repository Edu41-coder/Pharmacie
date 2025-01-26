<?php
$pageTitle = "Ajouter un Produit";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/produits/create_produit.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<script>
    document.body.className = 'index-produits-page';
</script>

<h1>Créer un Produit</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form id="produitForm" action="/Pharmacie_S/PHP/produits/create_produit.php" method="post" class="register-form" novalidate>
    <label for="nom">Nom:</label>
    <input type="text" id="nom" name="nom" required 
           maxlength="100" 
           pattern="[A-Za-z0-9\s\-]+"
           title="Lettres, chiffres, espaces et tirets uniquement"
           oninput="validateNom(this)">
    <span class="error-message" id="nom-error"></span>

    <label for="description">Description:</label>
    <textarea id="description" name="description"
              maxlength="500"
              oninput="validateDescription(this)"></textarea>
    <span class="error-message" id="description-error"></span>

    <label for="prix_vente_ht">Prix Vente HT:</label>
    <input type="number" id="prix_vente_ht" name="prix_vente_ht" 
           step="0.01" min="0" required
           pattern="^\d+(\.\d{0,2})?$"
           oninput="validatePrix(this)">
    <span class="error-message" id="prix-error"></span>

    <label for="prescription">Prescription:</label>
    <select id="prescription" name="prescription" required>
        <option value="oui">Oui</option>
        <option value="non">Non</option>
    </select>

    <label for="taux_remboursement">Taux Remboursement:</label>
    <input type="number" id="taux_remboursement" name="taux_remboursement" 
           min="0" max="100" step="1"
           pattern="^\d+$"
           oninput="validateTauxRemboursement(this)">
    <span class="error-message" id="taux-error"></span>

    <label for="alerte">Alerte:</label>
    <input type="number" id="alerte" name="alerte" 
           min="0" step="1"
           pattern="^\d+$"
           oninput="validateAlerte(this)">
    <span class="error-message" id="alerte-error"></span>

    <label for="declencher_alerte">Déclencher Alerte:</label>
    <select id="declencher_alerte" name="declencher_alerte">
        <option value="oui">Oui</option>
        <option value="non">Non</option>
    </select>

    <button type="submit">Créer</button>
</form>

<a href="/Pharmacie_S/Views/produits/index_produits.php" class="back-link-gray">Retour à la liste des produits</a>

<style>
.error-message {
    color: red;
    font-size: 0.8em;
    display: block;
    margin-top: 5px;
}
</style>

<script>
function sanitizeInput(input) {
    return input.replace(/[<>]/g, '');
}

function validateNom(input) {
    const errorElement = document.getElementById('nom-error');
    const value = sanitizeInput(input.value.trim());
    
    if (value === '') {
        errorElement.textContent = 'Le nom est obligatoire';
        return false;
    }
    if (value.length > 100) {
        errorElement.textContent = 'Le nom ne doit pas dépasser 100 caractères';
        return false;
    }
    if (!/^[A-Za-z0-9\s\-]+$/.test(value)) {
        errorElement.textContent = 'Le nom ne doit contenir que des lettres, des chiffres, des espaces et des tirets';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

function validateDescription(input) {
    const errorElement = document.getElementById('description-error');
    const value = sanitizeInput(input.value.trim());
    
    if (value.length > 500) {
        errorElement.textContent = 'La description ne doit pas dépasser 500 caractères';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

function validatePrix(input) {
    const errorElement = document.getElementById('prix-error');
    const value = input.value;
    
    if (value === '') {
        errorElement.textContent = 'Le prix est obligatoire';
        return false;
    }
    if (value < 0) {
        errorElement.textContent = 'Le prix ne peut pas être négatif';
        return false;
    }
    if (!/^\d+(\.\d{0,2})?$/.test(value)) {
        errorElement.textContent = 'Le prix doit avoir maximum 2 décimales';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

function validateTauxRemboursement(input) {
    const errorElement = document.getElementById('taux-error');
    const value = input.value;
    
    if (value !== '') {
        if (value < 0 || value > 100) {
            errorElement.textContent = 'Le taux doit être entre 0 et 100';
            return false;
        }
        if (!Number.isInteger(parseFloat(value))) {
            errorElement.textContent = 'Le taux doit être un nombre entier';
            return false;
        }
    }
    errorElement.textContent = '';
    return true;
}

function validateAlerte(input) {
    const errorElement = document.getElementById('alerte-error');
    const value = input.value;
    
    if (value !== '') {
        if (value < 0) {
            errorElement.textContent = "L'alerte doit être un nombre positif";
            return false;
        }
        if (!Number.isInteger(parseFloat(value))) {
            errorElement.textContent = "L'alerte doit être un nombre entier";
            return false;
        }
    }
    errorElement.textContent = '';
    return true;
}

document.getElementById('produitForm').addEventListener('submit', function(event) {
    let isValid = true;
    
    // Valider tous les champs
    if (!validateNom(document.getElementById('nom'))) isValid = false;
    if (!validateDescription(document.getElementById('description'))) isValid = false;
    if (!validatePrix(document.getElementById('prix_vente_ht'))) isValid = false;
    if (!validateTauxRemboursement(document.getElementById('taux_remboursement'))) isValid = false;
    if (!validateAlerte(document.getElementById('alerte'))) isValid = false;

    if (!isValid) {
        event.preventDefault();
    }
});

// Protection XSS supplémentaire pour tous les champs de texte
document.querySelectorAll('input[type="text"], textarea').forEach(input => {
    input.addEventListener('input', function() {
        this.value = sanitizeInput(this.value);
    });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>