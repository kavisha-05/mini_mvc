-- Vérifier les commandes avec produits et clients
SELECT c.nom, c.prenom, co.numero_commande, p.nom AS produit, lc.quantite, lc.prix_unitaire
FROM clients c
JOIN commandes co ON c.id_client = co.id_client
JOIN lignes_commande lc ON co.id_commande = lc.id_commande
JOIN produits p ON lc.id_produit = p.id_produit
ORDER BY co.id_commande;