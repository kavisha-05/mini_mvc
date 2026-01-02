<!-- Détails du produit -->
<div class="container fade-in">
    <?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
        <div class="alert alert-success">
            ✅ Produit modifié avec succès !
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error']) && $_GET['error'] === 'delete_failed'): ?>
        <div class="alert alert-error">
            ❌ Erreur lors de la suppression du produit.
        </div>
    <?php endif; ?>
    
    <?php if (!$product): ?>
        <div style="text-align: center; padding: 60px; background: #ffe0e6; border-radius: 20px; border: 3px solid var(--rose-fonce);">
            <h2 style="color: var(--rose-fonce);">Produit introuvable</h2>
            <p style="color: var(--rose-fonce); margin: 20px 0;">Le produit que vous recherchez n'existe pas ou a été supprimé.</p>
            <a href="/products" class="btn btn-primary">← Retour à la liste des produits</a>
        </div>
    <?php else: ?>
        <div class="product-detail">
            <!-- Image du produit -->
            <div>
                <?php if (!empty($product['image_url'])): ?>
                    <img 
                        src="<?= htmlspecialchars($product['image_url']) ?>" 
                        alt="<?= htmlspecialchars($product['nom']) ?>" 
                        style="width: 100%; max-height: 500px; border-radius: 20px; object-fit: contain;"
                        onerror="this.style.display='none'"
                    >
                <?php else: ?>
                    <div style="width: 100%; height: 400px; background: var(--jaune-clair); border-radius: 20px; display: flex; align-items: center; justify-content: center; border: 3px solid var(--jaune-principal);">
                        <span style="color: var(--gris-fonce); font-size: 18px; font-weight: 600;">Aucune image disponible</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Informations du produit -->
            <div>
                <h1 style="margin: 0 0 20px 0; font-size: 36px;">
                    <?= htmlspecialchars($product['nom']) ?>
                </h1>
                
                <?php if (!empty($product['categorie_nom'])): ?>
                    <div style="margin-bottom: 20px;">
                        <span class="badge badge-category" style="font-size: 16px; padding: 8px 16px;">
                            📁 <?= htmlspecialchars($product['categorie_nom']) ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <div style="margin-bottom: 30px;">
                    <div class="product-price" style="font-size: 42px; margin-bottom: 15px;">
                        <?= number_format((float)$product['prix'], 2, ',', ' ') ?> €
                    </div>
                    <div style="font-size: 18px; font-weight: bold; padding: 12px 20px; border-radius: 12px; display: inline-block; 
                                background: <?= $product['stock'] > 0 ? 'var(--jaune-clair)' : '#ffe0e6' ?>; 
                                color: <?= $product['stock'] > 0 ? 'var(--jaune-fonce)' : 'var(--rose-fonce)' ?>; 
                                border: 2px solid <?= $product['stock'] > 0 ? 'var(--jaune-principal)' : 'var(--rose-fonce)' ?>;">
                        <?php if ($product['stock'] > 0): ?>
                            ✅ En stock (<?= htmlspecialchars($product['stock']) ?> disponible<?= $product['stock'] > 1 ? 's' : '' ?>)
                        <?php else: ?>
                            ❌ Stock épuisé
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($product['description'])): ?>
                    <div style="margin-bottom: 30px; padding: 25px; background: var(--jaune-clair); border-radius: 15px; border: 2px solid var(--jaune-principal);">
                        <h3 style="margin: 0 0 15px 0; color: var(--rose-fonce); font-size: 22px;">Description</h3>
                        <p style="margin: 0; color: var(--gris-fonce); line-height: 1.8; white-space: pre-wrap; font-size: 16px;">
                            <?= htmlspecialchars($product['description']) ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <!-- Formulaire d'ajout au panier -->
                <?php if ($product['stock'] > 0): ?>
                    <form method="POST" action="/cart/add-from-form" style="margin-top: 30px;">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($_SESSION['user_id']) ?>">
                        <?php endif; ?>
                        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                            <label for="quantite" style="font-weight: 600; color: var(--rose-fonce); font-size: 18px;">Quantité :</label>
                            <input 
                                type="number" 
                                id="quantite" 
                                name="quantite" 
                                value="1" 
                                min="1" 
                                max="<?= htmlspecialchars($product['stock']) ?>"
                                class="form-control"
                                style="width: 100px;"
                                required
                            >
                            <button type="submit" class="btn btn-success" style="flex: 1; min-width: 200px;">
                                🛒 Ajouter au panier
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-error" style="margin-top: 30px;">
                        ⚠️ Ce produit n'est actuellement pas disponible en stock.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="margin-top: 40px; padding-top: 30px; border-top: 3px solid var(--rose-clair);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                <a href="/products" class="btn btn-secondary">← Retour à la liste des produits</a>
                <a href="/cart<?= isset($_SESSION['user_id']) ? '?user_id=' . htmlspecialchars($_SESSION['user_id']) : '' ?>" class="btn btn-warning">🛒 Voir mon panier</a>
            </div>
            
            <div class="btn-group">
                <a href="/products/edit?id=<?= htmlspecialchars($product['id']) ?>" class="btn btn-info">
                    ✏️ Modifier
                </a>
                <form method="POST" action="/products/delete" style="flex: 1; margin: 0;" 
                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.');">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
                    <button type="submit" class="btn btn-danger" style="width: 100%;">
                        🗑️ Supprimer
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
