<!-- Vue du panier -->
<div class="container fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h2>Mon panier 🛒</h2>
        <a href="/products" class="btn btn-primary">← Continuer les achats</a>
    </div>
    
    <?php
    // Utilise l'ID de l'utilisateur connecté ou celui en paramètre
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $current_user_id = $_SESSION['user_id'] ?? $_GET['user_id'] ?? $user_id ?? 1;
    ?>
    
    <?php 
    // Utilise l'ID de l'utilisateur connecté ou celui en paramètre
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $current_user_id = $_SESSION['user_id'] ?? $_GET['user_id'] ?? $user_id ?? 1;
    ?>
    
    <!-- Messages de succès/erreur -->
    <?php if (isset($message) && $message): ?>
        <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?>">
            <?= $messageType === 'success' ? '✅ ' : '❌ ' ?><?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error" style="background: linear-gradient(135deg, #ffe0e6 0%, #ffb3d9 100%); border: 3px solid var(--blush-pop); padding: 20px; border-radius: 15px; margin-bottom: 25px;">
            <?php
            if ($_GET['error'] === 'empty_cart') {
                echo '<strong>❌ Votre panier est vide</strong><br>Impossible de valider la commande. Ajoutez des produits à votre panier.';
            } elseif ($_GET['error'] === 'create_failed') {
                $errorMsg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Erreur inconnue';
                echo '<strong>❌ Erreur lors de la création de la commande</strong><br>';
                echo 'Détails: ' . $errorMsg . '<br>';
                echo 'Veuillez réessayer ou contacter le support si le problème persiste.';
            } else {
                echo '<strong>❌ Une erreur est survenue</strong><br>Veuillez réessayer.';
            }
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php
            if ($_GET['success'] === 'added') {
                echo '✅ Produit ajouté au panier avec succès !';
            } elseif ($_GET['success'] === 'updated') {
                echo '✅ Panier mis à jour avec succès !';
            } elseif ($_GET['success'] === 'removed') {
                echo '✅ Produit retiré du panier avec succès !';
            } elseif ($_GET['success'] === 'cleared') {
                echo '✅ Panier vidé avec succès !';
            }
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($cartItems)): ?>
        <div style="text-align: center; padding: 80px; background: var(--jaune-clair); border-radius: 20px; border: 3px solid var(--jaune-principal);">
            <div style="font-size: 80px; margin-bottom: 20px;">🛒</div>
            <h3 style="color: var(--rose-fonce); margin-bottom: 15px; font-size: 24px;">Votre panier est vide</h3>
            <p style="color: var(--gris-fonce); margin-bottom: 30px; font-size: 18px;">Ajoutez des produits à votre panier pour commencer vos achats.</p>
            <a href="/products" class="btn btn-primary">
                Voir les produits
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <!-- Liste des articles -->
            <div>
                <h3 style="margin-bottom: 25px; color: var(--rose-fonce); font-size: 24px;">Articles dans votre panier</h3>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <!-- Image du produit -->
                            <div style="width: 120px; height: 120px; flex-shrink: 0;">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img 
                                        src="<?= htmlspecialchars($item['image_url']) ?>" 
                                        alt="<?= htmlspecialchars($item['nom']) ?>" 
                                        style="width: 100%; height: 100%; object-fit: contain; border-radius: 15px; border: 3px solid var(--jaune-principal); padding: 5px; background: var(--jaune-clair);"
                                        onerror="this.style.display='none'"
                                    >
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; background: var(--jaune-clair); border-radius: 15px; display: flex; align-items: center; justify-content: center; border: 3px solid var(--jaune-principal);">
                                        <span style="color: var(--gris-fonce); font-size: 12px; font-weight: 600;">Pas d'image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Informations du produit -->
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 10px 0; font-size: 20px;">
                                    <a href="/products/show?id=<?= htmlspecialchars($item['id']) ?>" style="color: var(--rose-fonce); text-decoration: none;">
                                        <?= htmlspecialchars($item['nom']) ?>
                                    </a>
                                </h4>
                                
                                <?php if (!empty($item['categorie_nom'])): ?>
                                    <div style="margin-bottom: 10px;">
                                        <span class="badge badge-category">📁 <?= htmlspecialchars($item['categorie_nom']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-price" style="font-size: 24px; margin-bottom: 15px;">
                                    <?= number_format((float)$item['prix'], 2, ',', ' ') ?> €
                                </div>
                                
                                <!-- Gestion de la quantité -->
                                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                    <form method="POST" action="/cart/update" style="display: flex; align-items: center; gap: 10px;">
                                        <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['panier_id']) ?>">
                                        <label for="quantite_<?= htmlspecialchars($item['panier_id']) ?>" style="font-size: 14px; color: var(--gris-fonce); font-weight: 600;">Quantité :</label>
                                        <input 
                                            type="number" 
                                            id="quantite_<?= htmlspecialchars($item['panier_id']) ?>" 
                                            name="quantite" 
                                            value="<?= htmlspecialchars($item['quantite']) ?>" 
                                            min="1" 
                                            max="<?= htmlspecialchars($item['stock']) ?>"
                                            class="form-control"
                                            style="width: 80px;"
                                            required
                                        >
                                        <button type="submit" class="btn btn-info btn-small">
                                            Mettre à jour
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="/cart/remove" style="margin: 0;">
                                        <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['panier_id']) ?>">
                                        <button 
                                            type="submit" 
                                            class="btn btn-danger btn-small"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')"
                                        >
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </div>
                                
                                <div style="margin-top: 15px; font-size: 16px; color: var(--gris-fonce); padding: 10px; background: var(--jaune-clair); border-radius: 8px; border: 2px solid var(--jaune-principal);">
                                    Sous-total : <strong style="color: var(--rose-fonce);"><?= number_format((float)$item['prix'] * (int)$item['quantite'], 2, ',', ' ') ?> €</strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Bouton vider le panier -->
                <div style="margin-top: 25px;">
                    <form method="POST" action="/cart/clear">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($current_user_id) ?>">
                        <button 
                            type="submit" 
                            class="btn btn-danger"
                            onclick="return confirm('Êtes-vous sûr de vouloir vider tout votre panier ?')"
                        >
                            🗑️ Vider le panier
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Résumé de la commande -->
            <div>
                <div style="border: 3px solid var(--rose-clair); border-radius: 20px; padding: 30px; background: var(--blanc); position: sticky; top: 20px; box-shadow: 0 10px 30px rgba(255, 107, 157, 0.2);">
                    <h3 style="margin: 0 0 25px 0; color: var(--rose-fonce); font-size: 24px; text-align: center;">Résumé de la commande</h3>
                    
                    <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid var(--rose-clair);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: var(--gris-fonce); font-size: 16px;">Sous-total :</span>
                            <span style="font-weight: bold; color: var(--rose-fonce); font-size: 18px;"><?= number_format((float)$total, 2, ',', ' ') ?> €</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--gris-fonce); font-size: 16px;">Frais de livraison :</span>
                            <span style="font-weight: bold; color: var(--jaune-fonce);">Gratuit</span>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 30px; padding: 20px; background: var(--rose-principal); border-radius: 15px; border: 3px solid var(--jaune-principal);">
                        <span style="font-size: 22px; font-weight: bold; color: var(--blanc);">Total :</span>
                        <span style="font-size: 28px; font-weight: bold; color: var(--blanc);"><?= number_format((float)$total, 2, ',', ' ') ?> €</span>
                    </div>
                    
                    <form method="POST" action="/orders/create">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($current_user_id) ?>">
                        <button type="submit" class="btn btn-success" style="width: 100%; font-size: 18px; padding: 15px;">
                            ✅ Valider la commande
                        </button>
                    </form>
                    
                    <div style="margin-top: 20px; text-align: center;">
                        <a href="/products" class="btn btn-secondary" style="width: 100%;">
                            ← Continuer les achats
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
