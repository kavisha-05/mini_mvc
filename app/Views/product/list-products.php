<!-- Liste des produits -->
<div class="container fade-in">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php if ($_GET['success'] === 'created'): ?>
                ✅ Produit créé avec succès !
            <?php elseif ($_GET['success'] === 'deleted'): ?>
                ✅ Produit supprimé avec succès !
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h2>Liste des produits</h2>
        <a href="/products/create" class="btn btn-primary">➕ Ajouter un produit</a>
    </div>
    
    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 60px; background: var(--jaune-clair); border-radius: 20px; border: 3px solid var(--jaune-principal);">
            <p style="color: var(--rose-fonce); font-size: 20px; margin-bottom: 20px; font-weight: 600;">Aucun produit disponible.</p>
            <a href="/products/create" class="btn btn-success">Créer le premier produit</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <!-- Image du produit -->
                    <?php if (!empty($product['image_url'])): ?>
                        <div style="margin-bottom: 15px; text-align: center;">
                            <img 
                                src="<?= htmlspecialchars($product['image_url']) ?>" 
                                alt="<?= htmlspecialchars($product['nom']) ?>" 
                                style="max-width: 100%; max-height: 200px; border-radius: 15px; object-fit: contain;"
                                onerror="this.style.display='none'"
                            >
                        </div>
                    <?php else: ?>
                        <div style="margin-bottom: 15px; text-align: center; padding: 40px; background: var(--jaune-clair); border-radius: 15px;">
                            <span style="color: var(--gris-fonce); font-size: 14px;">Aucune image</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Informations du produit -->
                    <h3><?= htmlspecialchars($product['nom']) ?></h3>
                    
                    <?php if (!empty($product['description'])): ?>
                        <p style="margin: 0 0 15px 0; color: var(--gris-fonce); font-size: 14px; line-height: 1.5;">
                            <?= htmlspecialchars($product['description']) ?>
                        </p>
                    <?php endif; ?>
                    
                    <div style="margin: 15px 0;">
                        <div class="product-price">
                            <?= number_format((float)$product['prix'], 2, ',', ' ') ?> €
                        </div>
                        <div style="font-size: 12px; color: var(--gris-fonce); margin-top: 5px;">
                            Stock: <?= htmlspecialchars($product['stock']) ?>
                        </div>
                        <?php if (!empty($product['categorie_nom'])): ?>
                            <span class="badge badge-category" style="margin-top: 8px; display: inline-block;">
                                📁 <?= htmlspecialchars($product['categorie_nom']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="btn-group">
                        <a href="/products/show?id=<?= htmlspecialchars($product['id']) ?>" class="btn btn-secondary btn-small">
                            👁️ Détails
                        </a>
                        <form method="POST" action="/cart/add-from-form" style="flex: 1; margin: 0;">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                            <input type="hidden" name="quantite" value="1">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($_SESSION['user_id']) ?>">
                            <?php endif; ?>
                            <button type="submit" 
                                    class="btn btn-success btn-small"
                                    style="width: 100%;"
                                    <?= $product['stock'] <= 0 ? 'disabled title="Stock épuisé"' : '' ?>>
                                🛒 Ajouter
                            </button>
                        </form>
                    </div>
                    
                    <div class="btn-group" style="margin-top: 10px;">
                        <a href="/products/edit?id=<?= htmlspecialchars($product['id']) ?>" class="btn btn-info btn-small">
                            ✏️ Modifier
                        </a>
                        <form method="POST" action="/products/delete" style="flex: 1; margin: 0;"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
                            <button type="submit" class="btn btn-danger btn-small" style="width: 100%;">
                                🗑️ Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 40px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <a href="/" class="btn btn-secondary">← Retour à l'accueil</a>
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $cart_user_id = $_SESSION['user_id'] ?? 1;
        ?>
        <a href="/cart?user_id=<?= htmlspecialchars($cart_user_id) ?>" class="btn btn-warning">🛒 Voir mon panier</a>
    </div>
</div>
