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

<body>
    <header>
        <img src="/Pharmacie_S/images/logo_pharmacie.png" alt="Logo Pharmacie" style="height: 40px; margin-right: 10px;">
        <button class="menu-toggle" onclick="toggleMenu()">Menu</button>
        <nav>
            <ul>
                <li style="margin-right: 50px;">
                    <a href="#" onclick="showLogoutModal()" style="background-color: #cc5500 !important; color: white !important; padding: 8px 16px; border-radius: 5px; font-weight: bold; text-decoration: none; transition: all 0.3s ease; border: 2px solid #cc5500;" onmouseover="this.style.backgroundColor='#b54800'; this.style.borderColor='#b54800'; this.style.transform='translateY(-1px)'" onmouseout="this.style.backgroundColor='#cc5500'; this.style.borderColor='#cc5500'; this.style.transform='translateY(0)'">Déconnexion</a>
                </li>    
                <li><a href="/Pharmacie_S/index.php">Accueil</a></li>
                <li><a href="/Pharmacie_S/Views/ventes/create_vente.php">Ventes</a></li>
                <li><a href="/Pharmacie_S/Views/produits/index_produits.php">Produits</a></li>
                <li><a href="/Pharmacie_S/Views/clients/index_clients.php">Clients</a></li>
                <li><a href="/Pharmacie_S/Views/inventaire/index_inventaire.php">Inventaire</a></li>
                <li><a href="/Pharmacie_S/Views/a_commander/index_a_commander.php">À Commander</a></li>
                <li><a href="/Pharmacie_S/Views/mes_documents/index_mes_documents.php">Mes Documents</a></li>
                <?php if (isset($role) && $role == 1): ?>
                    <li><a href="/Pharmacie_S/Views/comptabilité/index_comptabilité.php">Comptabilité</a></li>
                    <li><a href="/Pharmacie_S/Views/parametre/edit_TVA.php">TVA</a></li>
                    <li><a href="/Pharmacie_S/Views/commande/index_commande.php">Passer Commande</a></li>
                    <li><a href="/Pharmacie_S/Views/users/manage_users.php">Gérer utilisateurs</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Modal de confirmation de déconnexion -->
    <div id="logoutModal" class="logout-modal">
        <div class="logout-modal-content">
            <h3>Confirmation de déconnexion</h3>
            <p>Êtes-vous sûr de vouloir vous déconnecter ?</p>
            <div class="logout-modal-buttons">
                <button class="confirm-logout" onclick="confirmLogout()">Oui, me déconnecter</button>
                <button class="cancel-logout" onclick="hideLogoutModal()">Annuler</button>
            </div>
        </div>
    </div>

    <main>