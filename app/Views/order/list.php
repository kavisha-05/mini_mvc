<!-- Liste des commandes -->
<div class="container fade-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h2>Mes commandes 📋</h2>
        <a href="/products" class="btn btn-primary">
            ← Retour aux produits
        </a>
    </div>
    
    <?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
        <div class="alert alert-success" style="background: linear-gradient(135deg, var(--petal-frost) 0%, var(--blush-pop) 100%); border: 3px solid var(--blush-pop); padding: 20px; border-radius: 15px; margin-bottom: 25px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
            <h3 style="color: var(--rose-intense-menu); margin: 0; font-size: 24px; font-weight: 700;">
                Commande validée avec succès !
            </h3>
            <p style="color: var(--gris-fonce); margin: 10px 0 0 0; font-size: 16px;">
                Votre commande a été enregistrée et apparaît ci-dessous dans votre historique.
            </p>
        </div>
    <?php endif; ?>
    
    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 80px; background: var(--petal-frost); border-radius: 20px; border: 3px solid var(--blush-pop);">
            <div style="font-size: 80px; margin-bottom: 20px;">📋</div>
            <h3 style="color: var(--rose-intense-menu); margin-bottom: 15px; font-size: 24px;">Aucune commande</h3>
            <p style="color: var(--gris-fonce); margin-bottom: 30px; font-size: 18px;">Vous n'avez pas encore passé de commande.</p>
            <a href="/products" class="btn btn-primary">
                Voir les produits
            </a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 25px;">
            <?php foreach ($orders as $order): ?>
                <div class="cart-item" style="padding: 30px;">
                    <!-- En-tête de la commande -->
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 25px; flex-wrap: wrap; gap: 20px; padding-bottom: 20px; border-bottom: 3px solid var(--blush-pop);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
                                <h3 style="margin: 0; color: var(--rose-intense-menu); font-size: 26px; font-weight: 700;">
                                    Commande #<?= htmlspecialchars($order['id']) ?>
                                </h3>
                                <span class="badge" style="
                                    padding: 8px 16px;
                                    border-radius: 20px;
                                    font-size: 14px;
                                    font-weight: bold;
                                    <?php 
                                    if ($order['statut'] === 'validee') {
                                        echo 'background: var(--jaune-clair); color: var(--jaune-fonce); border: 2px solid var(--jaune-principal);';
                                    } elseif ($order['statut'] === 'en_attente') {
                                        echo 'background: var(--petal-frost); color: var(--rose-intense-menu); border: 2px solid var(--blush-pop);';
                                    } elseif ($order['statut'] === 'annulee') {
                                        echo 'background: #ffe0e6; color: var(--rose-intense-menu); border: 2px solid var(--blush-pop);';
                                    }
                                    ?>
                                ">
                                    <?php 
                                        if ($order['statut'] === 'validee') {
                                            echo '✅ Validée';
                                        } elseif ($order['statut'] === 'en_attente') {
                                            echo '⏳ En attente';
                                        } elseif ($order['statut'] === 'annulee') {
                                            echo '❌ Annulée';
                                        } else {
                                            echo htmlspecialchars($order['statut']);
                                        }
                                    ?>
                                </span>
                            </div>
                            
                            <div style="color: var(--gris-fonce); font-size: 16px; margin-bottom: 10px;">
                                <strong>Date de commande :</strong> 
                                <?= isset($order['created_at']) && $order['created_at'] ? date('d/m/Y à H:i', strtotime($order['created_at'])) : 'Date non disponible' ?>
                            </div>
                            
                            <div class="product-price" style="font-size: 32px; margin: 10px 0;">
                                Total : <?= number_format((float)$order['total'], 2, ',', ' ') ?> €
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <a href="/orders/show?id=<?= htmlspecialchars($order['id']) ?>" class="btn btn-info btn-small">
                                👁️ Voir les détails
                            </a>
                        </div>
                    </div>
                    
                    <!-- Produits de la commande -->
                    <?php if (!empty($order['products'])): ?>
                        <div style="margin-top: 20px;">
                            <h4 style="color: var(--rose-intense-menu); font-size: 20px; margin-bottom: 15px; font-weight: 700;">
                                Produits commandés (<?= count($order['products']) ?>)
                            </h4>
                            <div style="display: flex; flex-direction: column; gap: 15px;">
                                <?php foreach ($order['products'] as $product): ?>
                                    <div style="display: flex; gap: 15px; padding: 15px; background: var(--petal-frost); border-radius: 15px; border: 2px solid var(--blush-pop); align-items: center; flex-wrap: wrap;">
                                        <!-- Image du produit -->
                                        <div style="width: 80px; height: 80px; flex-shrink: 0;">
                                            <?php if (!empty($product['image_url'])): ?>
                                                <img 
                                                    src="<?= htmlspecialchars($product['image_url']) ?>" 
                                                    alt="<?= htmlspecialchars($product['product_nom']) ?>" 
                                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px; border: 2px solid var(--blush-pop);"
                                                    onerror="this.style.display='none'"
                                                >
                                            <?php else: ?>
                                                <div style="width: 100%; height: 100%; background: var(--mint-cream); border-radius: 10px; border: 2px solid var(--blush-pop); display: flex; align-items: center; justify-content: center; color: var(--gris-fonce); font-size: 12px;">
                                                    Pas d'image
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Informations du produit -->
                                        <div style="flex: 1; min-width: 200px;">
                                            <h5 style="margin: 0 0 8px 0; color: var(--rose-intense-menu); font-size: 18px; font-weight: 700;">
                                                <?= htmlspecialchars($product['product_nom']) ?>
                                            </h5>
                                            <?php if (!empty($product['categorie_nom'])): ?>
                                                <span class="badge badge-category" style="font-size: 12px; padding: 4px 10px; margin-bottom: 5px; display: inline-block;">
                                                    📁 <?= htmlspecialchars($product['categorie_nom']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Quantité et prix -->
                                        <div style="text-align: right; min-width: 150px;">
                                            <div style="color: var(--gris-fonce); font-size: 14px; margin-bottom: 5px;">
                                                Quantité : <strong style="color: var(--rose-intense-menu);"><?= htmlspecialchars($product['quantite']) ?></strong>
                                            </div>
                                            <div style="color: var(--gris-fonce); font-size: 14px; margin-bottom: 5px;">
                                                Prix unitaire : <strong style="color: var(--rose-intense-menu);"><?= number_format((float)$product['prix_unitaire'], 2, ',', ' ') ?> €</strong>
                                            </div>
                                            <div style="font-size: 18px; font-weight: bold; color: var(--rose-intense-menu);">
                                                Sous-total : <?= number_format((float)$product['prix_unitaire'] * (int)$product['quantite'], 2, ',', ' ') ?> €
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="padding: 20px; background: var(--petal-frost); border-radius: 15px; border: 2px solid var(--blush-pop); text-align: center; color: var(--gris-fonce);">
                            Aucun produit trouvé pour cette commande.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
