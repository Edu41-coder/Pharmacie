<?php
$pageTitle = "Liste des utilisateurs";
$additionalHeadContent = <<<EOT
<script src="/Pharmacie_S/js/sort_user.js"></script>
<style>
    th[data-sort] {
        cursor: pointer;
    }
    th.sort-asc::after {
        content: " ▲";
    }
    th.sort-desc::after {
        content: " ▼";
    }
</style>
EOT;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/users/list_user.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>
<script>
    document.body.className = "manage-users-page";
</script>

<div class="index-produits-container">
    <div class="container">
        <h1>Liste des utilisateurs</h1>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <table class="user-table">
            <thead>
                <tr>
                    <th data-sort="0">ID</th>
                    <th data-sort="1">Nom</th>
                    <th data-sort="2">Prénom</th>
                    <th data-sort="3">Email</th>
                    <th data-sort="4">Rôle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['nom']); ?></td>
                        <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']['nom']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="centered-link">
            <a href="/Pharmacie_S/Views/users/manage_users.php" class="back-link-gray">Retour à gestion des utilisateurs</a>
        </div>
    </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>