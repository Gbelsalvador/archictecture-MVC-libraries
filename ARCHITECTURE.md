# Documentation Technique - Architecture MVC

Ce document décrit la structure interne du projet, les responsabilités des fichiers principaux et les conventions à respecter.

## Vue d'ensemble

Le projet suit une architecture MVC simple :

- `public/` : point d'entrée HTTP (routeur frontal).
- `router/` : wrapper pour le routeur (AltoRouter).
- `controllers/` : logique applicative, rend des vues ou des réponses API.
- `models/` : accès aux données via `Models\DataModel` et modèles métiers.
- `views/` : templates PHP pour l'affichage côté serveur.
- `core/` : utilitaires et fonctions centrales (bootstrapping).
- `utils/` : petites utilitaires (Mailer, Security, UploadHandler).
- `vendor/` : dépendances Composer.
- `automat` : CLI home-grown (génère modèles et contrôleurs à partir de templates).

## Fichiers et responsabilités (détails)

- `public/index.php`
  - Point d'entrée. Monte l'environnement, charge l'autoload, instancie le routeur et délègue les requêtes.
  - Doit rester léger : uniquement bootstrap + dispatch.

- `router/Router.php`
  - Wrapper autour d'AltoRouter. Définit des helpers statiques pour déclarer les routes.
  - Les routes sont déclarées dans `routes/*.php` (GET/POST/PUT/DELETE).

- `controllers/controller.php` (classe `Controller`)
  - Classe de base pour tous les contrôleurs.
  - Propriétés : `protected DataModel $db` (injection/construction automatique).
  - Méthodes principales :
    - `render($view, $data = [])` : inclut un fichier de `views/` (lance une Exception si absent).
    - `redirection($url)` : redirige (header ou script js si headers déjà envoyés) puis `exit`.
    - `error($message)` / `success($message)` : sorties HTML pour erreurs/succès standards.
    - `jsonResponse($data, $status = 200)` / `jsonSuccess()` / `jsonError()` : helpers JSON ajoutés pour les API.
  - Remarque : `render()` attend un chemin type `folder/template`.

- `controllers/ApiController.php`
  - Exemple d'un contrôleur API : retourne généralement des tableaux (non rendu HTML).
  - Pour les routes API, préférez utiliser `jsonResponse` / `jsonError`.

- `models/DataModel.php` (classe `DataModel`)
  - Fournit la connexion PDO centralisée.
  - Méthode `getPDO()` pour récupérer l'objet PDO (ou `null` si non connecté).
  - Lit la configuration DB depuis `config/config.php`.
  - Configuration PDO : erreurexception et fetch assoc par défaut.

- `models/*.php` (modèles métiers)
  - Doivent se situer dans le namespace `Models`.
  - `automat` génère des classes `XModel` avec méthodes CRUD (`findAll`, `findById`, `create`, `update`, `delete`).
  - Les méthodes utilisent `$this->db->getPDO()` pour préparer/exécuter requêtes.

- `views/` 
  - Fichiers PHP rendus par `Controller::render()`.
  - Convention : `views/<entities>/<template>.php`. `automat` génère `views` folder names au pluriel.

- `automat` (CLI)
  - Script PHP CLI à la racine (`automat`) pour générer rapidement des modèles et contrôleurs.
  - Commandes principales : `create:model`, `create:controller`, `list`, `help`.
  - Comportement des templates :
    - Modèles générés : namespace `Models`, class `XModel`, méthodes CRUD et injections `DataModel`.
    - Contrôleurs générés : extends `Controller`, méthodes REST-like (`index`, `show`, `create`, `store`, `edit`, `update`, `destroy`) et endpoints API (`apiIndex`, `apiShow`) qui utilisent `jsonResponse`/`jsonError`.
    - `viewFolder` est généré au pluriel (ex: `tests`) et `route_name` est pluriel (`/tests`) pour cohérence.
  - Limitations / points d'attention :
    - `automat` ne génère pas automatiquement les fichiers de `views` par défaut (option `--with-views` non présente). Si vous appelez `render()` sans créer la vue, une Exception est lancée.
    - Le CLI écrit directement dans `models/` et `controllers/` et écrase les fichiers existants en affichant un avertissement.

- `utils/` 
  - `Mailer.php`, `Security.php`, `UploadHandler.php` : utilitaires réutilisables.
  - `Security` gère sanitation, hash/verify password, CSRF, etc.

## Conventions et recommandations

- Namespace : `Controllers` et `Models` pour les fichiers respectifs.
- Nom des classes : `PascalCase`, suffixes `Controller` et `Model` pour générateur automatique.
- Noms de vues : dossiers en minuscules et au pluriel pour regrouper les templates (ex: `views/articles/index.php`).
- Routes : `/<resource>` pour index, `/<resource>/{id}` pour détail. `automat` utilise la version plurielle pour `route_name`.
- Réponses API : utiliser `jsonResponse()` / `jsonError()` au sein des contrôleurs pour cohérence.

## Bonnes pratiques pour le développement

- Ne pas modifier `vendor/` directement. Utiliser `composer` pour gérer les dépendances.
- Tester localement avec le serveur PHP :

```bash
composer install
php -S localhost:8000 -t public
```

- Pour créer un modèle + contrôleur avec `automat` :

```bash
php automat create:model Article
php automat create:controller ArticleController
```

- Après génération, créez les vues correspondantes dans `views/<resources>/` pour éviter les Exceptions.

## Notes sur sécurité et production

- Toujours configurer les credentials DB dans `config/config.php` ou via `.env` (ne pas committer de secrets).
- Activer TLS pour SMTP en production.
- Valider et limiter la taille/type d'uploads dans `UploadHandler`.

## Ajouts récents (2025-12-19)

- `automat` corrigé pour :
  - générer des templates valides (fix d'accolades),
  - uniformiser les bindings PDO (`['id' => $id]`),
  - générer `viewFolder` et `route_name` au pluriel,
  - utiliser `jsonResponse`/`jsonError` dans les méthodes API.

---

Pour d'autres détails (exemples de code, extension des templates `automat`, tests), dites-moi ce que vous voulez approfondir et j'ajouterai des sections ou exemples supplémentaires.
