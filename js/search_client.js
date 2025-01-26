document.addEventListener('DOMContentLoaded', function () {
    console.log("Script chargé");

    const table = document.getElementById("clients-table");
    const tbody = table.querySelector("tbody");
    const searchInput = document.getElementById("search-input");
    const searchCriteria = document.getElementById("search-criteria");
    const originalRows = tbody.innerHTML;

    function updateTable(suggestions) {
        console.log("Mise à jour du tableau avec les suggestions:", suggestions);
        tbody.innerHTML = '';
        if (suggestions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8">Aucun client trouvé.</td></tr>';
        } else {
            suggestions.forEach(function (suggestion) {
                const parts = suggestion.label.split(" - ");
                const nameParts = parts[0].split(" ");
                const row = `
                    <tr>
                        <td>${suggestion.value}</td>
                        <td>${nameParts[0]}</td>
                        <td>${nameParts.slice(1).join(" ")}</td>
                        <td>${parts[1]}</td>
                        <td>${parts[2]}</td>
                        <td>${parts[3]}</td>
                        <td>${parts[4]}</td>
                        <td>
                            <a href="/Pharmacie_S/Views/clients/show_client.php?id=${suggestion.value}"><i class="fas fa-eye"></i></a>
                            <a href="/Pharmacie_S/Views/clients/edit_client.php?id=${suggestion.value}"><i class="fas fa-edit"></i></a>
                            <a href="/Pharmacie_S/Views/clients/delete_client.php?id=${suggestion.value}"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }
        hideAutocomplete(); // Cacher l'autocomplétion après la mise à jour du tableau
    }

    function performSearch() {
        const searchTerm = searchInput.value;
        const criteria = searchCriteria.value;
        console.log("Recherche avec le terme:", searchTerm, "et le critère:", criteria);

        axios.get('/Pharmacie_S/PHP/clients/autocomplete_clients.php', {
            params: {
                term: searchTerm,
                criteria: criteria
            }
        })
        .then(function (response) {
            console.log("Données reçues:", response.data);
            updateTable(response.data);
        })
        .catch(function (error) {
            console.error("Erreur Axios:", error);
            if (error.response) {
                console.error("Réponse du serveur:", error.response.data);
            }
        });
    }

    function filterTableRows() {
        const searchTerm = searchInput.value.toLowerCase();
        const criteria = searchCriteria.value;
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(function(row) {
            let text = "";
            if (criteria === "all") {
                text = row.textContent.toLowerCase();
            } else if (criteria === "name") {
                text = row.children[1].textContent.toLowerCase() + ' ' + row.children[2].textContent.toLowerCase();
            } else if (criteria === "email") {
                text = row.children[3].textContent.toLowerCase();
            } else if (criteria === "phone") {
                text = row.children[4].textContent.toLowerCase();
            } else if (criteria === "carte_vitale") {
                text = row.children[5].textContent.toLowerCase();
            }

            if (text.indexOf(searchTerm) > -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', function () {
        console.log("Événement input déclenché");
        const searchTerm = searchInput.value;
        if (searchTerm.length >= 2) {
            console.log("Appel de performSearch()");
            performSearch();
        } else {
            tbody.innerHTML = originalRows;
            hideAutocomplete(); // Cacher l'autocomplétion si le terme de recherche est trop court
        }
    });

    searchCriteria.addEventListener('change', function () {
        console.log("Critère de recherche changé");
        searchInput.value = "";
        tbody.innerHTML = originalRows;
        hideAutocomplete(); // Cacher l'autocomplétion lors du changement de critère
    });

    // Implémentation de l'autocomplétion
    const autocompleteResults = document.createElement('div');
    autocompleteResults.id = 'autocomplete-results';
    autocompleteResults.style.position = 'absolute';
    autocompleteResults.style.zIndex = '1000';
    autocompleteResults.style.backgroundColor = 'white';
    autocompleteResults.style.border = '1px solid #ddd';
    autocompleteResults.style.maxHeight = '200px';
    autocompleteResults.style.overflowY = 'auto';
    autocompleteResults.style.width = `${searchInput.offsetWidth}px`;
    autocompleteResults.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
    autocompleteResults.style.display = 'none'; // Cacher par défaut
    searchInput.parentNode.appendChild(autocompleteResults);

    function showAutocomplete() {
        autocompleteResults.style.display = 'block';
        adjustAutocompletePosition();
    }

    function hideAutocomplete() {
        autocompleteResults.style.display = 'none';
    }

    searchInput.addEventListener('input', function() {
        if (this.value.length >= 2) {
            axios.get('/Pharmacie_S/PHP/clients/autocomplete_clients.php', {
                params: {
                    term: this.value,
                    criteria: searchCriteria.value
                }
            })
            .then(function (response) {
                const suggestions = response.data;
                autocompleteResults.innerHTML = '';
                if (suggestions.length > 0) {
                    suggestions.forEach(function(suggestion) {
                        const div = document.createElement('div');
                        div.textContent = suggestion.label;
                        div.style.cursor = 'pointer';
                        div.style.padding = '5px 10px';
                        div.style.backgroundColor = 'white';
                        div.addEventListener('mouseover', function() {
                            this.style.backgroundColor = '#f0f0f0';
                        });
                        div.addEventListener('mouseout', function() {
                            this.style.backgroundColor = 'white';
                        });
                        div.addEventListener('click', function() {
                            searchInput.value = suggestion.label;
                            updateTable([suggestion]);
                            hideAutocomplete();
                        });
                        autocompleteResults.appendChild(div);
                    });
                    showAutocomplete();
                } else {
                    hideAutocomplete();
                }
            })
            .catch(function (error) {
                console.error("Erreur lors de l'autocomplétion:", error);
                hideAutocomplete();
            });
        } else {
            hideAutocomplete();
        }
    });

    // Fermer l'autocomplétion si on clique en dehors
    document.addEventListener('click', function(e) {
        if (e.target !== searchInput && e.target !== autocompleteResults) {
            hideAutocomplete();
        }
    });

    // Ajuster la position du menu d'autocomplétion
    function adjustAutocompletePosition() {
        const inputRect = searchInput.getBoundingClientRect();
        autocompleteResults.style.top = `${inputRect.bottom + window.scrollY}px`;
        autocompleteResults.style.left = `${inputRect.left + window.scrollX}px`;
        autocompleteResults.style.width = `${searchInput.offsetWidth}px`;
    }

    // Ajuster la position lors du redimensionnement de la fenêtre
    window.addEventListener('resize', adjustAutocompletePosition);

    // Ajuster la position initiale
    adjustAutocompletePosition();
});