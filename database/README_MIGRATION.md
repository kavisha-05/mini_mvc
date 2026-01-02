# Guide de migration de la base de données

## Problèmes courants

Si vous rencontrez ces erreurs :
- `Table 'mini_mvc.panier' doesn't exist`
- `Unknown column 'user_id' in 'where clause'` dans la table commande

## Solution rapide

Exécutez le fichier SQL suivant dans votre base de données :

### Option 1 : Via phpMyAdmin ou un client MySQL

1. Ouvrez phpMyAdmin ou votre client MySQL préféré
2. Sélectionnez votre base de données `mini_mvc`
3. Allez dans l'onglet "SQL"
4. Copiez-collez le contenu du fichier `database/fix_tables.sql`
5. Cliquez sur "Exécuter"

### Option 2 : Via la ligne de commande MySQL

```bash
mysql -u root -p mini_mvc < database/fix_tables.sql
```

### Option 3 : Exécution manuelle

Si vous préférez exécuter les commandes une par une, voici ce qu'il faut faire :

#### 1. Créer la table panier

```sql
CREATE TABLE IF NOT EXISTS panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_panier_user 
        FOREIGN KEY (user_id) 
        REFERENCES user(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_panier_produit 
        FOREIGN KEY (product_id) 
        REFERENCES produit(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### 2. Créer la table commande

```sql
CREATE TABLE IF NOT EXISTS commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    statut ENUM('en_attente', 'validee', 'annulee') DEFAULT 'en_attente',
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_commande_user 
        FOREIGN KEY (user_id) 
        REFERENCES user(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### 3. Créer la table commande_produit

```sql
CREATE TABLE IF NOT EXISTS commande_produit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    product_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_commande_produit_commande 
        FOREIGN KEY (commande_id) 
        REFERENCES commande(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_commande_produit_produit 
        FOREIGN KEY (product_id) 
        REFERENCES produit(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Vérification

Après avoir exécuté les migrations, vérifiez que les tables existent :

```sql
SHOW TABLES;
```

Vous devriez voir :
- `panier`
- `commande`
- `commande_produit`

## Si la table commande existe mais sans user_id

Si la table `commande` existe déjà mais sans la colonne `user_id`, exécutez :

```sql
ALTER TABLE commande ADD COLUMN user_id INT NOT NULL AFTER id;
ALTER TABLE commande ADD CONSTRAINT fk_commande_user 
    FOREIGN KEY (user_id) 
    REFERENCES user(id) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE;
```

## Notes importantes

- Assurez-vous que les tables `user` et `produit` existent avant d'exécuter ces migrations
- Les contraintes de clés étrangères nécessitent que les tables référencées existent
- Si vous avez des données existantes, faites une sauvegarde avant d'exécuter les migrations

