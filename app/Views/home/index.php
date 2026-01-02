<!-- Page d'accueil -->
<div class="container fade-in">
    <div class="hero-section">
        <h1>Bienvenue sur notre boutique en ligne 🌸</h1>
        <p>Découvrez notre sélection de produits exceptionnels</p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="/products" class="btn btn-primary">📦 Voir tous les produits</a>
            <a href="/products/create" class="btn btn-success">➕ Ajouter un produit</a>
        </div>
    </div>
    
    <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
        <div class="alert alert-success">
            ✅ Inscription réussie ! Bienvenue sur notre site.
        </div>
    <?php endif; ?>
    
    <?php if (!empty($products)): ?>
        <h2>Derniers produits</h2>
        <div class="products-grid">
            <?php 
            // Affiche les 6 premiers produits
            $displayedProducts = array_slice($products, 0, 6);
            foreach ($displayedProducts as $product): 
            ?>
                <div class="product-card">
                    <!-- Image du produit -->
                    <?php if (!empty($product['image_url'])): ?>
                        <div style="margin-bottom: 15px; text-align: center;">
                            <img 
                                src="<?= htmlspecialchars($product['image_url']) ?>" 
                                alt="<?= htmlspecialchars($product['nom']) ?>" 
                                style="max-width: 100%; max-height: 180px; border-radius: 15px; object-fit: contain;"
                                onerror="this.style.display='none'"
                            >
                        </div>
                    <?php else: ?>
                        <div style="margin-bottom: 15px; text-align: center; padding: 30px; background: var(--jaune-clair); border-radius: 15px;">
                            <span style="color: var(--gris-fonce); font-size: 14px;">Aucune image</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Informations du produit -->
                    <h3><?= htmlspecialchars($product['nom']) ?></h3>
                    
                    <?php if (!empty($product['description'])): ?>
                        <p style="margin: 0 0 15px 0; color: var(--gris-fonce); font-size: 14px; line-height: 1.4; 
                                  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; 
                                  overflow: hidden; text-overflow: ellipsis;">
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
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($products) > 6): ?>
            <div style="text-align: center; margin-top: 40px;">
                <a href="/products" class="btn btn-primary">
                    Voir tous les produits (<?= count($products) ?>)
                </a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; background: var(--jaune-clair); border-radius: 20px; border: 3px solid var(--jaune-principal);">
            <p style="color: var(--rose-fonce); font-size: 20px; margin-bottom: 30px; font-weight: 600;">
                Aucun produit disponible pour le moment.
            </p>
            <a href="/products/create" class="btn btn-success">
                ➕ Créer le premier produit
            </a>
        </div>
    <?php endif; ?>
</div>
