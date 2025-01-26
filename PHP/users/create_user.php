<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$auth = new Authentification();
$user = $auth->checkAdminAuthentication();

// Récupérer les rôles depuis la base de données
$db = Database::getInstance()->getConnection();
$query = "SELECT role_id, nom FROM role";
$stmt = $db->prepare($query);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Mot de passe non haché pour l'e-mail
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Hachage du mot de passe pour la base de données
    $role_id = $_POST['role_id'];

    $userModel = new User();

    // Vérifier si l'email existe déjà
    if ($userModel->getUserByEmail($email)) {
        $_SESSION['error'] = "L'email existe déjà. Veuillez en choisir un autre.";
        header('Location: /Pharmacie_S/Views/users/create_user.php');
        exit();
    }

    if ($userModel->createUser($nom, $prenom, $email, $hashed_password, $role_id)) {
        $_SESSION['success'] = "Utilisateur créé avec succès.";

        // Si l'utilisateur a choisi d'envoyer un e-mail
        if ($_POST['action'] === 'create_and_send_email') {
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.mail.yahoo.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'goldfingerindenait@yahoo.es';
                $mail->Password   = 'abc986224626'; // Utilisez un mot de passe d'application si l'authentification à deux facteurs est activée
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                //Recipients
                $mail->setFrom('goldfingerindenait@yahoo.es', 'Administrateur');
                $mail->addAddress($email, $nom . ' ' . $prenom);

                //Content
                $mail->isHTML(true);
                $mail->Subject = 'Vos identifiants de connexion';
                $mail->Body    = "Bonjour $prenom $nom,<br><br>Voici vos identifiants de connexion :<br>Email : $email<br>Mot de passe : $password<br><br>Nous vous recommandons de changer votre mot de passe après votre première connexion.";

                $mail->send();
                $_SESSION['success'] .= " Un e-mail avec les identifiants a été envoyé.";
            } catch (Exception $e) {
                $_SESSION['error'] = "L'e-mail n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}";
            }
        }

        header('Location: /Pharmacie_S/Views/users/create_user.php');
        exit();
    } else {
        $_SESSION['error'] = "Erreur lors de la création de l'utilisateur.";
        header('Location: /Pharmacie_S/Views/users/create_user.php');
        exit();
    }
}

// Récupérer les messages de session
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>