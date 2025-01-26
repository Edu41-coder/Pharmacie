class ClientSearch {
    constructor() {
      this.searchInput = document.getElementById("client-search");
      this.clientSelect = document.getElementById("client");
      this.searchTimeout = null;
      this.resultsDiv = null;
      this.DEBOUNCE_DELAY = 300;
      this.MIN_SEARCH_LENGTH = 2;
  
      if (!this.searchInput || !this.clientSelect) {
        console.error("Éléments requis non trouvés");
        return;
      }
  
      this.initializeEventListeners();
    }
  
    createResultsDiv() {
      if (!this.resultsDiv) {
        this.resultsDiv = document.createElement("div");
        this.resultsDiv.className = "client-results";
        this.searchInput.parentNode.appendChild(this.resultsDiv);
      }
    }
  
    addClientPassage() {
      this.resultsDiv.innerHTML = `
              <div class="client-result-item client-passage" data-id="0">
                  Client de passage
              </div>
          `;
    }
  
    async searchClients(searchText) {
      try {
        console.log("Recherche pour:", searchText);
        const response = await fetch(
          "/Pharmacie_S/PHP/clients/search_clients.php",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `search=${encodeURIComponent(searchText)}`,
          }
        );
  
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
  
        const clients = await response.json();
        this.displayResults(clients);
      } catch (error) {
        console.error("Erreur lors de la recherche:", error);
        this.showError("Erreur lors de la recherche");
      }
    }
  
    displayResults(clients) {
      this.resultsDiv.style.display = "block";
      this.resultsDiv.innerHTML = "";
  
      // Toujours ajouter l'option "Client de passage"
      this.addClientPassage();
      // Vérifiez si des clients ont été trouvés
      if (clients.length === 0) {
        this.resultsDiv.innerHTML += `
        <div class="client-result-item no-results">
            Aucun client trouvé.
        </div>
    `;
        return;
      }
      // Ajouter les résultats de la recherche
      clients.forEach((client) => {
        const div = document.createElement("div");
        div.className = "client-result-item";
        if (client.has_warning) {
          div.className += " warning";
        }
        div.textContent = client.display;
        div.dataset.id = client.client_id;
        this.resultsDiv.appendChild(div);
      });
    }
  
    showError(message) {
      this.resultsDiv.innerHTML = `
              <div class="client-result-item error">
                  ${message}
              </div>
          `;
    }
  
    handleSearchInput() {
      clearTimeout(this.searchTimeout);
      const searchText = this.searchInput.value.trim();
  
      this.createResultsDiv();
  
      if (searchText.length === 0) {
        this.resultsDiv.style.display = "block";
        this.addClientPassage();
        return;
      }
  
      if (searchText.length < this.MIN_SEARCH_LENGTH) {
        this.resultsDiv.style.display = "none";
        return;
      }
  
      this.searchTimeout = setTimeout(() => {
        this.searchClients(searchText);
      }, this.DEBOUNCE_DELAY);
    }
  
    handleClientSelection(e) {
      if (e.target.classList.contains("client-result-item")) {
        const clientId = e.target.dataset.id;
        const clientName = e.target.textContent.trim();
  
        this.clientSelect.value = clientId;
        this.searchInput.value = clientId === "0" ? "" : clientName;
        this.resultsDiv.style.display = "none";
  
        // Déclencher l'événement change
        this.clientSelect.dispatchEvent(new Event("change"));
      }
    }
    handleClickOutside(e) {
      if (
        !this.searchInput.contains(e.target) &&
        this.resultsDiv &&
        !this.resultsDiv.contains(e.target)
      ) {
        if (this.resultsDiv) {
          this.resultsDiv.style.display = "none";
        }
      }
    }
    initializeEventListeners() {
      // Gestionnaire de recherche
      this.searchInput.addEventListener(
        "input",
        this.handleSearchInput.bind(this)
      );
  
      // Gestionnaire de sélection et click outside
      document.addEventListener("click", (e) => {
        if (e.target.classList.contains("client-result-item")) {
          this.handleClientSelection(e);
        }
        this.handleClickOutside(e);
      });
  
      // Gestionnaire de focus
      this.searchInput.addEventListener("focus", () => {
        if (this.searchInput.value.length === 0) {
          this.createResultsDiv();
          this.resultsDiv.style.display = "block";
          this.addClientPassage();
        }
      });
    }
  }
  
  // Initialisation
  document.addEventListener("DOMContentLoaded", () => {
    const clientSearch = new ClientSearch();
  });
  