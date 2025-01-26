// Détecter le scroll horizontal pour toutes les tables
document.addEventListener('DOMContentLoaded', function() {
    const tableResponsive = document.querySelectorAll('.table-responsive');
    tableResponsive.forEach(function(table) {
        if (table.scrollWidth > table.clientWidth) {
            table.classList.add('has-scroll');
        }
    });
    
    // Cacher l'indicateur après le premier scroll
    tableResponsive.forEach(function(table) {
        table.addEventListener('scroll', function() {
            this.classList.remove('has-scroll');
        });
    });
});