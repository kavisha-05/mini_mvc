<?php

declare(strict_types=1);

require dirname(path: __DIR__) . '/vendor/autoload.php';

use Mini\Core\Router;

// Table des routes minimaliste
$routes = [
    ['GET', '/', [Mini\Controllers\HomeController::class, 'index']],
    ['GET', '/users', [Mini\Controllers\HomeController::class, 'users']],
    ['POST', '/users', [Mini\Controllers\HomeController::class, 'createUser']],
    ['GET', '/users/create', [Mini\Controllers\HomeController::class, 'showCreateUserForm']],
    // Routes pour les produits
    ['GET', '/products', [Mini\Controllers\ProductController::class, 'listProducts']],
    ['GET', '/products/show', [Mini\Controllers\ProductController::class, 'show']],
    ['GET', '/products/create', [Mini\Controllers\ProductController::class, 'showCreateProductForm']],
    ['POST', '/products', [Mini\Controllers\ProductController::class, 'createProduct']],
    ['GET', '/products/edit', [Mini\Controllers\ProductController::class, 'showEditForm']],
    ['POST', '/products/update', [Mini\Controllers\ProductController::class, 'update']],
    ['POST', '/products/delete', [Mini\Controllers\ProductController::class, 'delete']],
    // Routes pour l'authentification
    ['GET', '/auth/login', [Mini\Controllers\AuthController::class, 'showLoginForm']],
    ['POST', '/auth/login', [Mini\Controllers\AuthController::class, 'login']],
    ['GET', '/auth/register', [Mini\Controllers\AuthController::class, 'showRegisterForm']],
    ['POST', '/auth/register', [Mini\Controllers\AuthController::class, 'register']],
    ['GET', '/auth/logout', [Mini\Controllers\AuthController::class, 'logout']],
    ['POST', '/auth/logout', [Mini\Controllers\AuthController::class, 'logout']],
    // Routes pour le panier
    ['GET', '/cart', [Mini\Controllers\CartController::class, 'show']],
    ['POST', '/cart/add', [Mini\Controllers\CartController::class, 'add']],
    ['POST', '/cart/add-from-form', [Mini\Controllers\CartController::class, 'addFromForm']],
    ['POST', '/cart/update', [Mini\Controllers\CartController::class, 'update']],
    ['POST', '/cart/remove', [Mini\Controllers\CartController::class, 'remove']],
    ['POST', '/cart/clear', [Mini\Controllers\CartController::class, 'clear']],
    // Routes pour les commandes
    ['GET', '/orders', [Mini\Controllers\OrderController::class, 'listByUser']],
    ['GET', '/orders/validated', [Mini\Controllers\OrderController::class, 'listValidated']],
    ['GET', '/orders/show', [Mini\Controllers\OrderController::class, 'show']],
    ['POST', '/orders/create', [Mini\Controllers\OrderController::class, 'create']],
    ['POST', '/orders/update-status', [Mini\Controllers\OrderController::class, 'updateStatus']],
];
// Bootstrap du router
$router = new Router($routes);
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);