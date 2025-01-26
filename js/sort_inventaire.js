document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("inventory-table");
    const headers = table.querySelectorAll("th");
    const tbody = table.querySelector("tbody");

    // Obtenir les paramètres actuels de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    let currentSort = urlParams.get('sort') || "produit_id";
    let currentDirection = urlParams.get('direction') || "asc";
    let currentPage = urlParams.get('page') || 1;

    // Ajouter les écouteurs d'événements pour le tri
    headers.forEach(function (header) {
        if (header.dataset.column) {
            // Ajouter l'icône de tri initiale
            if (!header.querySelector(".fas")) {
                const icon = document.createElement("i");
                icon.className = "fas fa-sort ml-1";
                header.appendChild(icon);
            }

            // Appliquer la classe de tri active
            if (header.dataset.column === currentSort) {
                updateSortIcon(header, currentDirection);
            }

            header.addEventListener("click", async function () {
                const columnName = header.dataset.column;

                // Mettre à jour la direction du tri
                if (columnName === currentSort) {
                    currentDirection = currentDirection === "asc" ? "desc" : "asc";
                } else {
                    currentSort = columnName;
                    currentDirection = "asc";
                }

                try {
                    tbody.style.opacity = "0.5";

                    const response = await axios.get(
                        "/Pharmacie_S/PHP/inventaire/sort_inventaire.php",
                        {
                            params: {
                                sort: columnName,
                                direction: currentDirection,
                                page: currentPage
                            },
                        }
                    );

                    if (response.data && response.data.data) {
                        // Mettre à jour le tableau
                        updateTableContent(response.data.data);
                        
                        // Mettre à jour les icônes de tri
                        headers.forEach(h => {
                            if (h.dataset.column) {
                                if (h === header) {
                                    updateSortIcon(h, currentDirection);
                                } else {
                                    const icon = h.querySelector(".fas");
                                    icon.className = "fas fa-sort ml-1";
                                }
                            }
                        });

                        // Mettre à jour l'URL
                        const url = new URL(window.location);
                        url.searchParams.set("sort", columnName);
                        url.searchParams.set("direction", currentDirection);
                        url.searchParams.set("page", "1"); // Retour à la première page lors du tri
                        window.history.pushState({}, "", url);

                        // Mettre à jour les liens de pagination
                        updatePaginationLinks(columnName, currentDirection);
                    }
                } catch (error) {
                    console.error("Erreur lors du tri:", error);
                } finally {
                    tbody.style.opacity = "1";
                }
            });
        }
    });

    function updateTableContent(data) {
        tbody.innerHTML = "";
        data.forEach((item) => {
            const row = `
                <tr data-id="${item.produit_id}">
                    <td>${item.produit_id}</td>
                    <td>${item.nom}</td>
                    <td class="${parseInt(item.stock) <= parseInt(item.alerte) ? 'stock-warning' : ''}">${item.stock}</td>
                    <td>${item.alerte || ""}</td>
                    <td>${item.declencher_alerte}</td>
                    <td>
                        <a href="/Pharmacie_S/Views/inventaire/edit_inventaire.php?id=${item.produit_id}">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="/Pharmacie_S/Views/inventaire/delete_inventaire.php?id=${item.produit_id}">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    function updateSortIcon(header, direction) {
        const icon = header.querySelector(".fas");
        icon.className = `fas fa-sort-${direction === "asc" ? "up" : "down"} ml-1`;
    }

    function updatePaginationLinks(sortColumn, sortDirection) {
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            const url = new URL(link.href);
            url.searchParams.set('sort', sortColumn);
            url.searchParams.set('direction', sortDirection);
            link.href = url.toString();
        });
    }
});