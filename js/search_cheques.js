import { updateTableWithPHPData, performSearch } from './chequeUtils.js';

document.addEventListener("DOMContentLoaded", function () {
    console.log("Script de recherche de chèques chargé");

    const table = document.getElementById("cheques-table");
    const tbody = table.querySelector("tbody");
    const searchInput = document.getElementById("search-input");
    const searchCriteria = document.getElementById("search-criteria");
    const etatFilter = document.getElementById("etat");
    const includeDeletedCheckbox = document.getElementById("include_deleted");
    const dateDebutInput = document.getElementById("date_debut");
    const dateFinInput = document.getElementById("date_fin");

    if (
        !table ||
        !tbody ||
        !searchInput ||
        !searchCriteria ||
        !etatFilter ||
        !includeDeletedCheckbox ||
        !dateDebutInput ||
        !dateFinInput
    ) {
        console.error("Un ou plusieurs éléments HTML nécessaires n'ont pas été trouvés");
        return;
    }

    // Création de l'élément pour les résultats d'autocomplétion
    const autocompleteResults = document.createElement("div");
    autocompleteResults.id = "autocomplete-results";
    autocompleteResults.style.position = "absolute";
    autocompleteResults.style.backgroundColor = "white";
    autocompleteResults.style.border = "1px solid #ddd";
    autocompleteResults.style.zIndex = "1000";
    autocompleteResults.style.maxHeight = "200px";
    autocompleteResults.style.overflowY = "auto";
    autocompleteResults.style.width = `${searchInput.offsetWidth}px`;
    autocompleteResults.style.boxShadow = "0 2px 4px rgba(0,0,0,0.1)";
    autocompleteResults.style.display = "none";
    searchInput.parentNode.appendChild(autocompleteResults);

    function showAutocomplete() {
        autocompleteResults.style.display = "block";
        adjustAutocompletePosition();
    }

    function hideAutocomplete() {
        autocompleteResults.style.display = "none";
    }

    function updateTable(suggestions) {
        if (!suggestions) {
            console.error("Suggestions non définies");
            return;
        }
        console.log("Mise à jour du tableau avec les suggestions:", suggestions);
        updateTableWithPHPData(suggestions);
    }

    let searchTimeout;

    searchInput.addEventListener("input", function () {
        console.log("Événement input déclenché");
        clearTimeout(searchTimeout); // Effacez le délai précédent
        const searchTerm = this.value;
        searchTimeout = setTimeout(() => {
            if (searchTerm.length >= 2) {
                console.log("Appel de performSearch()");
                performSearch(searchInput, searchCriteria, etatFilter, includeDeletedCheckbox, dateDebutInput, dateFinInput)
                    .then(data => {
                        if (data && data.length > 0) {
                            console.log("Données reçues :", data); // Vérifiez les données ici
                            updateTable(data);
                            autocompleteResults.innerHTML = "";
                            data.forEach(function (suggestion) {
                                const div = document.createElement("div");
                                div.textContent = `${suggestion.numero_cheque} - ${suggestion.client_nom} ${suggestion.client_prenom}`;
                                div.style.padding = "5px 10px";
                                div.style.cursor = "pointer";
                                div.addEventListener("click", function () {
                                    searchInput.value = suggestion.numero_cheque;
                                    updateTable([suggestion]);
                                    hideAutocomplete();
                                });
                                autocompleteResults.appendChild(div);
                            });
                            showAutocomplete();
                        } else {
                            console.log("Aucune donnée reçue");
                            updateTable([]); // Mettre à jour le tableau avec un tableau vide si aucune donnée n'est reçue
                        }
                    })
                    .catch(error => {
                        console.error("Erreur lors de la recherche:", error);
                        updateTable([]); // Mettre à jour le tableau avec un tableau vide en cas d'erreur
                        hideAutocomplete();
                    });
            } else {
                loadInitialData(); // Charge les données initiales si le terme de recherche est vide
                hideAutocomplete();
            }
        }, 300); // Délai de 300 ms
    });

    // Charger les données initiales
    function loadInitialData() {
        console.log("Chargement des données initiales");
        console.log("Données initiales:", window.currentCheques);
        if (window.currentCheques) {
            updateTable(window.currentCheques);
        } else {
            console.error("Données initiales non définies");
        }
    }

    // Événements pour la recherche et le filtrage
    function applyFilters() {
        console.log("Application des filtres");
        const isAnyFilterActive = etatFilter.value || dateDebutInput.value || dateFinInput.value || includeDeletedCheckbox.checked;

        if (!isAnyFilterActive) {
            loadInitialData(); // Affiche les données initiales si aucun filtre n'est actif
        } else {
            performSearch(searchInput, searchCriteria, etatFilter, includeDeletedCheckbox, dateDebutInput, dateFinInput)
                .then(data => {
                    if (data && data.length > 0) {
                        console.log("Données filtrées reçues :", data);
                        updateTable(data);
                    } else {
                        console.log("Aucune donnée filtrée reçue");
                        updateTable([]); // Mettre à jour le tableau avec un tableau vide si aucune donnée n'est reçue
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de l'application des filtres:", error);
                    updateTable([]); // Mettre à jour le tableau avec un tableau vide en cas d'erreur
                });
        }
    }

    searchCriteria.addEventListener("change", applyFilters);
    etatFilter.addEventListener("change", applyFilters);
    includeDeletedCheckbox.addEventListener("change", applyFilters);
    dateDebutInput.addEventListener("change", applyFilters);
    dateFinInput.addEventListener("change", applyFilters);

    // Fermer l'autocomplétion si on clique en dehors
    document.addEventListener("click", function (e) {
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
    window.addEventListener("resize", adjustAutocompletePosition);

    // Ajuster la position initiale
    adjustAutocompletePosition();

    // Charger les données initiales
    loadInitialData();
});