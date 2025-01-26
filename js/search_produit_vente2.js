class ProduitSearch {
    constructor(searchInput, produitSelect) {
      this.searchInput = searchInput;
      this.produitSelect = produitSelect;
      this.searchTimeout = null;
      this.resultsDiv = null;
      this.DEBOUNCE_DELAY = 300;
      this.MIN_SEARCH_LENGTH = 2;
  
      if (!this.searchInput || !this.produitSelect) {
        console.error("Éléments requis non trouvés");
        return;
      }
  
      this.initializeEventListeners();
    }
    createResultsDiv() {
      if (!this.resultsDiv) {
        this.resultsDiv = document.createElement("div");
        this.resultsDiv.className = "produit-results";
        this.searchInput.parentNode.appendChild(this.resultsDiv);
      }
    }
  
    async searchProduits(searchText) {
      try {
        const response = await fetch(
          "/Pharmacie_S/PHP/produits/search_produits.php",
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
  
        const produits = await response.json();
        this.displayResults(produits);
      } catch (error) {
        console.error("Erreur lors de la recherche:", error);
        this.showError("Erreur lors de la recherche");
      }
    }
  
    displayResults(produits) {
      this.resultsDiv.style.display = "block";
      this.resultsDiv.innerHTML = "";
  
      if (produits.length === 0) {
        this.resultsDiv.innerHTML = `
                  <div class="produit-result-item no-results">
                      Aucun produit trouvé.
                  </div>
              `;
        return;
      }
  
      produits.forEach((produit) => {
        const div = document.createElement("div");
        div.className = "produit-result-item";
        if (produit.stock < 5 && produit.stock > 0) {
          div.className += " stock-warning";
        }
        div.textContent = produit.nom;
        div.dataset.id = produit.produit_id;
        div.dataset.prescription = produit.prescription;
        div.dataset.prix = produit.prix_vente_ht;
        div.dataset.taux_remboursement = produit.taux_remboursement;
        div.dataset.stock = produit.stock;
        this.resultsDiv.appendChild(div);
      });
    }
  
    showError(message) {
      this.resultsDiv.innerHTML = `
              <div class="produit-result-item error">
                  ${message}
              </div>
          `;
    }
  
    handleSearchInput() {
      clearTimeout(this.searchTimeout);
      const searchText = this.searchInput.value.trim();
  
      this.createResultsDiv();
  
      if (searchText.length === 0) {
        this.resultsDiv.style.display = "none";
        return;
      }
  
      if (searchText.length < this.MIN_SEARCH_LENGTH) {
        this.resultsDiv.style.display = "none";
        return;
      }
  
      this.searchTimeout = setTimeout(() => {
        this.searchProduits(searchText);
      }, this.DEBOUNCE_DELAY);
    }
  
    handleProduitSelection(e) {
      const target = e.target;
      if (
        target.classList.contains("produit-result-item") &&
        !target.classList.contains("no-results")
      ) {
        const produitId = target.dataset.id;
        const produitNom = target.textContent.trim();
  
        // Mettre à jour le select et la barre de recherche
        this.produitSelect.value = produitId;
        this.searchInput.value = produitNom;
        this.resultsDiv.style.display = "none";
  
        // Déclencher l'événement change sur le select
        this.produitSelect.dispatchEvent(new Event("change"));
      }
    }
  
    handleClickOutside(e) {
      if (
        !this.searchInput.contains(e.target) &&
        this.resultsDiv &&
        !this.resultsDiv.contains(e.target)
      ) {
        this.resultsDiv.style.display = "none";
      }
    }
  
    clearSearch() {
      this.searchInput.value = "";
      if (this.resultsDiv) {
        this.resultsDiv.style.display = "none";
      }
    }
  
    initializeEventListeners() {
      this.searchInput.addEventListener(
        "input",
        this.handleSearchInput.bind(this)
      );
  
      document.addEventListener("click", (e) => {
        if (e.target.classList.contains("produit-result-item")) {
          this.handleProduitSelection(e);
        }
        this.handleClickOutside(e);
      });
  
      this.produitSelect.addEventListener("change", () => {
        this.clearSearch();
      });
  
      // Gestionnaire de focus
      this.searchInput.addEventListener("focus", () => {
        const searchText = this.searchInput.value.trim();
        if (searchText.length >= this.MIN_SEARCH_LENGTH) {
          this.createResultsDiv();
          this.searchProduits(searchText);
        }
      });
    }
  }
  
  // Initialisation
  document.addEventListener("DOMContentLoaded", () => {
    const produitSearch = new ProduitSearch();
  });
  