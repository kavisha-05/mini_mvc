<?php

// Active le mode strict pour la vérification des types
declare(strict_types=1);
// Déclare l'espace de noms pour ce contrôleur
namespace Mini\Controllers;
// Importe la classe de base Controller du noyau
use Mini\Core\Controller;
use Mini\Core\MigrationHelper;
use Mini\Models\Product;
use Mini\Models\Category;

// Déclare la classe finale ProductController qui hérite de Controller
final class ProductController extends Controller
{
    public function listProducts(): void
    {
        // Vérifie et ajoute automatiquement les colonnes manquantes si nécessaire
        MigrationHelper::ensureImageUrlColumnExists();
        MigrationHelper::ensureCategorieIdAllowsNull();
        
        // Récupère tous les produits
        $products = Product::getAll();
        
        // Affiche la liste des produits
        $this->render('product/list-products', params: [
            'title' => 'Liste des produits',
            'products' => $products
        ]);
    }

    /**
     * Affiche les détails d'un produit
     */
    public function show(): void
    {
        // Vérifie et ajoute automatiquement les colonnes manquantes si nécessaire
        MigrationHelper::ensureImageUrlColumnExists();
        MigrationHelper::ensureCategorieIdAllowsNull();
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Le paramètre id est requis.'], JSON_PRETTY_PRINT);
            return;
        }
        
        $product = Product::findById($id);
        
        $this->render('product/show', params: [
            'title' => $product ? htmlspecialchars($product['nom']) : 'Produit introuvable',
            'product' => $product
        ]);
    }

    public function showCreateProductForm(): void
    {
        // Vérifie et ajoute automatiquement les colonnes manquantes si nécessaire
        MigrationHelper::ensureImageUrlColumnExists();
        MigrationHelper::ensureCategorieIdAllowsNull();
        // Vérifie et insère automatiquement les catégories de beauté si elles n'existent pas
        $this->ensureBeautyCategoriesExist();
        
        // Récupère toutes les catégories
        $categories = Category::getAll();
        
        // Affiche le formulaire de création de produit
        $this->render('product/create-product', params: [
            'title' => 'Créer un produit',
            'categories' => $categories
        ]);
    }

    /**
     * Vérifie et insère automatiquement les catégories de beauté si elles n'existent pas
     */
    private function ensureBeautyCategoriesExist(): void
    {
        $requiredCategories = [
            'Essences' => 'Essences hydratantes et régénérantes pour la peau',
            'Sérums' => 'Sérums concentrés pour des soins ciblés',
            'Nettoyants' => 'Produits de nettoyage et démaquillants',
            'Toners' => 'Toniques et lotions pour équilibrer le pH de la peau',
            'Protection solaire' => 'Crèmes et sprays de protection solaire',
            'Masques' => 'Masques visage pour des soins intensifs',
            'Exfoliants' => 'Produits exfoliants pour éliminer les cellules mortes',
            'Soins yeux' => 'Crèmes et sérums spécialisés pour le contour des yeux',
            'Soins lèvres' => 'Baumes et soins pour les lèvres'
        ];
        
        // Récupère toutes les catégories existantes
        $existingCategories = Category::getAll();
        $existingNames = array_column($existingCategories, 'nom');
        
        // Insère les catégories manquantes
        foreach ($requiredCategories as $nom => $description) {
            if (!in_array($nom, $existingNames)) {
                $category = new Category();
                $category->setNom($nom);
                $category->setDescription($description);
                $category->save();
            }
        }
    }

    public function createProduct(): void
    {
        // Vérifie et ajoute automatiquement les colonnes manquantes si nécessaire
        MigrationHelper::ensureImageUrlColumnExists();
        MigrationHelper::ensureCategorieIdAllowsNull();
        // Vérifie et insère automatiquement les catégories de beauté si elles n'existent pas
        $this->ensureBeautyCategoriesExist();
        
        // Vérifie que la méthode HTTP est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /products/create');
            return;
        }
        
        // Récupère les données depuis $_POST
        $input = $_POST;
        
        // Récupère toutes les catégories pour la vue
        $categories = Category::getAll();
        
        // Valide les données requises
        if (empty($input['nom']) || empty($input['prix']) || empty($input['stock'])) {
            $this->render('product/create-product', params: [
                'title' => 'Créer un produit',
                'message' => 'Les champs "nom", "prix" et "stock" sont requis.',
                'success' => false,
                'old_values' => $input,
                'categories' => $categories
            ]);
            return;
        }
        
        // Valide le prix (doit être un nombre positif)
        if (!is_numeric($input['prix']) || floatval($input['prix']) < 0) {
            $this->render('product/create-product', params: [
                'title' => 'Créer un produit',
                'message' => 'Le prix doit être un nombre positif.',
                'success' => false,
                'old_values' => $input,
                'categories' => $categories
            ]);
            return;
        }
        
        // Valide le stock (doit être un entier positif)
        if (!is_numeric($input['stock']) || intval($input['stock']) < 0) {
            $this->render('product/create-product', params: [
                'title' => 'Créer un produit',
                'message' => 'Le stock doit être un entier positif.',
                'success' => false,
                'old_values' => $input,
                'categories' => $categories
            ]);
            return;
        }
        
        // Valide l'URL de l'image si fournie
        $image_url = $input['image_url'] ?? '';
        if (!empty($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
            $this->render('product/create-product', params: [
                'title' => 'Créer un produit',
                'message' => 'L\'URL de l\'image n\'est pas valide.',
                'success' => false,
                'old_values' => $input,
                'categories' => $categories
            ]);
            return;
        }
        
        // Crée une nouvelle instance Product
        $product = new Product();
        $product->setNom($input['nom']);
        $product->setDescription($input['description'] ?? '');
        $product->setPrix(floatval($input['prix']));
        $product->setStock(intval($input['stock']));
        $product->setImageUrl($image_url);
        $product->setCategorieId(!empty($input['categorie_id']) ? intval($input['categorie_id']) : null);
        
        // Sauvegarde le produit
        if ($product->save()) {
            header('Location: /products?success=created');
        } else {
            $this->render('product/create-product', params: [
                'title' => 'Créer un produit',
                'message' => 'Erreur lors de la création du produit.',
                'success' => false,
                'old_values' => $input,
                'categories' => $categories
            ]);
        }
    }

    /**
     * Affiche le formulaire de modification d'un produit
     */
    public function showEditForm(): void
    {
        // Vérifie et ajoute automatiquement les colonnes manquantes si nécessaire
        MigrationHelper::ensureImageUrlColumnExists();
        MigrationHelper::ensureCategorieIdAllowsNull();
        // Vérifie et insère automatiquement les catégories de beauté si elles n'existent pas
        $this->ensureBeautyCategoriesExist();
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: /products');
            return;
        }
        
        $product = Product::findById($id);
        
        if (!$product) {
            header('Location: /products');
            return;
        }
        
        $categories = Category::getAll();
        
        $this->render('product/edit-product', params: [
            'title' => 'Modifier le produit',
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * Met à jour un produit
     */
    public function update(): void
    {
        // Vérifie et ajoute automatiquement les colonnes manquantes si nécessaire
        MigrationHelper::ensureImageUrlColumnExists();
        MigrationHelper::ensureCategorieIdAllowsNull();
        // Vérifie et insère automatiquement les catégories de beauté si elles n'existent pas
        $this->ensureBeautyCategoriesExist();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /products');
            return;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            header('Location: /products');
            return;
        }
        
        $product = Product::findById($id);
        
        if (!$product) {
            header('Location: /products');
            return;
        }
        
        $input = $_POST;
        $categories = Category::getAll();
        
        // Valide les données requises
        if (empty($input['nom']) || empty($input['prix']) || empty($input['stock'])) {
            $this->render('product/edit-product', params: [
                'title' => 'Modifier le produit',
                'message' => 'Les champs "nom", "prix" et "stock" sont requis.',
                'success' => false,
                'product' => array_merge($product, $input),
                'categories' => $categories
            ]);
            return;
        }
        
        // Valide le prix
        if (!is_numeric($input['prix']) || floatval($input['prix']) < 0) {
            $this->render('product/edit-product', params: [
                'title' => 'Modifier le produit',
                'message' => 'Le prix doit être un nombre positif.',
                'success' => false,
                'product' => array_merge($product, $input),
                'categories' => $categories
            ]);
            return;
        }
        
        // Valide le stock
        if (!is_numeric($input['stock']) || intval($input['stock']) < 0) {
            $this->render('product/edit-product', params: [
                'title' => 'Modifier le produit',
                'message' => 'Le stock doit être un entier positif.',
                'success' => false,
                'product' => array_merge($product, $input),
                'categories' => $categories
            ]);
            return;
        }
        
        // Valide l'URL de l'image si fournie
        $image_url = $input['image_url'] ?? '';
        if (!empty($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
            $this->render('product/edit-product', params: [
                'title' => 'Modifier le produit',
                'message' => 'L\'URL de l\'image n\'est pas valide.',
                'success' => false,
                'product' => array_merge($product, $input),
                'categories' => $categories
            ]);
            return;
        }
        
        // Met à jour le produit
        $productObj = new Product();
        $productObj->setId($id);
        $productObj->setNom($input['nom']);
        $productObj->setDescription($input['description'] ?? '');
        $productObj->setPrix(floatval($input['prix']));
        $productObj->setStock(intval($input['stock']));
        $productObj->setImageUrl($image_url);
        $productObj->setCategorieId(!empty($input['categorie_id']) ? intval($input['categorie_id']) : null);
        
        if ($productObj->update()) {
            header('Location: /products/show?id=' . $id . '&success=updated');
        } else {
            $this->render('product/edit-product', params: [
                'title' => 'Modifier le produit',
                'message' => 'Erreur lors de la mise à jour du produit.',
                'success' => false,
                'product' => array_merge($product, $input),
                'categories' => $categories
            ]);
        }
    }

    /**
     * Supprime un produit
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /products');
            return;
        }
        
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: /products');
            return;
        }
        
        $product = Product::findById($id);
        
        if (!$product) {
            header('Location: /products');
            return;
        }
        
        $productObj = new Product();
        $productObj->setId($id);
        
        if ($productObj->delete()) {
            header('Location: /products?success=deleted');
        } else {
            header('Location: /products/show?id=' . $id . '&error=delete_failed');
        }
    }
}

