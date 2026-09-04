# Bibliothèque numérique de l'ISSTM (canevas, mémoires/projets, horaires)

Mini-application PHP/MySQL (canevas, mémoires/projets, horaire, actualités, carrousel
d'accueil) intégrée au site principal de l'ISSTM : elle utilise le même `header.php` /
`footer.php` / `style.css` que le reste du site, la même session, le même système de
traduction `t()` (fr/en/mg), tout en gardant sa **propre base de données MySQL**
(`bibliotheque`, connexion PDO séparée d'`isstm_db`).

- **Canevas** : UN modèle officiel publié par l'école chaque année (Licence ou Master),
  valable pour **toutes les filières**, téléchargeable librement.
- **Mémoires & Projets** : travaux déjà réalisés par les anciens étudiants, remis en
  version numérique finale à la responsable de bibliothèque, qui les ajoute.
  Classés par **filière + année universitaire + catégorie (Mémoire ou Projet)**.
  **Consultables en ligne uniquement** (visionneuse pdf.js avec filigrane, téléchargement
  et clic droit désactivés — `consulter_memoire.php` / `memoire_flux.php`).
- **Horaire** d'ouverture/fermeture, modifiable par l'admin, avec zone d'actualités
  (fermeture exceptionnelle, etc.), photos jointes possibles.
- **Barre de recherche** (PHP + SQL, requêtes préparées PDO), couvrant canevas et mémoires/projets.
- **Carrousel d'images** en haut de l'accueil, sur le composant `.news-hero-carousel`
  du site principal (mêmes classes que la page d'accueil de l'ISSTM).

## Connexion à la base de données

`config.php` (à la racine de `bibliotheque/`) ouvre une connexion PDO dédiée à la base
`bibliotheque` (constantes `BIB_DB_HOST` / `BIB_DB_NAME` / `BIB_DB_USER` / `BIB_DB_PASS`,
préfixées pour ne jamais entrer en collision avec les constantes `DB_*` du
`db_connect.php` principal de l'ISSTM, inclus juste avant sur chaque page).

## Session unifiée avec le site ISSTM

Chaque page (publique et admin) commence par :

```php
include_once '../language.php';   // démarre/réutilise la session, fournit t()/$lang
require_once '../db_connect.php'; // définit SITE_URL, ouvre $mysqli si besoin
require 'config.php';             // connexion PDO propre à la bibliothèque ($pdo)
require 'functions.php';          // isAdminLogged(), requireAdmin(), e(), iconeFichier()
```

(chemins `../../` depuis `admin/`). `isAdminLogged()` renvoie `true` si **soit** un
compte bibliothèque autonome est connecté (`$_SESSION['admin_id']`), **soit** un admin
général de l'ISSTM est connecté (`$_SESSION['user_logged_in'] === true` et
`$_SESSION['user_role'] === 'admin'`) : un admin ISSTM déjà connecté sur le site
principal accède donc directement à `bibliotheque/admin/` sans seconde connexion,
depuis la carte « Gérer la Bibliothèque » de `administrateur.php`.

## Compte administrateur

Un seul compte, avec accès complet (canevas, mémoires/projets, mentions/filières,
années, horaire, actualités, carrousel) :

| Utilisateur | Mot de passe | Accès |
|---|---|---|
| `bibliotheque` | `bibliotheque123` | Complet |

⚠️ Changez ce mot de passe en production (table `admins`, colonne `password`,
générée avec `password_hash()`).

## Structure du projet

```
bibliotheque/
├── admin/                  Espace administrateur (session bridgée avec l'ISSTM)
│   ├── ajouter_canevas.php / gerer_canevas.php
│   ├── ajouter_memoire.php / gerer_memoires.php
│   ├── gerer_horaire.php / gerer_actualite.php / gerer_filieres.php / gerer_carousel.php
│   ├── login.php / logout.php / dashboard.php
├── uploads/                 Fichiers (protégés par .htaccess)
├── config.php                Connexion PDO ($pdo)
├── functions.php             isAdminLogged() / requireAdmin() / e() / iconeFichier()
├── index.php                 Accueil (carrousel + actualités + derniers documents)
├── canevas.php                Liste des canevas (filtre : niveau + année)
├── memoires.php                Liste des mémoires/projets (filtre : catégorie + filière + année)
├── horaire.php                  Horaire d'ouverture
├── recherche.php                 Résultats de recherche (canevas + mémoires/projets)
├── telecharger.php                Téléchargement des canevas
├── consulter_memoire.php           Visionneuse en ligne des mémoires/projets (pdf.js)
└── memoire_flux.php                 Flux PDF protégé par jeton (utilisé par la visionneuse uniquement)
```

## Modèle de données (résumé, base `bibliotheque`)

- `mentions` (id, nom, abreviation)
- `filieres` (id, nom, abreviation, niveau, mention_id) — sert à classer les mémoires/projets
- `annees_universitaires` (id, libelle)
- `canevas` (id, titre, niveau, annee_id, type_fichier, chemin_fichier, date_ajout) — index sur `niveau`
- `memoires` (id, titre, auteur, encadreur, categorie, filiere_id, annee_id, resume, chemin_fichier, date_ajout) — index sur `categorie`
- `carousel_images` (id, chemin_fichier, legende, ordre)
- `horaires` (id, jour, heure_ouverture, heure_fermeture, actif)
- `actualites_horaire` (id, message, date_debut, date_fin) / `actualite_photos` (photos jointes)
- `admins` (id, username, password, role)

## Note sur la "non-téléchargeabilité" des mémoires

`consulter_memoire.php` affiche le PDF **dans le navigateur**, page par page, via
pdf.js (rendu en `<canvas>`, avec un filigrane "cuit" dans l'image et le clic droit /
raccourcis d'impression désactivés). Le fichier brut n'est jamais lié directement :
`memoire_flux.php` exige un jeton à usage limité (généré et stocké en session par
`consulter_memoire.php`, valable 5 minutes) et un en-tête personnalisé envoyé par le
lecteur JS. Aucune protection web ne peut empêcher à 100 % un utilisateur technique de
capturer un contenu affiché dans son navigateur ; ce mécanisme dissuade l'usage courant
(lien direct, clic droit, impression) sans bloquer la consultation légitime.
