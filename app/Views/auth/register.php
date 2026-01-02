<!-- Formulaire d'inscription -->
<div class="form-container fade-in">
    <h2>Inscription</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            ❌ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/auth/register">
        <div class="form-group">
            <label for="nom">Nom :</label>
            <input 
                type="text" 
                id="nom" 
                name="nom" 
                class="form-control"
                value="<?= htmlspecialchars($nom ?? '') ?>"
                required 
                placeholder="Votre nom"
            >
        </div>
        
        <div class="form-group">
            <label for="email">Email :</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-control"
                value="<?= htmlspecialchars($email ?? '') ?>"
                required 
                placeholder="exemple@email.com"
            >
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control"
                required 
                minlength="6"
                placeholder="Au moins 6 caractères"
            >
            <small style="display: block; margin-top: 5px; color: var(--gris-fonce);">Le mot de passe doit contenir au moins 6 caractères</small>
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Confirmer le mot de passe :</label>
            <input 
                type="password" 
                id="password_confirm" 
                name="password_confirm" 
                class="form-control"
                required 
                minlength="6"
                placeholder="Répétez le mot de passe"
            >
        </div>
        
        <button type="submit" class="btn btn-success" style="width: 100%;">
            S'inscrire
        </button>
    </form>
    
    <div style="margin-top: 30px; text-align: center; padding-top: 25px; border-top: 2px solid var(--rose-clair);">
        <p style="color: var(--gris-fonce); margin-bottom: 15px; font-size: 16px;">Déjà un compte ?</p>
        <a href="/auth/login" class="btn btn-primary">
            Se connecter
        </a>
    </div>
    
    <div style="margin-top: 20px; text-align: center;">
        <a href="/" class="btn btn-secondary">← Retour à l'accueil</a>
    </div>
</div>
