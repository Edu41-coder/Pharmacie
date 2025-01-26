<?php
$pageTitle = "Créer un Client";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/clients/create_client.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<script>
    document.body.className = "index-clients-page";
</script>

<h1>Créer un Client</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form id="clientForm" action="/Pharmacie_S/Views/clients/create_client.php" method="post" class="register-form" novalidate>
    <label for="nom">Nom:</label>
    <input type="text" id="nom" name="nom" required
           maxlength="100"
           pattern="[A-Za-zÀ-ÿ\s\-]+"
           title="Lettres, espaces et tirets uniquement"
           oninput="validateNom(this)">
    <span class="error-message" id="nom-error"></span>

    <label for="prenom">Prénom:</label>
    <input type="text" id="prenom" name="prenom" required
           maxlength="100"
           pattern="[A-Za-zÀ-ÿ\s\-]+"
           title="Lettres, espaces et tirets uniquement"
           oninput="validatePrenom(this)">
    <span class="error-message" id="prenom-error"></span>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required
           maxlength="255"
           oninput="validateEmail(this)">
    <span class="error-message" id="email-error"></span>

    <label for="telephone">Téléphone:</label>
    <input type="tel" id="telephone" name="telephone"
           pattern="[0-9\+\-\s]{10,15}"
           title="Format: 10 à 15 chiffres, +, - ou espaces"
           oninput="validateTelephone(this)">
    <span class="error-message" id="telephone-error"></span>

    <label for="adresse">Adresse:</label>
    <textarea id="adresse" name="adresse"
              maxlength="500"
              oninput="validateAdresse(this)"></textarea>
    <span class="error-message" id="adresse-error"></span>

    <label for="commentaire">Commentaire:</label>
    <textarea id="commentaire" name="commentaire"
              maxlength="1000"
              oninput="validateCommentaire(this)"></textarea>
    <span class="error-message" id="commentaire-error"></span>

    <label for="numero_carte_vitale">Numéro de Carte Vitale:</label>
    <input type="text" id="numero_carte_vitale" name="numero_carte_vitale"
           pattern="[0-9]{15}"
           title="15 chiffres exactement"
           oninput="validateCarteVitale(this)">
    <span class="error-message" id="carte-vitale-error"></span>

    <button type="submit">Créer</button>
</form>

<a href="/Pharmacie_S/Views/clients/index_clients.php" class="back-link-gray">Retour à la liste des clients</a>

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
    if (!/^[A-Za-zÀ-ÿ\s\-]+$/.test(value)) {
        errorElement.textContent = 'Le nom ne doit contenir que des lettres, espaces et tirets';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

function validatePrenom(input) {
    const errorElement = document.getElementById('prenom-error');
    const value = sanitizeInput(input.value.trim());
    
    if (value === '') {
        errorElement.textContent = 'Le prénom est obligatoire';
        return false;
    }
    if (value.length > 100) {
        errorElement.textContent = 'Le prénom ne doit pas dépasser 100 caractères';
        return false;
    }
    if (!/^[A-Za-zÀ-ÿ\s\-]+$/.test(value)) {
        errorElement.textContent = 'Le prénom ne doit contenir que des lettres, espaces et tirets';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

function validateEmail(input) {
    const errorElement = document.getElementById('email-error');
    const value = sanitizeInput(input.value.trim());
    
    if (value === '') {
        errorElement.textContent = 'L\'email est obligatoire';
        return false;
    }
    if (value.length > 255) {
        errorElement.textContent = 'L\'email ne doit pas dépasser 255 caractères';
        return false;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        errorElement.textContent = 'L\'email n\'est pas valide';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

function validateTelephone(input) {
    const errorElement = document.getElementById('telephone-error');
    const value = sanitizeInput(input.value.trim());
    
    if (value !== '' && !/^[0-9\+\-\s]{10,15}$/.test(value)) {
        errorElement.textContent = 'Format de téléphone invalide';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

function validateAdresse(input) {
    const errorElement = document.getElementById('adresse-error');
    const value = sanitizeInput(input.value.trim());
    
    if (value.length > 500) {
        errorElement.textContent = 'L\'adresse ne doit pas dépasser 500 caractères';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

function validateCommentaire(input) {
    const errorElement = document.getElementById('commentaire-error');
    const value = sanitizeInput(input.value.trim());
    
    if (value.length > 1000) {
        errorElement.textContent = 'Le commentaire ne doit pas dépasser 1000 caractères';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

function validateCarteVitale(input) {
    const errorElement = document.getElementById('carte-vitale-error');
    const value = sanitizeInput(input.value.trim());
    
    if (value !== '' && !/^[0-9]{15}$/.test(value)) {
        errorElement.textContent = 'Le numéro doit contenir exactement 15 chiffres';
        return false;
    }
    errorElement.textContent = '';
    return true;
}

document.getElementById('clientForm').addEventListener('submit', function(event) {
    let isValid = true;
    
    if (!validateNom(document.getElementById('nom'))) isValid = false;
    if (!validatePrenom(document.getElementById('prenom'))) isValid = false;
    if (!validateEmail(document.getElementById('email'))) isValid = false;
    if (!validateTelephone(document.getElementById('telephone'))) isValid = false;
    if (!validateAdresse(document.getElementById('adresse'))) isValid = false;
    if (!validateCommentaire(document.getElementById('commentaire'))) isValid = false;
    if (!validateCarteVitale(document.getElementById('numero_carte_vitale'))) isValid = false;

    if (!isValid) {
        event.preventDefault();
    }
});

// Protection XSS supplémentaire pour tous les champs de texte
document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], textarea').forEach(input => {
    input.addEventListener('input', function() {
        this.value = sanitizeInput(this.value);
    });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>