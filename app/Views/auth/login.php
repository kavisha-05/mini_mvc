<!-- Formulaire de connexion -->
<div class="form-container fade-in">
    <h2>Connexion</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            ❌ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
        <div class="alert alert-success">
            ✅ Inscription réussie ! Vous pouvez maintenant vous connecter.
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/auth/login">
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
                placeholder="Votre mot de passe"
            >
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">
            Se connecter
        </button>
    </form>
    
    <div style="margin-top: 30px; text-align: center; padding-top: 25px; border-top: 2px solid var(--rose-clair);">
        <p style="color: var(--gris-fonce); margin-bottom: 15px; font-size: 16px;">Pas encore de compte ?</p>
        <a href="/auth/register" class="btn btn-success">
            Créer un compte
        </a>
    </div>
    
    <div style="margin-top: 20px; text-align: center;">
        <a href="/" class="btn btn-secondary">← Retour à l'accueil</a>
    </div>
</div>
