</main>
<footer>
    <img src="/Pharmacie_S/images/logo_pharmacie.png" alt="Logo Pharmacie" style="height: 40px; margin-right: 10px;">
    <button class="menu-toggle" onclick="toggleMenu()">Menu</button>
    <nav>
        <ul>
            <li><a href="/Pharmacie_S/index.php">Accueil</a></li> <!-- Lien Accueil -->
            <li><a href="/Pharmacie_S/PHP/auth/logout.php">Déconnexion</a></li>
            <li><a href="/Pharmacie_S/Views/ventes/create_vente.php">Ventes</a></li>
            <li><a href="/Pharmacie_S/Views/produits/index_produits.php">Produits</a></li>
            <li><a href="/Pharmacie_S/Views/clients/index_clients.php">Clients</a></li>
            <li><a href="/Pharmacie_S/Views/inventaire/index.php">Inventaire</a></li>
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
</footer>
<script src="/Pharmacie_S/js/mobile.js"></script>
<script>
    function toggleMenu() {
        const nav = document.querySelector('footer nav ul');
        nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
    }
</script>
</body>

</html>