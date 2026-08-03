# Release 1.1.0

Date de publication : 3 août 2026

Cette version met l’accent sur la correction de bugs, la stabilité du noyau MVC et la fiabilité du routage.

## Nouveautés et corrections

- Correction du rendu des vues dans le contrôleur de base pour utiliser le bon dossier `views/` à la racine du projet.
- Renforcement du routeur pour permettre l’initialisation automatique quand l’instance AltoRouter n’a pas encore été créée.
- Amélioration du dispatch des routes vers les contrôleurs pour rendre les cibles de type `[Classe::class, 'methode']` plus robustes.
- Uniformisation des réponses `404` avec un retour JSON cohérent et un `Content-Type` explicite.
- Nettoyage du cœur applicatif `Core.php` et correction de la signature de récupération des entrées JSON.
- Correction du test de routage pour pointer vers le bon contrôleur de validation.

## Vérifications réalisées

- Suite de tests exécutée avec succès.
- Vérification de la syntaxe PHP sur les fichiers modifiés.

## Impact

- Aucun changement cassant n’a été introduit.
- Les endpoints existants continuent de fonctionner avec le même contrat.
- Cette mise à jour améliore surtout la fiabilité et la maintenabilité de la base MVC.

## Notes techniques

- Le projet reste basé sur AltoRouter pour la couche de routage.
- Le cœur applicatif conserve son approche légère et orientée contrôleurs.
- Les réponses JSON et les helpers d’authentification restent centralisés dans `src/core/`.

## Recommandation de publication

Cette version peut être annoncée comme une mise à jour de stabilisation :

> Version 1.1.0: correction de bugs, amélioration du routage, consolidation du cœur MVC et fiabilisation des réponses API.
