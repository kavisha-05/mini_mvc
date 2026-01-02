<?php

declare(strict_types=1);

namespace Mini\Controllers;

use Mini\Core\Controller;
use Mini\Models\User;

final class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm(): void
    {
        $this->render('auth/login', params: [
            'title' => 'Connexion'
        ]);
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegisterForm(): void
    {
        $this->render('auth/register', params: [
            'title' => 'Inscription'
        ]);
    }

    /**
     * Traite la connexion de l'utilisateur
     */
    public function login(): void
    {
        // Vérifie et crée la table user si nécessaire
        \Mini\Core\MigrationHelper::ensureUserTableExists();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /auth/login');
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->render('auth/login', params: [
                'title' => 'Connexion',
                'error' => 'Veuillez remplir tous les champs.',
                'email' => $email
            ]);
            return;
        }

        $user = User::findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            $this->render('auth/login', params: [
                'title' => 'Connexion',
                'error' => 'Email ou mot de passe incorrect.',
                'email' => $email
            ]);
            return;
        }

        // Démarre la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Stocke les informations de l'utilisateur dans la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_nom'] = $user['nom'];

        header('Location: /');
    }

    /**
     * Traite l'inscription d'un nouvel utilisateur
     */
    public function register(): void
    {
        // Vérifie et crée la table user si nécessaire
        \Mini\Core\MigrationHelper::ensureUserTableExists();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /auth/register');
            return;
        }

        $nom = $_POST['nom'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validation
        if (empty($nom) || empty($email) || empty($password) || empty($password_confirm)) {
            $this->render('auth/register', params: [
                'title' => 'Inscription',
                'error' => 'Veuillez remplir tous les champs.',
                'nom' => $nom,
                'email' => $email
            ]);
            return;
        }

        if ($password !== $password_confirm) {
            $this->render('auth/register', params: [
                'title' => 'Inscription',
                'error' => 'Les mots de passe ne correspondent pas.',
                'nom' => $nom,
                'email' => $email
            ]);
            return;
        }

        if (strlen($password) < 6) {
            $this->render('auth/register', params: [
                'title' => 'Inscription',
                'error' => 'Le mot de passe doit contenir au moins 6 caractères.',
                'nom' => $nom,
                'email' => $email
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/register', params: [
                'title' => 'Inscription',
                'error' => 'Format d\'email invalide.',
                'nom' => $nom,
                'email' => $email
            ]);
            return;
        }

        // Vérifie si l'email existe déjà
        if (User::findByEmail($email)) {
            $this->render('auth/register', params: [
                'title' => 'Inscription',
                'error' => 'Cet email est déjà utilisé.',
                'nom' => $nom,
                'email' => $email
            ]);
            return;
        }

        // Hash le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Crée l'utilisateur
        $user = new User();
        $user->setNom($nom);
        $user->setEmail($email);
        $user->setPassword($hashedPassword);

        if ($user->save()) {
            // Connecte automatiquement l'utilisateur
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $newUser = User::findByEmail($email);
            $_SESSION['user_id'] = $newUser['id'];
            $_SESSION['user_email'] = $newUser['email'];
            $_SESSION['user_nom'] = $newUser['nom'];

            header('Location: /?success=registered');
        } else {
            $this->render('auth/register', params: [
                'title' => 'Inscription',
                'error' => 'Erreur lors de l\'inscription. Veuillez réessayer.',
                'nom' => $nom,
                'email' => $email
            ]);
        }
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        header('Location: /');
    }
}


