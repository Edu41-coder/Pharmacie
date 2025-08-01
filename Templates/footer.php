<?php
    // Footer content
?>
</main>
<footer>
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
</footer>
<script src="/Pharmacie_S/js/mobile.js"></script>
<script src="/Pharmacie_S/js/logout-modal.js"></script>
<script>
    function toggleMenu() {
        const nav = document.querySelector('footer nav ul');
        nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
    }
</script>
</body>
</html>