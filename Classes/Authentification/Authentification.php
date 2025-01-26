<?php

require_once __DIR__ . '/../User.php';

class Authentification
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Authentifie un utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe
     * @return array|false Données de l'utilisateur si authentifié, sinon false
     */
    public function login($email, $password)
    {
        $user = $this->userModel->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $this->startUserSession($user);
            return $user;
        }
        return false;
    }

    /**
     * Déconnecte l'utilisateur actuel
     */
    public function logout()
    {
        $this->endUserSession();
    }

    /**
     * Vérifie si un utilisateur est actuellement connecté
     * @return bool True si un utilisateur est connecté, sinon false
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Récupère l'utilisateur actuellement connecté
     * @return array|null Données de l'utilisateur connecté ou null si aucun utilisateur n'est connecté
     */
    public function getCurrentUser()
    {
        if ($this->isLoggedIn()) {
            return $this->userModel->getUserById($_SESSION['user_id']);
        }
        return null;
    }

    /**
     * Vérifie si l'utilisateur actuel a un rôle spécifique
     * @param int $role_id ID du rôle à vérifier
     * @return bool True si l'utilisateur a le rôle spécifié, sinon false
     */
    public function hasRole($role_id)
    {
        $currentUser = $this->getCurrentUser();
        return $currentUser && $currentUser['role_id'] === $role_id;
    }

    /**
     * Démarre une session utilisateur
     * @param array $user Données de l'utilisateur
     */
    private function startUserSession($user)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['last_activity'] = time();
    }

    /**
     * Termine la session utilisateur
     */
    private function endUserSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }
    public function checkAuthentication()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        error_log("Début de checkAuthentication");
        if (!isset($_SESSION['user_id'])) {
            error_log("Session user_id non définie");
            $this->redirectToLogin();
        }
        error_log("user_id dans la session: " . $_SESSION['user_id']);

        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if (!$user) {
            error_log("Utilisateur non trouvé dans la base de données");
            $this->logout();
            $this->redirectToLogin();
        }
        error_log("Utilisateur trouvé: " . json_encode($user));
        return $user;
    }
    private function redirectToLogin()
    {
        error_log("Redirection vers la page de connexion");
        header('Location: /Pharmacie_S/Views/auth/login.php');
        exit();
    }
    function checkAdminAuthentication()
    {
        error_log("Début de checkAdminAuthentication");
        $user = $this->checkAuthentication();
        error_log("User récupéré: " . json_encode($user));
        if ($user['role_id'] != 1) {
            error_log("Accès non autorisé. Role_id: " . $user['role_id']);
            header('Location: /Pharmacie_S/Views/auth/login.php');
            exit();
        }
        error_log("Authentification admin réussie");
        return $user;
    }
    /**
     * Vérifie si la session n'a pas expiré
     * @return bool True si la session est valide, false si elle a expiré
     */
    public function checkSessionValidity()
    {
        if (!$this->isLoggedIn()) {
            error_log("Session invalide : utilisateur non connecté.");
            return false;
        }
        $inactiveTime = 1800; // 30 min
        if (time() - $_SESSION['last_activity'] > $inactiveTime) {
            error_log("Session expirée : déconnexion de l'utilisateur.");
            $this->logout();
            return false;
        }
        $_SESSION['last_activity'] = time(); // Met à jour le temps d'activité
        return true; // Session valide
    }

    public function getUserById($user_id)
    {
        return $this->userModel->getUserById($user_id);
    }
    /**
     * Génère un token CSRF
     * @return string Token CSRF généré
     */
    private function generateCsrfToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Récupère le token CSRF actuel ou en génère un nouveau si nécessaire
     * @return string Token CSRF
     */
    public function getCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->generateCsrfToken();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie si le token CSRF fourni est valide
     * @param string $token Token CSRF à vérifier
     * @return bool True si le token est valide, sinon false
     */
    public function verifyCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Régénère le token CSRF
     */
    public function regenerateCsrfToken()
    {
        $_SESSION['csrf_token'] = $this->generateCsrfToken();
    }
}
