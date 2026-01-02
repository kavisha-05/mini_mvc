# Modèle Conceptuel de Données (MCD) et Modèle Logique de Données (MLD)

## Modèle Conceptuel de Données (MCD)

### Entités

#### USER (Utilisateur)
- **id** : Identifiant unique (PK)
- **nom** : Nom de l'utilisateur
- **email** : Adresse email (unique)
- **password** : Mot de passe (haché)
- **created_at** : Date de création
- **updated_at** : Date de mise à jour

#### CATEGORIE
- **id** : Identifiant unique (PK)
- **nom** : Nom de la catégorie (unique)

#### PRODUIT
- **id** : Identifiant unique (PK)
- **nom** : Nom du produit
- **description** : Description du produit
- **prix** : Prix unitaire
- **stock** : Quantité en stock
- **image_url** : URL de l'image du produit
- **created_at** : Date de création
- **updated_at** : Date de mise à jour

#### PANIER
- **id** : Identifiant unique (PK)
- **quantite** : Quantité du produit dans le panier
- **created_at** : Date d'ajout au panier

#### COMMANDE
- **id** : Identifiant unique (PK)
- **statut** : Statut de la commande (en_attente, validee, annulee)
- **total** : Montant total de la commande
- **created_at** : Date de création
- **updated_at** : Date de mise à jour

#### COMMANDE_PRODUIT (Table de liaison)
- **id** : Identifiant unique (PK)
- **quantite** : Quantité du produit dans la commande
- **prix_unitaire** : Prix unitaire au moment de la commande
- **created_at** : Date de création

### Relations

1. **USER** → **PANIER** (1, N)
   - Un utilisateur peut avoir plusieurs articles dans son panier
   - Un article du panier appartient à un seul utilisateur

2. **USER** → **COMMANDE** (1, N)
   - Un utilisateur peut avoir plusieurs commandes
   - Une commande appartient à un seul utilisateur

3. **CATEGORIE** → **PRODUIT** (1, N)
   - Une catégorie peut contenir plusieurs produits
   - Un produit appartient à une seule catégorie (ou aucune)

4. **PRODUIT** → **PANIER** (1, N)
   - Un produit peut être dans plusieurs paniers
   - Un article du panier référence un seul produit

5. **COMMANDE** → **COMMANDE_PRODUIT** (1, N)
   - Une commande peut contenir plusieurs lignes de produits
   - Une ligne de commande appartient à une seule commande

6. **PRODUIT** → **COMMANDE_PRODUIT** (1, N)
   - Un produit peut apparaître dans plusieurs commandes
   - Une ligne de commande référence un seul produit

---

## Modèle Logique de Données (MLD)

### Table: user

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(255) | NOT NULL | Nom de l'utilisateur |
| email | VARCHAR(255) | NOT NULL, UNIQUE | Adresse email |
| password | VARCHAR(255) | NULL | Mot de passe haché |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Date de mise à jour |

**Index:**
- PRIMARY KEY (id)
- UNIQUE KEY (email)

---

### Table: categorie

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(255) | NOT NULL, UNIQUE | Nom de la catégorie |

**Index:**
- PRIMARY KEY (id)
- UNIQUE KEY (nom)

---

### Table: produit

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(255) | NOT NULL | Nom du produit |
| description | TEXT | NULL | Description du produit |
| prix | DECIMAL(10,2) | NOT NULL | Prix unitaire |
| stock | INT | NOT NULL, DEFAULT 0 | Quantité en stock |
| image_url | VARCHAR(500) | NULL | URL de l'image |
| categorie_id | INT | NULL, FK → categorie(id) | Référence à la catégorie |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Date de mise à jour |

**Index:**
- PRIMARY KEY (id)
- FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE SET NULL ON UPDATE CASCADE

---

### Table: panier

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| user_id | INT | NOT NULL, FK → user(id) | Référence à l'utilisateur |
| product_id | INT | NOT NULL, FK → produit(id) | Référence au produit |
| quantite | INT | NOT NULL, DEFAULT 1 | Quantité dans le panier |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date d'ajout |

**Index:**
- PRIMARY KEY (id)
- UNIQUE KEY unique_user_product (user_id, product_id)
- FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
- FOREIGN KEY (product_id) REFERENCES produit(id) ON DELETE CASCADE ON UPDATE CASCADE

---

### Table: commande

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| user_id | INT | NOT NULL, FK → user(id) | Référence à l'utilisateur |
| statut | ENUM | NOT NULL, DEFAULT 'en_attente' | Statut: en_attente, validee, annulee |
| total | DECIMAL(10,2) | NOT NULL, DEFAULT 0.00 | Montant total |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Date de mise à jour |

**Index:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE

---

### Table: commande_produit

| Colonne | Type | Contraintes | Description |
|---------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| commande_id | INT | NOT NULL, FK → commande(id) | Référence à la commande |
| product_id | INT | NOT NULL, FK → produit(id) | Référence au produit |
| quantite | INT | NOT NULL, DEFAULT 1 | Quantité commandée |
| prix_unitaire | DECIMAL(10,2) | NOT NULL | Prix unitaire au moment de la commande |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |

**Index:**
- PRIMARY KEY (id)
- FOREIGN KEY (commande_id) REFERENCES commande(id) ON DELETE CASCADE ON UPDATE CASCADE
- FOREIGN KEY (product_id) REFERENCES produit(id) ON DELETE CASCADE ON UPDATE CASCADE

---

## Schéma relationnel (notation textuelle)

```
USER (id, nom, email, password, created_at, updated_at)
    PK: id
    UK: email

CATEGORIE (id, nom)
    PK: id
    UK: nom

PRODUIT (id, nom, description, prix, stock, image_url, categorie_id, created_at, updated_at)
    PK: id
    FK: categorie_id → CATEGORIE(id)

PANIER (id, user_id, product_id, quantite, created_at)
    PK: id
    FK: user_id → USER(id)
    FK: product_id → PRODUIT(id)
    UK: (user_id, product_id)

COMMANDE (id, user_id, statut, total, created_at, updated_at)
    PK: id
    FK: user_id → USER(id)

COMMANDE_PRODUIT (id, commande_id, product_id, quantite, prix_unitaire, created_at)
    PK: id
    FK: commande_id → COMMANDE(id)
    FK: product_id → PRODUIT(id)
```

---

## Notes importantes

1. **Contraintes d'intégrité référentielle:**
   - CASCADE: Lorsqu'un utilisateur est supprimé, ses paniers et commandes sont également supprimés
   - SET NULL: Lorsqu'une catégorie est supprimée, les produits gardent leur référence mais categorie_id devient NULL

2. **Contrainte unique sur panier:**
   - Un utilisateur ne peut avoir qu'une seule ligne par produit dans son panier (quantité mise à jour si le produit est réajouté)

3. **Table de liaison commande_produit:**
   - Stocke le prix_unitaire au moment de la commande (historisation du prix)
   - Permet de conserver le prix même si le produit change de prix par la suite

4. **Statut de commande:**
   - en_attente: Commande en cours de traitement
   - validee: Commande validée et confirmée
   - annulee: Commande annulée

