<?php
$pageTitle = "Détail des Opérations";
$additionalHeadContent = <<<EOT
<link rel="stylesheet" href="/Pharmacie_S/css/all.min.css">
<link rel="stylesheet" href="/Pharmacie_S/css/bootstrap.min.css">
<style>.container {
  /* Styles existants */
  border: 10px solid #ddd;
  border-radius: 10px;
  padding: 30px;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
  position: relative;
  margin-top: 20px;
  background: #fff;

  /* Ajout de la transition pour un effet fluide */
  transform: translateZ(0);
  transition: all 0.3s ease;
}

/* Effet de survol */
.container:hover {
  transform: translateZ(50px) translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-tva-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';

$venteObj = new Vente();
$commandeObj = new Commande();
$parametreObj = new Parametre();

// Récupérer le taux de TVA depuis la table parametres
$tauxTVA = $parametreObj->getParametre('TVA');
$tauxTVA = $tauxTVA ? floatval($tauxTVA) : 20; // Valeur par défaut de 20% si non définie

// Traitement du formulaire
$dateDebut = !empty($_GET['date_debut']) ? $_GET['date_debut'] : null;
$dateFin = !empty($_GET['date_fin']) ? $_GET['date_fin'] : null;

// Calcul des différents montants
$montantTotalVentes = $venteObj->getMontantTotalVentes($dateDebut, $dateFin);
$montantTotalTVA = $montantTotalVentes - ($montantTotalVentes / (1 + $tauxTVA / 100));
$montantTotalChequesImpayes = $venteObj->getMontantTotalChequesImpayes($dateDebut, $dateFin);
$montantTotalCommandes = $commandeObj->getMontantTotalCommandesLivrees($dateDebut, $dateFin);
?>

<div class="container">
    <div class="main-box">
        <h1>Détail des Opérations</h1>

        <form method="GET" action="" class="mb-4">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="date_debut">Date de début</label>
                    <input type="date" name="date_debut" id="date_debut" class="form-control" value="<?php echo $dateDebut ?? ''; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="date_fin">Date de fin</label>
                    <input type="date" name="date_fin" id="date_fin" class="form-control" value="<?php echo $dateFin ?? ''; ?>">
                </div>
                <div class="form-group col-md-2">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary form-control">Filtrer</button>
                </div>
                <div class="form-group col-md-2">
                    <label>&nbsp;</label>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary form-control">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="alert alert-info">
            <h4>Détail des opérations <?php echo ($dateDebut && $dateFin) ? "du $dateDebut au $dateFin" : ""; ?> :</h4>
            <ul>
                <li>Montant total des ventes : <?php echo number_format($montantTotalVentes, 2, ',', ' '); ?> €</li>
                <li>Montant total de TVA : <?php echo number_format($montantTotalTVA, 2, ',', ' '); ?> €</li>
                <li>Montant total des chèques impayés : <?php echo number_format($montantTotalChequesImpayes, 2, ',', ' '); ?> €</li>
                <li>Montant total des commandes livrées : <?php echo number_format($montantTotalCommandes, 2, ',', ' '); ?> €</li>
            </ul>
        </div>
        <a href="/Pharmacie_S/Views/comptabilité/index_comptabilité.php" class="back-link-gray">
            <i class="fas fa-arrow-left"></i> Retour à l'index comptabilité
        </a>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php'; ?>