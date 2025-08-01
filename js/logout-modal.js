function showLogoutModal() {
    document.getElementById('logoutModal').style.display = 'flex'; // ← Changé en 'flex'
}

function hideLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}

function confirmLogout() {
    window.location.href = '/Pharmacie_S/PHP/auth/logout.php';
}

// Fermer le modal si on clique à l'extérieur
window.onclick = function(event) {
    const modal = document.getElementById('logoutModal');
    if (event.target === modal) {
        hideLogoutModal();
    }
}

// Fonction pour le menu mobile
function toggleMenu() {
    const nav = document.querySelector('nav ul');
    nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
}