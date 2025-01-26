<?php
$pageTitle = "Créer utilisateur";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/users/create_user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<script>
    document.body.className = "manage-users-page";
</script>
<h1>Inscription</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/PHP/users/create_user.php" method="post" class="register-form">
    <label for="nom">Nom:</label>
    <input type="text" id="nom" name="nom" required>
    
    <label for="prenom">Prénom:</label>
    <input type="text" id="prenom" name="prenom" required>
    
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    
    <label for="password">Mot de passe:</label>
    <div class="password-container">
        <input type="password" id="password" name="password" required>
        <button type="button" id="togglePassword">Afficher</button>
    </div>
    
    <label for="role_id">Rôle:</label>
    <select id="role_id" name="role_id" required>
        <?php foreach ($roles as $role): ?>
            <option value="<?php echo htmlspecialchars($role['role_id']); ?>">
                <?php echo htmlspecialchars($role['nom']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <div class="button-group">
        <button type="submit" name="action" value="create">S'inscrire</button>
        <button type="submit" name="action" value="create_and_send_email">S'inscrire et envoyer un e-mail</button>
    </div>
</form>

<a href="/Pharmacie_S/Views/users/manage_users.php" class="back-link-gray">Retour à gestion des utilisateurs</a>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function(e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye / eye slash icon
            this.textContent = type === 'password' ? 'Afficher' : 'Masquer';
        });
    });
</script>

<style>
    .button-group {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }
    .button-group button {
        flex: 1;
        margin: 0 5px;
    }
</style>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>