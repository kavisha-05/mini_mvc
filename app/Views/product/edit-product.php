<!-- Formulaire pour modifier un produit -->
<div class="form-container fade-in">
    <h2>Modifier le produit</h2>
    
    <!-- Message de succès ou d'erreur -->
    <?php if (isset($message)): ?>
        <div class="alert <?= isset($success) && $success ? 'alert-success' : 'alert-error' ?>">
            <?= isset($success) && $success ? '✅ ' : '❌ ' ?><?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/products/update">
        <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
        
        <div class="form-group">
            <label for="nom">Nom du produit :</label>
            <input 
                type="text" 
                id="nom" 
                name="nom" 
                class="form-control"
                required 
                maxlength="150"
                value="<?= htmlspecialchars($product['nom']) ?>"
                placeholder="Entrez le nom du produit"
            >
        </div>
        
        <div class="form-group">
            <label for="description">Description :</label>
            <textarea 
                id="description" 
                name="description" 
                class="form-control"
                rows="4"
                placeholder="Entrez la description du produit (optionnel)"
            ><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="prix">Prix :</label>
            <input 
                type="number" 
                id="prix" 
                name="prix" 
                class="form-control"
                required 
                step="0.01"
                min="0"
                value="<?= htmlspecialchars($product['prix']) ?>"
                placeholder="0.00"
            >
        </div>
        
        <div class="form-group">
            <label for="stock">Stock :</label>
            <input 
                type="number" 
                id="stock" 
                name="stock" 
                class="form-control"
                required 
                min="0"
                value="<?= htmlspecialchars($product['stock']) ?>"
                placeholder="0"
            >
        </div>
        
        <div class="form-group">
            <label for="categorie_id">Catégorie :</label>
            <select id="categorie_id" name="categorie_id" class="form-control">
                <option value="">-- Sélectionnez une catégorie (optionnel) --</option>
                <?php if (isset($categories)): ?>
                    <?php foreach ($categories as $categorie): ?>
                        <option 
                            value="<?= $categorie['id'] ?>"
                            <?= ($product['categorie_id'] == $categorie['id']) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($categorie['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <small style="display: block; margin-top: 5px; color: var(--gris-fonce);">Sélectionnez la catégorie du produit (optionnel)</small>
        </div>
        
        <div class="form-group">
            <label for="image_url">URL de l'image :</label>
            <input 
                type="url" 
                id="image_url" 
                name="image_url" 
                class="form-control"
                value="<?= htmlspecialchars($product['image_url'] ?? '') ?>"
                placeholder="https://exemple.com/image.jpg"
            >
            <small style="display: block; margin-top: 5px; color: var(--gris-fonce);">Entrez l'URL complète de l'image (optionnel)</small>
        </div>
        
        <!-- Aperçu de l'image si une URL est fournie -->
        <?php if (!empty($product['image_url']) && filter_var($product['image_url'], FILTER_VALIDATE_URL)): ?>
            <div class="form-group">
                <label>Aperçu de l'image :</label>
                <img 
                    src="<?= htmlspecialchars($product['image_url']) ?>" 
                    alt="Aperçu" 
                    style="max-width: 100%; max-height: 300px; border: 3px solid var(--jaune-principal); border-radius: 15px; object-fit: contain; padding: 10px; background: var(--jaune-clair);"
                    onerror="this.style.display='none'"
                >
            </div>
        <?php endif; ?>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">
            Enregistrer les modifications
        </button>
    </form>
    
    <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="/products/show?id=<?= htmlspecialchars($product['id']) ?>" class="btn btn-secondary">← Retour aux détails</a>
        <a href="/products" class="btn btn-secondary">📋 Liste des produits</a>
    </div>
</div>
