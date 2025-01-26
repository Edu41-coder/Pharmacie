<?php
$pageTitle = "modifier utilisateur";
$additionalHeadContent = <<<EOT
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.className = "manage-users-page";
        })
    </script>
EOT;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/users/modify_user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

$auth = new Authentification();
$csrfToken = $auth->getCsrfToken();
error_log("CSRF Token généré/récupéré dans modify_user.php (vue) : " . $csrfToken);
?>

<h1>Modifier utilisateur</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/PHP/users/modify_user.php" method="post" class="register-form">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <?php error_log("CSRF Token inséré dans le formulaire : " . $csrfToken); ?>
    <label for="user_id">Sélectionner un utilisateur:</label>
    <select id="user_id" name="user_id" required onchange="location = this.value;">
        <option value="">-- Sélectionner un utilisateur --</option>
        <?php foreach ($users as $user): ?>
            <option value="?user_id=<?php echo htmlspecialchars($user['user_id']); ?>" <?php echo isset($selectedUser) && $selectedUser['user_id'] == $user['user_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom'] . ' - ' . $user['email'] . ' - ' . $user['role']['nom']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if ($selectedUser): ?>
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($selectedUser['user_id']); ?>">
        <label for="nom">Nom:</label>
        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($selectedUser['nom']); ?>" required>
        <label for="prenom">Prénom:</label>
        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($selectedUser['prenom']); ?>" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($selectedUser['email']); ?>" required>
        <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer):</label>
        <div class="password-container">
            <input type="password" id="password" name="password">
            <button type="button" id="togglePassword">Afficher</button>
        </div>
        <label for="role_id">Rôle:</label>
        <select id="role_id" name="role_id" required>
            <option value="1" <?php echo $selectedUser['role']['role_id'] == 1 ? 'selected' : ''; ?>>Admin</option>
            <option value="2" <?php echo $selectedUser['role']['role_id'] == 2 ? 'selected' : ''; ?>>Pharmacien</option>
            <option value="3" <?php echo $selectedUser['role']['role_id'] == 3 ? 'selected' : ''; ?>>Vendeur</option>
        </select>
        <button type="submit">Mettre à jour</button>
    <?php endif; ?>
</form>
<a href="/Pharmacie_S/Views/users/manage_users.php" class="back-link-gray">Retour à gestion des utilisateurs</a>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function(e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Afficher' : 'Masquer';
        });
    });
</script>

<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; 
error_log("Fin du chargement de la page modify_user.php (vue)");
?>