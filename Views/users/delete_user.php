<?php
$pageTitle = "Supprimer utilisateur";
$additionalHeadContent = <<<EOT
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.className = "manage-users-page";
        });

        function confirmDeletion() {
            return confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?");
        }
    </script>
EOT;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/users/delete_user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<h1>Supprimer utilisateur</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<form action="/Pharmacie_S/PHP/users/delete_user.php" method="post" class="register-form" onsubmit="return confirmDeletion();">
    <label for="user_id">Sélectionner un utilisateur à supprimer:</label>
    <select id="user_id" name="user_id" required>
        <?php foreach ($users as $user): ?>
            <option value="<?php echo htmlspecialchars($user['user_id']); ?>">
                <?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom'] . ' - ' . $user['email'] . ' - ' . $user['role']['nom']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Supprimer</button>
</form>
<a href="/Pharmacie_S/Views/users/manage_users.php" class="back-link-gray">Retour à gestion des utilisateurs</a>
</div>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>