<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

$userModel = new User();
$role = $userModel->getUserRole($_SESSION['user_id'])['role_id'];

$parametre = new Parametre();
$tva = $parametre->getParametre('TVA');

$clientModel = new Client();
$clients = $clientModel->getAllClients();

$inventaireModel = new Inventaire();
$produits = $inventaireModel->getAllInventaireProducts();

$produitModel = new Produit();

$produitsAjoutes = isset($_POST['produit_id']) ? $_POST['produit_id'] : [];

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<body class="create-vente-page">
    <h1>Créer une nouvelle vente</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form id="venteForm" method="post" action="/Pharmacie_S/PHP/ventes/process_vente.php" enctype="multipart/form-data">
        <div>
            <label for="client">Client:</label>
            <select name="client_id" id="client">
                <option value="0">Client de passage</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['client_id']; ?>">
                        <?php echo htmlspecialchars($client['nom'] . ' ' . $client['prenom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="produits">
            <div class="produit-ligne">
                <select name="produit_id[]" class="produit-select">
                    <option value="">Sélectionnez un produit</option>
                    <?php foreach ($produits as $produit):
                        $produitDetails = $produitModel->getProduitById($produit['produit_id']);
                        if ($role == 3 && $produitDetails['prescription'] == 'oui') continue;
                        if (in_array($produit['produit_id'], $produitsAjoutes)) continue;
                    ?>
                        <option value="<?php echo $produit['produit_id']; ?>"
                            data-prix="<?php echo number_format($produitDetails['prix_vente_ht'] * (1 + ($tva / 100)), 2, '.', ''); ?>"
                            data-taux="<?php echo $produitDetails['taux_remboursement'] !== null ? $produitDetails['taux_remboursement'] : ''; ?>"
                            data-prescription="<?php echo $produitDetails['prescription']; ?>"
                            data-stock="<?php echo $produit['stock']; ?>">
                            <?php echo htmlspecialchars($produit['nom'] . ' (Stock: ' . $produit['stock'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantite[]" class="quantite" min="1" value="1">
                <input type="hidden" name="montant_a_rembourser" id="montant_a_rembourser_input">
                <span class="prix-total"></span>
                <span class="montant-a-regler"></span>
                <span class="montant-a-rembourser"></span>
                <div class="ordonnance-fields" style="display:none;">
                    <input type="text" name="numero_ordonnance[]" placeholder="Numéro d'ordonnance">
                    <input type="text" name="numero_ordre[]" placeholder="Numéro d'ordre">
                    <input type="file" name="image_ordonnance[]" accept="image/*">
                </div>
                <button type="button" class="supprimer-produit">Supprimer</button>
            </div>
        </div>

        <button type="button" id="ajouterProduit">Ajouter un produit</button>

        <div id="modes_paiement">
            <h3>Modes de paiement</h3>
            <div>
                <label>
                    <input type="checkbox" name="mode_encaissement[]" value="especes" class="mode-paiement"> Espèces
                </label>
                <input type="number" name="montant_especes" class="montant-paiement" step="0.01" min="0" disabled>
            </div>
            <div>
                <label>
                    <input type="checkbox" name="mode_encaissement[]" value="carte_bleu" class="mode-paiement"> Carte bleue
                </label>
                <input type="number" name="montant_carte" class="montant-paiement" step="0.01" min="0" disabled>
            </div>
            <div>
                <label>
                    <input type="checkbox" name="mode_encaissement[]" value="cheque" class="mode-paiement"> Chèque
                </label>
                <input type="number" name="montant_cheque" class="montant-paiement" step="0.01" min="0" disabled>
                <input type="text" name="numero_cheque" placeholder="Numéro de chèque" disabled>
            </div>
        </div>

        <div>
            <span>Montant total: </span><span id="montant_total">0</span>
        </div>
        <div>
            <span>Montant à régler: </span><span id="montant_a_regler">0</span>
        </div>
        <div>
            <span>Montant restant à payer: </span><span id="montant_restant">0</span>
        </div>
        <div>
            <span>Montant à rembourser: </span><span id="montant_a_rembourser">0</span>
        </div>
        <div>
            <label for="commentaire">Commentaire (optionnel):</label>
            <textarea id="commentaire" name="commentaire" placeholder="Ajoutez un commentaire si nécessaire"></textarea>
        </div>

        <input type="hidden" name="montant_total" id="montant_total_input">
        <input type="hidden" name="montant_a_regler" id="montant_a_regler_input">

        <button type="submit">Encaisser</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const produitsDiv = document.getElementById('produits');
            const ajouterProduitBtn = document.getElementById('ajouterProduit');
            const montantTotalSpan = document.getElementById('montant_total');
            const montantAReglerSpan = document.getElementById('montant_a_regler');
            const montantRestantSpan = document.getElementById('montant_restant');
            const montantsPaiement = document.querySelectorAll('.montant-paiement');
            const modesPaiement = document.querySelectorAll('.mode-paiement');
            const montantTotalInput = document.getElementById('montant_total_input');
            const montantAReglerInput = document.getElementById('montant_a_regler_input');
            const clientSelect = document.getElementById('client');

            function supprimerProduit(event) {
                const ligne = event.target.closest('.produit-ligne');
                if (ligne && produitsDiv.children.length > 1) {
                    ligne.remove();
                    updateProduitOptions();
                    updateTotaux();
                } else if (produitsDiv.children.length === 1) {
                    alert("Vous ne pouvez pas supprimer le dernier produit. Vous pouvez modifier sa quantité à 0 si nécessaire.");
                }
            }

            produitsDiv.addEventListener('click', function(e) {
                if (e.target.classList.contains('supprimer-produit')) {
                    supprimerProduit(e);
                }
            });

            ajouterProduitBtn.addEventListener('click', function() {
                const newLine = produitsDiv.children[0].cloneNode(true);
                newLine.querySelector('.produit-select').value = '';
                newLine.querySelector('.quantite').value = '1';
                newLine.querySelector('.prix-total').textContent = '';
                newLine.querySelector('.montant-a-regler').textContent = '';
                newLine.querySelector('.montant-a-rembourser').textContent = '';
                newLine.querySelector('.ordonnance-fields').style.display = 'none';
                newLine.querySelector('input[name="numero_ordonnance[]"]').value = '';
                newLine.querySelector('input[name="numero_ordre[]"]').value = '';
                newLine.querySelector('input[name="image_ordonnance[]"]').value = '';

                if (!newLine.querySelector('.supprimer-produit')) {
                    const supprimerBtn = document.createElement('button');
                    supprimerBtn.type = 'button';
                    supprimerBtn.className = 'supprimer-produit';
                    supprimerBtn.textContent = 'Supprimer';
                    newLine.appendChild(supprimerBtn);
                }

                produitsDiv.appendChild(newLine);
                updateProduitOptions();
            });

            produitsDiv.addEventListener('change', function(e) {
                if (e.target.classList.contains('produit-select') || e.target.classList.contains('quantite')) {
                    const ligne = e.target.closest('.produit-ligne');
                    const select = ligne.querySelector('.produit-select');
                    const quantite = ligne.querySelector('.quantite');
                    const prixTotalSpan = ligne.querySelector('.prix-total');
                    const montantAReglerSpan = ligne.querySelector('.montant-a-regler');
                    const montantARembourserSpan = ligne.querySelector('.montant-a-rembourser');
                    const ordonnanceFields = ligne.querySelector('.ordonnance-fields');

                    if (select.value) {
                        const option = select.options[select.selectedIndex];
                        const prix = parseFloat(option.dataset.prix);
                        const taux = parseFloat(option.dataset.taux) || 0;
                        const prescription = option.dataset.prescription;
                        const stock = parseInt(option.dataset.stock);

                        const prixTotal = prix * quantite.value;
                        const montantARembourser = prixTotal * (taux / 100);
                        const montantARegler = prixTotal - montantARembourser;

                        prixTotalSpan.textContent = prixTotal.toFixed(2) + '€';
                        montantAReglerSpan.textContent = montantARegler.toFixed(2) + '€';
                        montantARembourserSpan.textContent = montantARembourser.toFixed(2) + '€';

                        ordonnanceFields.style.display = prescription === 'oui' ? 'block' : 'none';
                    } else {
                        prixTotalSpan.textContent = '';
                        montantAReglerSpan.textContent = '';
                        montantARembourserSpan.textContent = '';
                        ordonnanceFields.style.display = 'none';
                    }

                    updateTotaux();
                }
            });

            function updateProduitOptions() {
                const selectedProduits = Array.from(document.querySelectorAll('.produit-select')).map(select => select.value);
                const produitSelects = document.querySelectorAll('.produit-select');

                produitSelects.forEach(select => {
                    const options = select.querySelectorAll('option');
                    options.forEach(option => {
                        if (selectedProduits.includes(option.value) && option.value !== '' && option.value !== select.value) {
                            option.style.display = 'none';
                        } else {
                            option.style.display = 'block';
                        }
                    });
                });
            }

            function updateTotaux() {
                let montantTotal = 0;
                let montantAReglerTotal = 0;
                let montantARembourserTotal = 0;

                produitsDiv.querySelectorAll('.produit-ligne').forEach(ligne => {
                    const prixTotal = parseFloat(ligne.querySelector('.prix-total').textContent);
                    const montantARegler = parseFloat(ligne.querySelector('.montant-a-regler').textContent);
                    const montantARembourser = parseFloat(ligne.querySelector('.montant-a-rembourser').textContent);
                    if (!isNaN(prixTotal)) montantTotal += prixTotal;
                    if (!isNaN(montantARegler)) montantAReglerTotal += montantARegler;
                    if (!isNaN(montantARembourser)) montantARembourserTotal += montantARembourser;
                });

                montantTotalSpan.textContent = montantTotal.toFixed(2) + '€';
                montantAReglerSpan.textContent = montantAReglerTotal.toFixed(2) + '€';
                document.getElementById('montant_a_rembourser').textContent = montantARembourserTotal.toFixed(2) + '€';
                montantTotalInput.value = montantTotal.toFixed(2);
                montantAReglerInput.value = montantAReglerTotal.toFixed(2);

                updateMontantRestant(montantAReglerTotal);
            }

            function updateMontantRestant(montantAReglerTotal) {
                let montantPaye = 0;
                montantsPaiement.forEach(input => {
                    montantPaye += parseFloat(input.value) || 0;
                });
                const montantRestant = montantAReglerTotal - montantPaye;
                montantRestantSpan.textContent = montantRestant.toFixed(2) + '€';

                montantsPaiement.forEach(input => {
                    if (!input.disabled) {
                        input.max = montantRestant + parseFloat(input.value);
                    }
                });
            }

            modesPaiement.forEach((checkbox, index) => {
                checkbox.addEventListener('change', function() {
                    montantsPaiement[index].disabled = !this.checked;
                    const numeroCheque = document.querySelector('input[name="numero_cheque"]');
                    numeroCheque.disabled = !this.checked;

                    const clientId = clientSelect.value;
                    if (clientId == 0) {
                        if (this.value === 'cheque') {
                            alert('Les chèques ne sont pas acceptés pour les clients de passage, veuillez choisir un client de la liste.');
                            this.checked = false;
                            montantsPaiement[index].disabled = true;
                            montantsPaiement[index].value = '';
                        } else {
                            const montantRestant = parseFloat(montantRestantSpan.textContent);
                            montantsPaiement[index].value = montantRestant.toFixed(2);
                        }
                    } else {
                        const montantRestant = parseFloat(montantRestantSpan.textContent);
                        montantsPaiement[index].value = montantRestant.toFixed(2);
                    }
                    if (!this.checked) {
                        montantsPaiement[index].value = '';
                    }
                    updateMontantRestant(parseFloat(montantAReglerSpan.textContent));
                });
            });
            montantsPaiement.forEach(input => {
                input.addEventListener('input', function() {
                    updateMontantRestant(parseFloat(montantAReglerSpan.textContent));
                });
            });
            document.getElementById('venteForm').addEventListener('submit', function(e) {
                let montantPaye = 0;
                let modesPaiementSelectionnes = [];
                montantsPaiement.forEach((input, index) => {
                    const montant = parseFloat(input.value) || 0;
                    if (montant > 0) {
                        montantPaye += montant;
                        modesPaiementSelectionnes.push({
                            mode: modesPaiement[index].value,
                            montant: montant
                        });
                    }
                });
                if (!verifierNumeroCheque()) {
                    e.preventDefault();
                    alert('Veuillez saisir un numéro de chèque.');
                    return;
                }
                const ordonnanceLignes = produitsDiv.querySelectorAll('.produit-ligne');
                let ordonnanceValide = true;
                ordonnanceLignes.forEach(ligne => {
                    const select = ligne.querySelector('.produit-select');
                    const option = select.options[select.selectedIndex];
                    if (option && option.dataset.prescription === 'oui') {
                        const numeroOrdonnance = ligne.querySelector('input[name="numero_ordonnance[]"]').value;
                        const numeroOrdre = ligne.querySelector('input[name="numero_ordre[]"]').value;
                        const imageOrdonnance = ligne.querySelector('input[name="image_ordonnance[]"]').value;
                        if (!numeroOrdonnance || !numeroOrdre || !imageOrdonnance) {
                            ordonnanceValide = false;
                        }
                    }
                });
                if (!ordonnanceValide) {
                    e.preventDefault();
                    alert("Veuillez remplir tous les champs d'ordonnance (numéro, ordre et image) pour les produits qui en nécessitent.");
                    return;
                }
                if (Math.abs(montantPaye - parseFloat(montantAReglerSpan.textContent)) > 0.01) {
                    e.preventDefault();
                    alert('Le montant payé doit être égal au montant à régler.');
                    return;
                }
                const modesPaiementInput = document.createElement('input');
                modesPaiementInput.type = 'hidden';
                modesPaiementInput.name = 'modes_paiement_json';
                modesPaiementInput.value = JSON.stringify(modesPaiementSelectionnes);
                this.appendChild(modesPaiementInput);
            });

            function verifierNumeroCheque() {
                const chequePaiement = document.querySelector('input[name="mode_encaissement[]"][value="cheque"]');
                const numeroChequeInput = document.querySelector('input[name="numero_cheque"]');
                if (chequePaiement && chequePaiement.checked) {
                    return numeroChequeInput.value.trim() !== '';
                }
                return true;
            }
            document.querySelector('input[name="mode_encaissement[]"][value="cheque"]').addEventListener('change', function() {
                const numeroChequeInput = document.querySelector('input[name="numero_cheque"]');
                numeroChequeInput.disabled = !this.checked;
                if (this.checked) {
                    numeroChequeInput.required = true;
                } else {
                    numeroChequeInput.required = false;
                    numeroChequeInput.value = '';
                }
            });
        });
    </script>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>
</body>
