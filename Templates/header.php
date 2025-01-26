<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/PHP/auth/header.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Pharmacie - Accueil'; ?></title>
    <link rel="stylesheet" href="/Pharmacie_S/css/styles.css"> 
    <?php
    if (isset($additionalHeadContent)) {
        echo $additionalHeadContent;
    }
    ?>
</head>
<script>
    function toggleMenu() {
        const nav = document.querySelector('nav ul');
        nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
    }
</script>

<body>
    <header>
        <img src="/Pharmacie_S/images/logo_pharmacie.png" alt="Logo Pharmacie" style="height: 40px; margin-right: 10px;">
        <button class="menu-toggle" onclick="toggleMenu()">Menu</button>
        <nav>
            <ul>
                <li><a href="/Pharmacie_S/index.php">Accueil</a></li> <!-- Lien Accueil -->
                <li><a href="/Pharmacie_S/PHP/auth/logout.php">Déconnexion</a></li>
                <li><a href="/Pharmacie_S/Views/ventes/create_vente.php">Ventes</a></li>
                <li><a href="/Pharmacie_S/Views/produits/index_produits.php">Produits</a></li>
                <li><a href="/Pharmacie_S/Views/clients/index_clients.php">Clients</a></li>
                <li><a href="/Pharmacie_S/Views/inventaire/index_inventaire.php">Inventaire</a></li>
                <li><a href="/Pharmacie_S/Views/a_commander/index_a_commander.php">À Commander</a></li>
                <li><a href="/Pharmacie_S/Views/mes_documents/index_mes_documents.php">Mes Documents</a></li>
                <?php if ($role == 1): // Admin 
                ?>
                    <li><a href="/Pharmacie_S/Views/comptabilité/index_comptabilité.php">Comptabilité</a></li>
                    <li><a href="/Pharmacie_S/Views/parametre/edit_TVA.php">TVA</a></li>
                    <li><a href="/Pharmacie_S/Views/commande/index_commande.php">Passer Commande</a></li>
                    <li><a href="/Pharmacie_S/Views/users/manage_users.php">Gérer utilisateurs</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>