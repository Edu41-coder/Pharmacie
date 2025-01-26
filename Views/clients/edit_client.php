<?php
$pageTitle = "Modifier un Client";
$additionalHeadContent = <<<EOT
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.className = "index-clients-page";
    });
</script>
EOT;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/Client.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Classes/Authentification/Authentification.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Instancier la classe Authentification
$auth = new Authentification();

// Générer ou récupérer le token CSRF
$csrfToken = $auth->getCsrfToken();
error_log("CSRF Token généré/récupéré : " . $csrfToken);

// Instancier le modèle User et récupérer le rôle
$userModel = new User();
$role = $userModel->getUserRole($_SESSION['user_id'])['role_id'];

// Récupérer les données du client
$clientModel = new Client();
$clientId = isset($_GET['id']) ? $_GET['id'] : null;
$item = $clientId ? $clientModel->getClientById($clientId) : null;

// Récupérer les messages de session
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']);
?>

<h1>Modifier un Client</h1>
<?php if (!empty($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>
<?php if ($item): ?>
    <form action="/Pharmacie_S/PHP/clients/edit_client.php?id=<?php echo htmlspecialchars($item['client_id']); ?>" method="post" class="register-form">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

        <label for="nom">Nom:</label>
        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($item['nom']); ?>" required>

        <label for="prenom">Prénom:</label>
        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($item['prenom']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($item['email']); ?>" required>

        <label for="telephone">Téléphone:</label>
        <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($item['telephone']); ?>">

        <label for="adresse">Adresse:</label>
        <textarea id="adresse" name="adresse"><?php echo htmlspecialchars($item['adresse']); ?></textarea>

        <label for="commentaire">Commentaire:</label>
        <textarea id="commentaire" name="commentaire"><?php echo htmlspecialchars($item['commentaire']); ?></textarea>

        <label for="numero_carte_vitale">Numéro de Carte Vitale:</label>
        <input type="text" id="numero_carte_vitale" name="numero_carte_vitale" value="<?php echo htmlspecialchars($item['numero_carte_vitale']); ?>">
        <button type="submit">Modifier</button>
    </form>
<?php else: ?>
    <p>Client non trouvé.</p>
<?php endif; ?>
<a href="/Pharmacie_S/Views/clients/index_clients.php" class="back-link-gray">Retour à la liste des clients</a>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/Templates/footer.php';
?>