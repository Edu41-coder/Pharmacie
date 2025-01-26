<?php
$pageTitle = "gestion utilisateurs";
$additionalHeadContent = <<<EOT
    <link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
EOT;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/users/manage_users.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
?>

<head>
    <link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
</head>
<script>
    document.body.className = "manage-users-page";
</script>
<div class="container">
    <h1>Gérer les utilisateurs</h1>
    <div>
        <a href="/Pharmacie_S/Views/users/create_user.php" class="back-link"><i class="fas fa-plus"></i>Créer un compte</a>
        <a href="/Pharmacie_S/Views/users/modify_user.php" class="back-link"><i class="fas fa-edit"></i>Modifier utilisateur</a>
        <a href="/Pharmacie_S/Views/users/delete_user.php" class="back-link"><i class="fas fa-trash"></i>Supprimer utilisateur</a>
        <a href="/Pharmacie_S/Views/users/list_user.php" class="back-link"><i class="fas fa-eye"></i>Liste utilisateurs</a>
    </div>
    <a href="/Pharmacie_S/index.php" class="back-link-gray">Retour à l'index admin</a>
</div>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php';
?>