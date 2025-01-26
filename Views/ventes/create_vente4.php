<?php
$pageTitle = "Créer une vente";
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

// Initialisation de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: /Pharmacie_S/login.php');
    exit();
}

// Initialisation des variables
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']);

// Initialisation des modèles et récupération des données
$userModel = new User();
$role = $userModel->getUserRole($_SESSION['user_id'])['role_id'];

$parametre = new Parametre();
$tva = $parametre->getParametre('TVA');

$clientModel = new Client();
$clients = $clientModel->getAllClients();

$inventaireModel = new Inventaire();
$produits = $inventaireModel->getAllInventaireProducts();

$produitModel = new Produit();
?>
<script>
    document.body.className = 'vente-page';
</script>
<script src="/Pharmacie_S/js/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialisation de tous les tooltips
        $('[data-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            container: 'body'
        });
    });
</script>
<script src="/Pharmacie_S/js/search_client_vente.js"></script>
<script src="/Pharmacie_S/js/search_produit_vente.js"></script>
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/vente.css">
<div class="container">
    <h1>Créer une nouvelle vente</h1>
    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmationModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation de vente</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir enregistrer cette vente ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="confirmVente">Confirmer</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form id="venteForm" method="post" action="/Pharmacie_S/PHP/ventes/process_vente.php" enctype="multipart/form-data">
        <!-- Section Client -->
        <div class="form-group mb-4">
            <h3>Client</h3>
            <label for="client">Client:</label>
            <div class="client-search-container">
                <select name="client_id" id="client" class="form-control" required>
                    <option value="0">Client de passage</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['client_id']; ?>">
                            <?php echo htmlspecialchars($client['nom'] . ' ' . $client['prenom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text"
                    id="client-search"
                    class="form-control"
                    placeholder="Rechercher un client..."
                    autocomplete="off"
                    data-toggle="tooltip"
                    data-placement="right"
                    title="Rechercher un client par nom ou prénom"
                    data-tooltip-id="client-search-tooltip">
            </div>
        </div>

        <!-- Section Produits -->
        <div id="produits" class="mb-4"></div>
        <h3>Produits</h3>
        <div class="produits-container">
            <div class="produit-ligne mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="produit-search-container">
                            <select name="produit_id[]" class="form-control produit-select" required>
                                <option value="">Sélectionnez un produit</option>
                                <?php foreach ($produits as $produit):
                                    $produitDetails = $produitModel->getProduitById($produit['produit_id']);
                                    if ($role == 3 && $produitDetails['prescription'] == 'oui') continue;
                                ?>
                                    <option value="<?php echo $produit['produit_id']; ?>"
                                        data-prix="<?php echo number_format($produitDetails['prix_vente_ht'] * (1 + ($tva / 100)), 2, '.', ''); ?>"
                                        data-taux="<?php echo $produitDetails['taux_remboursement'] ?? ''; ?>"
                                        data-prescription="<?php echo $produitDetails['prescription']; ?>"
                                        data-stock="<?php echo $produit['stock']; ?>">
                                        <?php echo htmlspecialchars($produitDetails['nom']); ?>
                                        (Stock: <?php echo $produit['stock']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text"
                                class="form-control produit-search"
                                placeholder="Rechercher un produit...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <input type="number"
                            name="quantite[]"
                            class="form-control quantite"
                            min="1"
                            value="1"
                            required
                            title="Entrez la quantité souhaitée">
                    </div>
                    <div class="col-md-2">
                        <span class="prix-total"></span>
                    </div>
                    <div class="col-md-2">
                        <span class="montant-a-rembourser"></span>
                    </div>
                    <div class="col-md-2">
                        <button type="button"
                            class="btn btn-outline-danger supprimer-produit"
                            data-toggle="tooltip"
                            data-placement="left"
                            title="Supprimer ce produit">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <!-- Champs Ordonnance -->
                <div class="ordonnance-fields" style="display: none;">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text"
                                name="numero_ordonnance[]"
                                class="form-control "
                                placeholder="Numéro d'ordonnance">
                        </div>
                        <div class="col-md-4">
                            <input type="text"
                                name="numero_ordre[]"
                                class="form-control"
                                placeholder="Numéro d'ordre"
                                title="Numéro d'ordre du médecin">
                        </div>
                        <div class="col-md-4">
                            <input type="file"
                                name="image_ordonnance[]"
                                class="form-control"
                                accept="image/*"
                                title="Image de l'ordonnance">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton Ajouter Produit -->
        <button type="button"
            id="ajouter-produit"
            class="btn btn-success mb-4 "
            data-placement="top"
            title="Ajouter un nouveau produit à la vente">
            <i class="fas fa-plus"></i> Ajouter un produit
        </button>
        <!-- Section Totaux -->
        <div class="totals-section">
            <div class="row">
                <div class="col-md-4">
                    <p>Montant total: <span id="montant_total">0.00€</span></p>
                </div>
                <div class="col-md-4">
                    <p>Montant à rembourser: <span id="montant_a_rembourser">0.00€</span></p>
                </div>
                <div class="col-md-4">
                    <p>Montant à régler: <span id="montant_a_regler">0.00€</span></p>
                </div>
            </div>
        </div>

        <!-- Section Paiement -->
        <div class="payment-section">
            <h3>Mode de paiement</h3>
            <div class="row">
                <div class="col-md-4">
                    <div>
                        <input type="checkbox"
                            name="mode_encaissement[]"
                            value="especes"
                            class="form-check-input"
                            title="Paiement en espèces">
                        <label class="form-check-label">Espèces</label>
                        <input type="number"
                            step="0.01"
                            class="form-control montant-paiement"
                            disabled
                            title="Montant en espèces">
                    </div>
                </div>
                <div class="col-md-4">
                    <div>
                        <input type="checkbox"
                            name="mode_encaissement[]"
                            value="cb"
                            class="form-check-input"
                            title="Paiement par carte bancaire">
                        <label class="form-check-label">Carte bancaire</label>
                        <input type="number"
                            step="0.01"
                            class="form-control montant-paiement"
                            disabled
                            title="Montant par carte">
                    </div>
                </div>
                <div class="col-md-4">
                    <div>
                        <input type="checkbox"
                            name="mode_encaissement[]"
                            value="cheque"
                            class="form-check-input"
                            title="Paiement par chèque">
                        <label class="form-check-label">Chèque</label>
                        <input type="number"
                            step="0.01"
                            class="form-control montant-paiement"
                            disabled
                            title="Montant par chèque">
                        <input type="text"
                            name="numero_cheque"
                            class="form-control mt-2"
                            placeholder="Numéro du chèque"
                            disabled
                            title="Numéro du chèque">
                    </div>
                </div>
            </div>
            <p class="mt-3">Montant restant à payer: <span id="montant_restant">0.00€</span></p>
        </div>

        <!-- Options supplémentaires -->
        <div class="facture-checkbox">
            <label for="creer_facture" class="btn-facture">
                <input type="checkbox"
                    id="creer_facture"
                    name="creer_facture"
                    value="1"
                    title="Générer une facture dans MongoDB">
                <i class="fas fa-file-invoice"></i> Créer une facture dans MongoDB
            </label>
        </div>

        <div class="form-group mt-4">
            <label for="commentaire">Commentaire:</label>
            <textarea name="commentaire"
                id="commentaire"
                class="form-control"
                title="Ajouter un commentaire à la vente"></textarea>
        </div>

        <!-- Bouton de soumission -->
        <button type="submit"
            class="btn btn-primary mt-4"
            title="Enregistrer la vente"
            data-target="#confirmationModal">
            <i class="fas fa-save"></i> Enregistrer la vente
        </button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Initialisation des variables
        const produitsDiv = document.getElementById('produits');
        const ajouterProduitBtn = document.getElementById('ajouter-produit');
        const montantTotalSpan = document.getElementById('montant_total');
        const montantAReglerSpan = document.getElementById('montant_a_regler');
        const montantRestantSpan = document.getElementById('montant_restant');
        const montantTotalInput = document.createElement('input');
        const montantAReglerInput = document.createElement('input');
        const modesPaiement = document.querySelectorAll('input[name="mode_encaissement[]"]');
        const montantsPaiement = document.querySelectorAll('.montant-paiement');
        const creerFactureCheckbox = document.getElementById('creer_facture');
        const clientSelect = document.getElementById('client');
        const venteForm = document.getElementById('venteForm');

        // Configuration des inputs cachés
        montantTotalInput.type = 'hidden';
        montantTotalInput.name = 'montant_total';
        montantAReglerInput.type = 'hidden';
        montantAReglerInput.name = 'montant_a_regler';
        venteForm.appendChild(montantTotalInput);
        venteForm.appendChild(montantAReglerInput);

        // Gestion de la modal de confirmation
        const confirmVenteBtn = document.getElementById('confirmVente');
        venteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateForm()) {
                $('#confirmationModal').modal('show');
            }
        });

        confirmVenteBtn.addEventListener('click', function() {
            preparePaiementData();
            $('#confirmationModal').modal('hide');
            venteForm.submit();
        });
        // Fonction pour attacher les événements à une ligne de produit
        function attachEventListeners(ligne) {
            const select = ligne.querySelector('.produit-select');
            const searchInput = ligne.querySelector('.produit-search');
            const quantiteInput = ligne.querySelector('.quantite');
            const supprimerBtn = ligne.querySelector('.supprimer-produit');
            const ordonnanceFields = ligne.querySelector('.ordonnance-fields');

            // Initialiser la recherche de produits
            if (searchInput) {
                const produitSearch = new ProduitSearch(searchInput, select);
            }
            // Lors du changement de produit
            select.addEventListener('change', () => updatePrixTotal(ligne));

            // Lors du changement de quantité
            quantiteInput.addEventListener('input', function() {
                let quantiteAchetee = parseInt(this.value) || 0;
                if (quantiteAchetee < 0) {
                    quantiteAchetee = 0;
                    this.value = 0;
                }
                updatePrixTotal(ligne);
            });
            // Gestion du bouton supprimer
            supprimerBtn.addEventListener('click', () => {
                const toutesLesLignes = document.querySelectorAll('.produit-ligne');
                // Si c'est la seule ligne, réinitialiser les valeurs au lieu de la supprimer
                if (toutesLesLignes.length === 1) {
                    const select = ligne.querySelector('.produit-select');
                    const quantiteInput = ligne.querySelector('.quantite');
                    const ordonnanceFields = ligne.querySelector('.ordonnance-fields');

                    // Réinitialiser les valeurs
                    select.value = '';
                    quantiteInput.value = '1';
                    ligne.querySelector('.prix-total').textContent = '';
                    ligne.querySelector('.montant-a-rembourser').textContent = '';
                    ordonnanceFields.style.display = 'none';

                    // Réinitialiser les champs d'ordonnance
                    ordonnanceFields.querySelectorAll('input').forEach(input => {
                        input.value = '';
                    });
                } else {
                    ligne.remove();
                }
                updateTotals();
            });

            // Gestion de l'affichage des champs d'ordonnance
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.dataset.prescription === 'oui') {
                    ordonnanceFields.style.display = 'block';
                } else {
                    ordonnanceFields.style.display = 'none';
                }
                updatePrixTotal(ligne);
            });
        }

        // Fonction de mise à jour du prix total
        function updatePrixTotal(ligne) {
            const select = ligne.querySelector('.produit-select');
            const quantiteInput = ligne.querySelector('.quantite');
            const prixTotalSpan = ligne.querySelector('.prix-total');
            const montantARembourserSpan = ligne.querySelector('.montant-a-rembourser');

            if (select.value) {
                const selectedOption = select.options[select.selectedIndex];
                const prix = parseFloat(selectedOption.dataset.prix);
                const quantite = parseInt(quantiteInput.value) || 0;
                const tauxRemboursement = parseFloat(selectedOption.dataset.taux) || 0;

                const total = prix * quantite;
                const remboursement = total * (tauxRemboursement / 100);

                prixTotalSpan.textContent = total.toFixed(2) + '€';
                montantARembourserSpan.textContent = remboursement.toFixed(2) + '€';
            } else {
                prixTotalSpan.textContent = '';
                montantARembourserSpan.textContent = '';
            }

            updateTotals();
        }

        // Fonction de mise à jour des totaux
        function updateTotals() {
            let total = 0;
            let totalRemboursement = 0;

            document.querySelectorAll('.produit-ligne').forEach(ligne => {
                const prixText = ligne.querySelector('.prix-total').textContent;
                const remboursementText = ligne.querySelector('.montant-a-rembourser').textContent;

                if (prixText) {
                    total += parseFloat(prixText);
                }
                if (remboursementText) {
                    totalRemboursement += parseFloat(remboursementText);
                }
            });

            montantTotalSpan.textContent = total.toFixed(2) + '€';
            document.getElementById('montant_a_rembourser').textContent = totalRemboursement.toFixed(2) + '€';
            montantAReglerSpan.textContent = (total - totalRemboursement).toFixed(2) + '€';

            montantTotalInput.value = total.toFixed(2);
            montantAReglerInput.value = (total - totalRemboursement).toFixed(2);

            updateMontantRestant(total - totalRemboursement);
        }
        // Gestion des modes de paiement
        function updateMontantRestant(montantTotal) {
            let montantPaye = 0;
            montantsPaiement.forEach(input => {
                montantPaye += parseFloat(input.value) || 0;
            });
            montantRestantSpan.textContent = (montantTotal - montantPaye).toFixed(2) + '€';
        }

        // Gestion des modes de paiement
        modesPaiement.forEach((checkbox, index) => {
            const montantInput = montantsPaiement[index];
            const numeroCheque = document.querySelector('input[name="numero_cheque"]');

            checkbox.addEventListener('change', function() {
                montantInput.disabled = !this.checked;

                if (this.value === 'cheque') {
                    numeroCheque.disabled = !this.checked;
                    if (this.checked) {
                        const clientId = clientSelect.value;
                        if (clientId === '0') {
                            alert('Les chèques ne sont pas acceptés pour les clients de passage.');
                            this.checked = false;
                            montantInput.disabled = true;
                            montantInput.value = '';
                            return;
                        }
                    }
                }

                if (this.checked) {
                    const montantRestant = parseFloat(montantRestantSpan.textContent);
                    montantInput.value = montantRestant.toFixed(2);
                } else {
                    montantInput.value = '';
                    if (this.value === 'cheque') {
                        numeroCheque.value = '';
                    }
                }

                updateMontantRestant(parseFloat(montantAReglerSpan.textContent));
            });
        });

        // Mise à jour des montants lors de la saisie
        montantsPaiement.forEach(input => {
            input.addEventListener('input', function() {
                updateMontantRestant(parseFloat(montantAReglerSpan.textContent));
            });
        });

        // Ajout d'une nouvelle ligne de produit
        ajouterProduitBtn.addEventListener('click', function() {
            const produitsContainer = document.querySelector('.produits-container');
            const nouvelleLigne = produitsContainer.querySelector('.produit-ligne').cloneNode(true);

            // Réinitialiser les valeurs
            nouvelleLigne.querySelectorAll('input').forEach(input => {
                if (input.classList.contains('produit-search')) {
                    input.value = '';
                } else if (input.closest('.ordonnance-fields')) {
                    input.value = '';
                    input.removeAttribute('data-toggle');
                    input.removeAttribute('title');
                }
            });

            nouvelleLigne.querySelector('select').value = '';
            nouvelleLigne.querySelector('.prix-total').textContent = '';
            nouvelleLigne.querySelector('.montant-a-rembourser').textContent = '';
            nouvelleLigne.querySelector('.ordonnance-fields').style.display = 'none';

            attachEventListeners(nouvelleLigne);
            produitsContainer.appendChild(nouvelleLigne);
        });
        // Validation des produits
        function validateProducts() {
            let valid = true;
            document.querySelectorAll('.produit-ligne').forEach(ligne => {
                const select = ligne.querySelector('.produit-select');
                const quantite = ligne.querySelector('.quantite');
                if (!select.value || parseInt(quantite.value) <= 0) {
                    valid = false;
                }
            });
            return valid;
        }

        // Validation des ordonnances
        function validateOrdonnances() {
            let valid = true;
            document.querySelectorAll('.produit-ligne').forEach(ligne => {
                const select = ligne.querySelector('.produit-select');
                const option = select.options[select.selectedIndex];
                if (option && option.dataset.prescription === 'oui') {
                    const numeroOrdonnance = ligne.querySelector('input[name="numero_ordonnance[]"]');
                    const numeroOrdre = ligne.querySelector('input[name="numero_ordre[]"]');
                    if (!numeroOrdonnance.value || !numeroOrdre.value) {
                        valid = false;
                    }
                }
            });
            return valid;
        }

        // Validation du paiement
        function validatePayment() {
            let montantPaye = 0;
            montantsPaiement.forEach(input => {
                montantPaye += parseFloat(input.value) || 0;
            });
            const montantARegler = parseFloat(montantAReglerSpan.textContent);
            return Math.abs(montantPaye - montantARegler) < 0.01;
        }

        // Validation du chèque
        function validateCheque() {
            const chequePaiement = document.querySelector('input[name="mode_encaissement[]"][value="cheque"]');
            const numeroCheque = document.querySelector('input[name="numero_cheque"]');
            if (chequePaiement && chequePaiement.checked) {
                return numeroCheque.value.trim() !== '';
            }
            return true;
        }
        // Fonctions de validation
        function validateForm() {
            if (!validateProducts()) {
                alert('Veuillez sélectionner au moins un produit avec une quantité valide.');
                return false;
            }
            if (!validateOrdonnances()) {
                alert("Veuillez remplir tous les champs d'ordonnance pour les produits qui en nécessitent.");
                return false;
            }
            if (!validatePayment()) {
                alert('Le montant payé doit être égal au montant à régler.');
                return false;
            }
            if (!validateCheque()) {
                alert('Veuillez saisir un numéro de chèque valide.');
                return false;
            }
            return true;
        }

        // Initialisation des écouteurs d'événements sur la première ligne
        document.querySelectorAll('.produit-ligne').forEach(ligne => {
            attachEventListeners(ligne);
        });
    });
</script>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>