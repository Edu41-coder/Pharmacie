class ProduitSearch {
    constructor(searchInput, produitSelect) {
        this.searchInput = searchInput;
        this.produitSelect = produitSelect;
        this.searchTimeout = null;
        this.resultsDiv = null;
        this.DEBOUNCE_DELAY = 300;
        this.MIN_SEARCH_LENGTH = 2;
        
        // Créer un conteneur de résultats spécifique à cette instance
        this.resultsContainer = document.createElement("div");
        this.resultsContainer.className = "produit-results-container";
        this.searchInput.parentNode.appendChild(this.resultsContainer);

        if (!this.searchInput || !this.produitSelect) {
            console.error("Éléments requis non trouvés");
            return;
        }

        console.log("ProduitSearch initialized for:", this.searchInput, this.produitSelect);
        this.initializeEventListeners();
    }

    createResultsDiv() {
        if (!this.resultsDiv) {
            this.resultsDiv = document.createElement("div");
            this.resultsDiv.className = "produit-results";
            this.resultsContainer.appendChild(this.resultsDiv);
            console.log("Results div created");
        }
    }

    getSelectedProducts() {
        const currentLine = this.searchInput.closest(".produit-ligne");
        const selectedProducts = Array.from(document.querySelectorAll(".produit-ligne"))
            .filter(ligne => ligne !== currentLine)
            .map(ligne => ligne.querySelector(".produit-select"))
            .filter(select => select && select.value)
            .map(select => select.value);

        console.log("Selected products (excluding current line):", selectedProducts);
        return selectedProducts;
    }

    async searchProduits(searchText) {
        const selectedProducts = this.getSelectedProducts();
        console.log("Produits sélectionnés avant la requête:", selectedProducts);

        try {
            const response = await fetch("/Pharmacie_S/PHP/produits/search_produits.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `search=${encodeURIComponent(searchText)}&selectedProducts=${encodeURIComponent(JSON.stringify(selectedProducts))}`,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const produits = await response.json();
            console.log("Produits reçus:", produits);
            this.displayResults(produits);
        } catch (error) {
            console.error("Erreur lors de la recherche de produits:", error);
            this.showError("Erreur lors de la recherche");
        }
    }

    displayResults(produits) {
        this.resultsDiv.style.display = "block";
        this.resultsDiv.innerHTML = "";

        const selectedProducts = this.getSelectedProducts();
        const availableProduits = produits.filter(
            produit => !selectedProducts.includes(produit.produit_id.toString())
        );

        console.log("Available produits after filtering:", availableProduits);

        if (availableProduits.length === 0) {
            this.resultsDiv.innerHTML = `
                <div class="produit-result-item no-results">
                    Aucun produit disponible pour la sélection.
                </div>
            `;
            return;
        }

        availableProduits.forEach(produit => {
            const div = document.createElement("div");
            div.className = "produit-result-item";
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
        if (!this.resultsDiv) this.createResultsDiv();
        this.resultsDiv.innerHTML = `
            <div class="produit-result-item error">
                ${message}
            </div>
        `;
    }

    handleSearchInput(event) {
        clearTimeout(this.searchTimeout);
        const searchText = event.target.value.trim();
        console.log("Search input changed:", searchText);

        this.createResultsDiv();

        if (searchText.length === 0 || searchText.length < this.MIN_SEARCH_LENGTH) {
            this.resultsDiv.style.display = "none";
            return;
        }

        this.searchTimeout = setTimeout(() => {
            this.searchProduits(searchText);
        }, this.DEBOUNCE_DELAY);
    }

    handleProduitSelection(event) {
        // Vérifier si l'événement provient de notre conteneur de résultats
        if (!this.resultsContainer.contains(event.target)) {
            return;
        }

        const target = event.target;
        if (!target.classList.contains("produit-result-item") || 
            target.classList.contains("no-results")) {
            return;
        }

        const produitId = target.dataset.id.toString();
        console.log("Produit selected:", produitId);

        this.produitSelect.value = produitId;
        this.searchInput.value = target.textContent.trim();

        if (this.resultsDiv) {
            this.resultsDiv.style.display = "none";
        }

        this.produitSelect.dispatchEvent(new Event("change"));
        
        if (typeof window.updateAvailableProducts === "function") {
            window.updateAvailableProducts();
        }
    }

    clearSearch() {
        this.searchInput.value = "";
        if (this.resultsDiv) {
            this.resultsDiv.style.display = "none";
        }
    }

    syncWithSelect() {
        const selectedOption = this.produitSelect.options[this.produitSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            this.searchInput.value = selectedOption.textContent
                .trim()
                .replace(/\(Stock:.*\)/, "")
                .trim();
        } else {
            this.searchInput.value = "";
        }
    }

    initializeEventListeners() {
        this.searchInput.addEventListener("input", this.handleSearchInput.bind(this));

        // Attacher l'événement click au conteneur de résultats au lieu du document
        this.resultsContainer.addEventListener("click", (event) => {
            if (event.target.classList.contains("produit-result-item")) {
                this.handleProduitSelection(event);
            }
        });

        // Gérer le clic en dehors des résultats
        document.addEventListener("click", (event) => {
            if (!this.resultsContainer.contains(event.target) && 
                !this.searchInput.contains(event.target)) {
                if (this.resultsDiv) {
                    this.resultsDiv.style.display = "none";
                }
            }
        });

        this.produitSelect.addEventListener("change", this.syncWithSelect.bind(this));

        this.searchInput.addEventListener("focus", () => {
            const searchText = this.searchInput.value.trim();
            if (searchText.length >= this.MIN_SEARCH_LENGTH) {
                this.createResultsDiv();
                this.searchProduits(searchText);
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".produit-ligne").forEach((ligne) => {
        const searchInput = ligne.querySelector(".produit-search");
        const produitSelect = ligne.querySelector(".produit-select");
        if (searchInput && produitSelect) {
            new ProduitSearch(searchInput, produitSelect);
        }
    });
});