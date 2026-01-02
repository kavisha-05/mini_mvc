<!doctype html>
<!-- Définit la langue du document -->
<html lang="fr">
<!-- En-tête du document HTML -->
<head>
    <!-- Déclare l'encodage des caractères -->
    <meta charset="utf-8">
    <!-- Configure le viewport pour les appareils mobiles -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Définit le titre de la page avec échappement -->
    <title><?= isset($title) ? htmlspecialchars($title) : 'App' ?></title>
    <!-- Feuille de style personnalisée -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<!-- Corps du document -->
<body>
<?php
// Démarre la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Détermine la page active pour la navigation
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$isHome = ($currentPath === '/');
$isProducts = ($currentPath === '/products' || strpos($currentPath, '/products/show') === 0 || strpos($currentPath, '/products/edit') === 0);
$isProductsCreate = ($currentPath === '/products/create');
$isUsersCreate = ($currentPath === '/users/create');
$isCart = ($currentPath === '/cart');
$isOrders = (strpos($currentPath, '/orders') === 0);
$isLogin = ($currentPath === '/auth/login');
$isRegister = ($currentPath === '/auth/register');
$user_id = $_SESSION['user_id'] ?? $_GET['user_id'] ?? 1; // Utilise la session ou le paramètre GET
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!-- En-tête de la page -->
<header>
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <!-- Logo/Titre -->
        <h1 style="margin: 0;">
            <a href="/" style="color: white !important;">🌸 Mini MVC</a>
        </h1>
        
        <!-- Navigation -->
        <nav>
            <ul>
                <li>
                    <a href="/" class="<?= $isHome ? 'active' : '' ?>">
                        🏠 Accueil
                    </a>
                </li>
                <li>
                    <a href="/products" class="<?= $isProducts ? 'active' : '' ?>">
                        📦 Produits
                    </a>
                </li>
                <li>
                    <a href="/products/create" class="<?= $isProductsCreate ? 'active' : '' ?>">
                        ➕ Ajouter un produit
                    </a>
                </li>
                <li>
                    <a href="/cart<?= $isLoggedIn ? '?user_id=' . $user_id : '' ?>" class="<?= $isCart ? 'active' : '' ?>">
                        🛒 Panier
                    </a>
                </li>
                <li>
                    <a href="/orders<?= $isLoggedIn ? '?user_id=' . $user_id : '' ?>" class="<?= $isOrders ? 'active' : '' ?>">
                        📋 Mes commandes
                    </a>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li>
                        <span style="padding: 10px 18px; color: white; font-weight: 600;">
                            👤 <?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?>
                        </span>
                    </li>
                    <li>
                        <a href="/auth/logout">
                            🚪 Déconnexion
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="/auth/login" class="<?= $isLogin ? 'active' : '' ?>">
                            🔐 Connexion
                        </a>
                    </li>
                    <li>
                        <a href="/auth/register" class="<?= $isRegister ? 'active' : '' ?>">
                            📝 Inscription
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<!-- Zone de contenu principal -->
<main>
    <!-- Insère le contenu rendu de la vue -->
    <?= $content ?>
    
</main>
<!-- Fin du corps de la page -->
</body>
<!-- Fin du document HTML -->
</html>

