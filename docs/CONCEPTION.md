Phase 1 – Conception de la base de données
1. Pourquoi stocker le prix unitaire dans la table des lignes de commande plutôt que d'utiliser directement le prix du produit ?

J’ai choisi de stocker le prix unitaire dans la table lignes_commande pour garder un historique exact du prix au moment de la commande.
Si je n’utilisais que le prix du produit dans produits, toute modification ultérieure du prix affecterait les anciennes commandes, ce qui fausserait le montant total et les rapports financiers.

2. Quelle stratégie avez-vous choisie pour gérer les suppressions ?

Pour chaque relation j’ai fait les choix suivants :

Produits → Catégories : ON DELETE SET NULL

Si une catégorie est supprimée, les produits restent mais leur catégorie devient NULL.

Lignes de commande → Commandes et Produits : ON DELETE CASCADE

Si une commande est supprimée, toutes ses lignes sont automatiquement supprimées.

Si un produit est supprimé, ses lignes associées sont supprimées pour garder l’intégrité.

Clients → Commandes : ON DELETE CASCADE

Si un client est supprimé, toutes ses commandes et lignes de commande sont supprimées automatiquement.

Produits et commandes : j’ai aussi ajouté un champ actif pour soft delete de produit afin de pouvoir le retirer temporairement sans supprimer les lignes de commande existantes.

3. Comment gérez-vous les stocks ?

Lorsqu’un client passe une commande, je ne décrémente pas immédiatement le stock.

Le stock est décrémenté seulement après la validation et le paiement de la commande, afin d’éviter les problèmes si le client abandonne son panier.

Si un client tente de commander un produit en rupture de stock, la commande est bloquée ou un message l’informe que la quantité demandée n’est pas disponible.

4. Avez-vous prévu des index ? Lesquels et pourquoi ?

Oui, j’ai mis des index pour optimiser les requêtes fréquentes :

email dans clients → UNIQUE, pour accélérer la recherche et garantir l’unicité.

id_categorie dans produits → pour les recherches par catégorie.

num_commande dans commandes → UNIQUE, pour retrouver rapidement une commande par son numéro.

id_client dans commandes → pour retrouver toutes les commandes d’un client.

5. Comment assurez-vous l'unicité du numéro de commande ?

Le champ num_commande dans la table commandes est défini comme UNIQUE dans le MPD.
Ainsi, PostgreSQL empêche toute duplication et garantit que chaque commande possède un numéro distinct.

6. Quelles sont les extensions possibles de votre modèle ?

Voici quelques améliorations possibles pour étendre le modèle :

Gestion de plusieurs adresses par client → ajouter une table adresses_client.

Historique des prix des produits → créer une table prix_historique.

Avis clients → table avis pour stocker notes et commentaires.

Images multiples par produit → table images_produit pour gérer plusieurs photos.

Gestion des rôles administrateurs plus fine → table roles_admin pour gérer plusieurs permissions.

7. Remarques générales

J’ai utilisé PostgreSQL avec des types adaptés : SERIAL pour les IDs, BOOLEAN pour les flags (actif), TIMESTAMP pour les dates.

Tous les mots de passe sont hachés et ne sont jamais stockés en clair.

L’intégrité référentielle est assurée par les clés étrangères et les contraintes ON DELETE/ON UPDATE.

Les tables sont normalisées en 3NF pour éviter les redondances et garantir la cohérence des données.