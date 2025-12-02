1. Pourquoi stocker le prix unitaire dans la table lignes_commande plutôt que d’utiliser directement le prix du produit ?
Le prix d’un produit peut changer dans le temps (promotion, augmentation, nouvelle version).
Si on ne stocke que le prix dans la table produits, les anciennes commandes seraient faussées si le prix change.
Solution choisie :
    • Stocker le prix unitaire au moment de la commande dans lignes_commande.
    • Cela permet de conserver un historique exact des achats et des montants payés.
2️. Stratégie pour gérer les suppressions
Relations principales :
Relation
Stratégie choisie
Justification
Client → Commande
ON DELETE CASCADE
Si un client est supprimé, ses commandes sont automatiquement supprimées pour garder la cohérence.
Catégorie → Produit
ON DELETE RESTRICT
Une catégorie ne peut pas être supprimée si elle contient des produits.
Commande → LigneCommande
ON DELETE CASCADE
Si une commande est supprimée, toutes ses lignes de commande sont supprimées automatiquement.
Produit → LigneCommande
ON DELETE RESTRICT
Impossible de supprimer un produit qui est déjà dans des lignes de commande pour préserver l’historique des ventes.
Optionnel : dans un futur développement, on pourrait utiliser un soft delete (deleted_at) pour les produits ou clients afin de conserver les données historiques tout en les masquant.
3️. Gestion des stocks
    • Le stock d’un produit est décrémenté après le paiement validé.
    • Si un client ajoute un produit au panier mais que le paiement n’est pas encore effectué, le stock n’est pas bloqué.
    • Si un produit est en rupture de stock, le client ne peut pas finaliser la commande.
    • On peut envisager un alerte automatique ou un flag actif = FALSE pour les produits en rupture.
4️. Index
Pour améliorer la performance des recherches et des jointures, nous avons créé des index implicites ou uniques sur :
    • clients.email → unicité et recherche rapide pour l’authentification.
    • administrateurs.nom_utilisateur et email → unicité et login rapide.
    • commandes.numero_commande → recherche rapide et unicité.
Ces index permettent d’accélérer les requêtes JOIN, les WHERE et la vérification d’unicité.
5️. Unicité du numéro de commande
    • La colonne numero_commande de la table commandes est UNIQUE.
    • Chaque commande possède un numéro différent, généré soit manuellement, soit automatiquement par le code (ex: CMD-0001, CMD-0002).
    • Cela garantit qu’aucune commande ne peut être dupliquée ou confondue.
6️. Extensions possibles du modèle
    • Plusieurs adresses par client : table adresses liée à clients.
    • Historique des prix : table historique_prix pour suivre les variations de prix d’un produit.
    • Avis clients : table avis avec id_client, id_produit, note et commentaire.
    • Images multiples par produit : table images_produits avec FK sur produits.
    • Statistiques et rapports : ventes par produit, par catégorie, par client.


