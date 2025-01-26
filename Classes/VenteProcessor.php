<?php
class VenteProcessor
{
    private $db;
    private $venteModel;
    private $inventaireModel;
    private $produitModel;
    private $ordonnanceModel;
    private $chequeModel;
    private $parametreModel;
    private $clientModel;
    private $factureModel;

    private $client_id;
    private $montant_total = 0;
    private $montant_regle = 0;
    private $montant_a_rembourser = 0;
    private $tva;
    private $commentaire;
    private $paiement_data;
    private $modes_paiement_combines = [];
    private $paiementsData = [];
    private $cheque_id = null;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Vérification de l'authentification
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Session utilisateur non trouvée");
        }

        $this->initializeModels();
    }

    private function initializeModels()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->venteModel = new Vente();
        $this->inventaireModel = new Inventaire();
        $this->produitModel = new Produit();
        $this->ordonnanceModel = new Ordonnance();
        $this->chequeModel = new Cheque();
        $this->parametreModel = new Parametre();
        $this->clientModel = new Client();

        $mongoConnection = Database_Mongo::getInstance();
        $this->factureModel = new FactureModel($mongoConnection->getBdd());
    }

    public function process()
    {
        try {
            $this->validateRequest();
            $this->validateAndSetClient();
            $this->initializeBasicData();
            $this->validateProducts();
            $this->processPaymentMethods();
            $this->processVente();

            $_SESSION['success'] = $this->paiement_data['creerFacture'] === '1' ?
                "Vente enregistrée avec succès. La facture a été créée." :
                "Vente enregistrée avec succès.";
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: /Pharmacie_S/Views/ventes/create_vente.php');
        exit();
    }

    private function validateRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Méthode non autorisée");
        }
    }
    private function validateAndSetClient()
    {
        $this->client_id = isset($_POST['client_id']) ?
            filter_var($_POST['client_id'], FILTER_VALIDATE_INT) : null;

        if ($this->client_id === 0) {
            $this->client_id = null;
        }

        if ($this->client_id !== null) {
            $client = $this->clientModel->getClientById($this->client_id);
            if (!$client) {
                throw new Exception("Client invalide");
            }

            $this->validateClientCheques($client);
        }
    }

    private function validateClientCheques($client)
    {
        if ($client['cheques_impayes'] == 1) {
            $paiement_data = $this->getPaiementData();

            if (isset($paiement_data['modes'])) {
                foreach ($paiement_data['modes'] as $paiement) {
                    if ($paiement['mode'] === 'cheque') {
                        throw new Exception(
                            "Ce client a des chèques impayés. Le paiement par chèque n'est pas autorisé."
                        );
                    }
                }
            }
        }
    }

    private function initializeBasicData()
    {
        $this->commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : null;
        $this->tva = $this->parametreModel->getParametre('TVA');

        if (!isset($_POST['produit_id']) || empty($_POST['produit_id'])) {
            throw new Exception("Aucun produit sélectionné");
        }
    }

    private function validateProducts()
    {
        foreach ($_POST['produit_id'] as $index => $produit_id) {
            if (empty($produit_id)) {
                continue;
            }

            $this->validateProductQuantity($index, $produit_id);
            $this->validateProductStock($index, $produit_id);
            $this->calculateProductAmounts($index, $produit_id);
        }
    }

    private function validateProductQuantity($index, $produit_id)
    {
        $quantite = isset($_POST['quantite'][$index]) ? (int)$_POST['quantite'][$index] : 0;
        if ($quantite <= 0) {
            throw new Exception("Quantité invalide pour le produit " . ($index + 1));
        }
        return $quantite;
    }

    private function validateProductStock($index, $produit_id)
    {
        $quantite = $this->validateProductQuantity($index, $produit_id);

        $produitDetails = $this->produitModel->getProduitById($produit_id);
        if ($produitDetails === false) {
            throw new Exception("Produit non trouvé pour l'ID: " . $produit_id);
        }

        $inventaireInfo = $this->inventaireModel->getInventaireById($produit_id);
        if (!$inventaireInfo) {
            throw new Exception("Produit non trouvé dans l'inventaire");
        }

        return [$produitDetails, $inventaireInfo];
    }

    private function calculateProductAmounts($index, $produit_id)
    {
        $quantite = $this->validateProductQuantity($index, $produit_id);
        list($produitDetails, $inventaireInfo) = $this->validateProductStock($index, $produit_id);

        // Calcul du prix TTC et du montant total
        $prix_ttc = $produitDetails['prix_vente_ht'] * (1 + ($this->tva / 100));
        $prix_produit = $prix_ttc * $quantite;
        $this->montant_total += $prix_produit;

        // Calcul du remboursement si applicable
        $taux_remboursement = $produitDetails['taux_remboursement'] ?? 0;
        if ($taux_remboursement > 0) {
            $this->montant_a_rembourser += $prix_produit * ($taux_remboursement / 100);
        }
    }

    private function getPaiementData()
    {
        $modes_paiement_json = isset($_POST['modes_paiement_json']) ?
            $_POST['modes_paiement_json'] : '{}';

        $this->paiement_data = json_decode($modes_paiement_json, true);

        if (!is_array($this->paiement_data) || !isset($this->paiement_data['modes'])) {
            throw new Exception("Format des modes de paiement invalide");
        }

        return $this->paiement_data;
    }
    private function processPaymentMethods()
    {
        $this->paiement_data = $this->getPaiementData();
        $montant_total_regle = 0;
        $montant_a_payer = $this->montant_total - $this->montant_a_rembourser;

        foreach ($this->paiement_data['modes'] as $paiement) {
            $this->validatePaymentMethod($paiement);
            $montant_total_regle += $this->processPaymentMethod($paiement);
        }

        $this->validateTotalPayment($montant_total_regle, $montant_a_payer);
        $this->montant_regle = $montant_total_regle;
    }

    private function validatePaymentMethod($paiement)
    {
        if (!isset($paiement['mode']) || !isset($paiement['montant'])) {
            throw new Exception("Données de paiement incomplètes");
        }

        if (!in_array($paiement['mode'], ['especes', 'cb', 'cheque'])) {
            throw new Exception("Mode de paiement non valide: " . htmlspecialchars($paiement['mode']));
        }

        $montant = floatval($paiement['montant']);
        if ($montant <= 0) {
            throw new Exception("Montant invalide pour le mode de paiement: " .
                htmlspecialchars($paiement['mode']));
        }
    }

    private function processPaymentMethod($paiement)
    {
        $mode = trim($paiement['mode']);
        $montant = floatval($paiement['montant']);

        if ($mode === 'cheque') {
            $this->processCheckPayment($montant);
        }

        $this->paiementsData[] = $this->createPaymentData($mode, $montant);
        $this->modes_paiement_combines[] = $mode;

        return $montant;
    }

    private function processCheckPayment($montant)
    {
        if ($this->client_id === 0) {
            throw new Exception("Le paiement par chèque n'est pas autorisé pour les clients anonymes");
        }

        $numero_cheque = isset($_POST['numero_cheque']) ? trim($_POST['numero_cheque']) : '';
        if (empty($numero_cheque)) {
            throw new Exception("Numéro de chèque obligatoire");
        }

        $this->cheque_id = $this->chequeModel->createCheque(
            $numero_cheque,
            $this->client_id,
            $montant
        );

        if (!$this->cheque_id) {
            throw new Exception("Erreur lors de l'enregistrement du chèque");
        }
    }

    private function createPaymentData($mode, $montant)
    {
        $paymentData = [
            'mode' => $mode,
            'montant' => $montant
        ];

        if ($mode === 'cheque') {
            $paymentData['numero_cheque'] = $_POST['numero_cheque'];
            $paymentData['cheque_id'] = $this->cheque_id;
        }

        return $paymentData;
    }

    private function validateTotalPayment($montant_total_regle, $montant_a_payer)
    {
        $difference = abs($montant_total_regle - $montant_a_payer);
        if ($difference > 0.01) { // Tolérance de 0.01€ pour les erreurs d'arrondi
            throw new Exception(sprintf(
                "Le montant payé (%.2f€) ne correspond pas au montant à régler (%.2f€)",
                $montant_total_regle,
                $montant_a_payer
            ));
        }
    }

    private function processVente()
    {
        $this->db->beginTransaction();

        try {
            $vente_id = $this->createVente();
            $produitsData = $this->processProducts($vente_id);
            $this->processPayments($vente_id);
            $this->createFactureIfRequested($vente_id, $produitsData);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    private function createVente()
    {
        $vente_id = $this->venteModel->createVente(
            $this->client_id,
            $_SESSION['user_id'],
            $this->montant_total,
            $this->montant_regle,
            $this->montant_a_rembourser,
            $this->commentaire
        );

        if (!$vente_id) {
            throw new Exception("Erreur lors de la création de la vente");
        }

        return $vente_id;
    }

    private function processProducts($vente_id)
    {
        $produitsData = [];

        foreach ($_POST['produit_id'] as $index => $produit_id) {
            if (empty($produit_id)) continue;

            $quantite = (int)$_POST['quantite'][$index];
            $produitDetails = $this->produitModel->getProduitById($produit_id);

            // Ajout du produit à la vente
            $this->addProductToVente($vente_id, $produit_id, $quantite);

            // Mise à jour du stock
            $this->updateStock($produit_id, $quantite);

            // Traitement des données pour la facture
            $produitsData[] = $this->prepareProductData($produitDetails, $quantite);

            // Traitement de l'ordonnance si nécessaire
            if ($produitDetails['prescription'] == 'oui') {
                $this->processOrdonnance($vente_id, $produit_id, $index);
            }
        }

        return $produitsData;
    }


    private function addProductToVente($vente_id, $produit_id, $quantite)
    {
        if (!$this->venteModel->addProduitToVente($vente_id, $produit_id, $quantite)) {
            throw new Exception("Erreur lors de l'ajout du produit à la vente");
        }
    }

    private function updateStock($produit_id, $quantite)
    {
        if (!$this->inventaireModel->updateStock($produit_id, -$quantite)) {
            throw new Exception("Erreur lors de la mise à jour du stock");
        }
    }

    private function prepareProductData($produitDetails, $quantite)
    {
        $prix_unitaire_ttc = $produitDetails['prix_vente_ht'] * (1 + ($this->tva / 100));
        $montant_remboursement = ($prix_unitaire_ttc * $quantite) *
            ($produitDetails['taux_remboursement'] / 100);

        return [
            'produit_id' => $produitDetails['produit_id'],
            'nom' => $produitDetails['nom'],
            'quantite' => $quantite,
            'prix_unitaire' => $prix_unitaire_ttc,
            'prix_total' => $prix_unitaire_ttc * $quantite,
            'taux_remboursement' => $produitDetails['taux_remboursement'],
            'montant_a_rembourser' => $montant_remboursement
        ];
    }

    private function processOrdonnance($vente_id, $produit_id, $index)
    {
        $ordonnanceData = $this->validateOrdonnanceData($index);
        $image_path = $this->processOrdonnanceImage($index);

        $ordonnance_id = $this->ordonnanceModel->createOrdonnance(
            $ordonnanceData['numero_ordonnance'],
            $ordonnanceData['numero_ordre'],
            $image_path
        );

        if (!$ordonnance_id) {
            throw new Exception("Erreur lors de la création de l'ordonnance");
        }

        $this->venteModel->addOrdonnanceToVente($vente_id, $ordonnance_id);
        $this->ordonnanceModel->addProduitToOrdonnance($ordonnance_id, $produit_id);
    }

    private function validateOrdonnanceData($index)
    {
        $numero_ordonnance = trim($_POST['numero_ordonnance'][$index] ?? '');
        $numero_ordre = trim($_POST['numero_ordre'][$index] ?? '');

        if (empty($numero_ordonnance) || empty($numero_ordre)) {
            throw new Exception(
                "Numéro d'ordonnance et numéro d'ordre requis pour les produits sous prescription"
            );
        }

        return [
            'numero_ordonnance' => $numero_ordonnance,
            'numero_ordre' => $numero_ordre
        ];
    }

    private function processOrdonnanceImage($index)
    {
        if (
            !isset($_FILES['image_ordonnance']['name'][$index]) ||
            $_FILES['image_ordonnance']['error'][$index] !== 0
        ) {
            return null;
        }

        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/uploads/ordonnances/';
        $image_name = uniqid() . '_' . basename($_FILES['image_ordonnance']['name'][$index]);
        $image_path = $upload_dir . $image_name;

        $this->validateImageFile($image_path);
        $this->uploadImage($index, $image_path);

        return $image_path;
    }

    private function validateImageFile($image_path)
    {
        $file_type = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        if (!in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception("Type de fichier non autorisé pour l'ordonnance");
        }
    }

    private function uploadImage($index, $image_path)
    {
        if (!move_uploaded_file($_FILES['image_ordonnance']['tmp_name'][$index], $image_path)) {
            throw new Exception("Erreur lors de l'upload de l'image d'ordonnance");
        }
    }

    private function processPayments($vente_id)
    {
        $modes_paiement_string = implode(',', array_unique($this->modes_paiement_combines));
        if (!$this->venteModel->addPaiementToVente(
            $vente_id,
            $modes_paiement_string,
            $this->montant_regle,
            $this->cheque_id
        )) {
            throw new Exception("Erreur lors de l'enregistrement des paiements");
        }
    }

    private function createFactureIfRequested($vente_id, $produitsData)
    {
        if (!$this->paiement_data['creerFacture'] === '1') {
            return;
        }

        $venteData = $this->venteModel->getVenteById($vente_id);
        if ($this->client_id > 0) {
            $clientInfo = $this->clientModel->getClientById($this->client_id);
            $venteData['client_nom'] = $clientInfo['nom'];
            $venteData['client_prenom'] = $clientInfo['prenom'];
        }

        if (!$this->factureModel->saveFacture($venteData, $produitsData, $this->paiementsData)) {
            throw new Exception("Erreur lors de la création de la facture");
        }
    }
}

