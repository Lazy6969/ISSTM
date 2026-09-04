# Cahier des charges — Site officiel de l'ISSTM

**Institut Supérieur des Sciences et Technologies de Mahajanga**
Document généré à partir de l'état réel du code au 04/09/2026 (reverse-engineering exhaustif du dépôt).

---

## 1. Présentation générale

### 1.1 Contexte et objectifs

Le site est la plateforme numérique officielle de l'ISSTM (Université de Mahajanga, Madagascar). Il remplit trois grandes missions :

1. **Vitrine institutionnelle** : présenter l'école, ses filières, son personnel, sa vie étudiante et son actualité au grand public et aux futurs candidats.
2. **Portail d'inscription** : informer sur les frais/dossiers/dates, et permettre une **préinscription en ligne** qui alimente un pipeline de validation administrative jusqu'à la création d'un compte étudiant.
3. **Intranet social et pédagogique** : une fois connectés, étudiants, enseignants et administration disposent d'un espace communautaire (réseau social interne, messagerie, groupes de classe, bibliothèque numérique de mémoires) et d'un back-office complet.

### 1.2 Échelle du projet (indicateurs)

| Indicateur | Valeur |
|---|---|
| Pages/scripts PHP à la racine | ~150 fichiers, ~22 200 lignes |
| Panneaux d'administration dédiés (`admin_*.php`) | 18 |
| Langues supportées | 3 (français, anglais, malagasy) via `translations.php`, ~1 665 clés traduites |
| Bases de données | 2 (`isstm_db` principale, `bibliotheque` séparée) |
| Sous-application intégrée | Bibliothèque numérique (`bibliotheque/`) |

### 1.3 Stack technique

- **Backend** : PHP procédural (pas de framework), MySQL/MariaDB via `mysqli` (site principal) et `PDO` (module bibliothèque).
- **Frontend** : HTML/CSS/JS « maison », **sans Bootstrap ni framework JS** (contrainte explicite du projet) — un seul `style.css` global et plusieurs fichiers `*.js` par domaine fonctionnel (`script.js`, `communaute.js`, `groupe.js`, `messagerie.js`, `admin_dashboard.js`, etc.).
- **Environnement** : XAMPP (Apache + MySQL + PHP 8.2) sous Windows, sans conteneurisation.
- **Dépendances externes** : PHPMailer (vendoré manuellement dans `vendor/phpmailer/`, sans Composer), API de traduction non-officielles (Google Translate « gtx », repli sur MyMemory) pour la traduction automatique de contenus.
- **PWA** : `manifest.webmanifest` + `sw.js` (service worker avec cache statique et page hors-ligne `offline.html`) — le site est installable et fonctionne partiellement hors connexion.
- **SEO** : `sitemap.php` généré dynamiquement (pages statiques + filières/articles/albums publiés en base) et `robots.txt`.

---

## 2. Utilisateurs et rôles

Table `utilisateurs.role` (enum) :

| Rôle | Description |
|---|---|
| `admin` | Accès complet au back-office (`administrateur.php` et tous les `admin_*.php`). |
| `enseignant` | Compte enseignant : accès à l'espace communautaire, groupes de classe (peut être délégué/animateur), messagerie. |
| `etudiant` | Compte étudiant, créé lors de la validation d'une préinscription. Accès complet à l'espace communautaire, groupes de classe, réseau d'amis. |
| `user` | Compte « visiteur inscrit » simple (sans statut étudiant/enseignant), accès restreint. |
| `bibliotheque` | Compte dédié uniquement à l'administration de la bibliothèque numérique (indépendant du reste, voir §4.7). |

Indicateurs booléens complémentaires sur `utilisateurs` : `is_messagerie` (accès à la conversation interne fixe Scolarité/Administration), `is_scolarite`, `is_bibliotheque` (pont d'accès admin bibliothèque sans second compte).

Un **visiteur anonyme** (non connecté) accède uniquement aux pages publiques (vitrine, actualités, préinscription) ; toute page communautaire redirige vers `login.php`.

---

## 3. Spécifications fonctionnelles

### 3.1 Site vitrine public

| Page | Rôle |
|---|---|
| `index.php` | Accueil : diaporama héros (images/vidéos administrables), carte « Dernière actualité » animée, statistiques (étudiants/enseignants/filières/vues, compteurs animés), mot du directeur, mission/vision, carrousel « Découvrir nos pages », carrousel d'actualités, événements à venir, témoignages. |
| `historique.php` | Histoire de l'établissement. |
| `filieres.php` / `filiere_detail.php` | Liste des filières d'ingénieur (GC, GH, GArch, GE, GI, GT, GInfo, GEI, GBM) et fiche détaillée par filière, avec traduction automatique à la volée du contenu (mise en cache en base après premier affichage). |
| `enseignants.php` | Annuaire public des enseignants (table `teachers`), avec traduction automatique à la volée de la spécialité/description. |
| `campus.php` | Présentation du campus / blocs (bâtiments, associations régionales). |
| `associations.php` | Associations étudiantes. |
| `parcours.php` | Débouchés / parcours professionnels. |
| `vie_etudiante.php` | Vie étudiante. |
| `bourse.php` | Informations bourses (avec lien vers la plateforme Trésor Public). |
| `documents.php` | Documents administratifs téléchargeables (table `documents`). |
| `galerie.php` / `galerie_album.php` | Galerie photo par albums (table `gallery_albums`/`gallery_photos`/`gallery_categories`). |
| `actualite.php` / `actualite_article.php` | Liste et fiche détaillée des actualités (table `news_articles`, catégories, photos, pièces jointes). |
| `evenements.php` | Calendrier des événements, avec **navigation par mois ET recherche/saut direct par date précise** (sélecteur de date qui fait défiler et surligne le jour visé). |
| `recherche.php` | Recherche globale sur le contenu du site. |
| `mentions_legales.php` / `confidentialite.php` | Pages légales. |
| `sitemap.php` / `robots.txt` | Référencement / indexation. |
| `ai_help.php` | Assistant d'aide conversationnel (sans IA payante) : met en correspondance la question de l'utilisateur avec la FAQ et des intentions courantes (inscription, bourse, contact…) via un score de mots-clés tolérant aux fautes/accents. |
| Newsletter | `newsletter_subscribe.php` / `newsletter_unsubscribe.php` (lien signé anti-forgeage) / `newsletter_cron.php` (envoi programmé des campagnes, table `newsletter_campaigns`/`newsletter_subscribers`). |

### 3.2 Inscription et préinscription

- **`inscription.php`** : page d'information complète — frais de scolarité (nationaux/étrangers × Licence/Master), dossiers à fournir (préinscription, inscription initiale, réinscription L2/L3, Master, réinscription M2), adresse et modalités de dépôt, coordonnées bancaires, bandeau média (image/vidéo en boucle) administrable menant vers la préinscription.
  - **Configurable depuis l'administration** (panneau « Inscription : Frais & Dates » de `admin_contenu.php`) : année universitaire affichée, date limite de dépôt (reformatée automatiquement en toutes lettres dans la langue active), adresse/contact du bureau de dépôt, numéro de compte bancaire, et l'intégralité des 18 montants de frais (Licence/Master × National/Étranger + tenue) — sans avoir à toucher au code.
- **`preinscription.php`** : formulaire de préinscription en ligne complet (identité, parcours bac, contact, filiation, upload photo), avec récapitulatif de confirmation avant envoi et message de succès animés.
- **`inscription_submit.php`** : traitement/stockage (table `preinscriptions`).
- **Validation administrative** (`admin_etudiants.php`) : l'administration consulte les préinscriptions et, en validant un dossier, **crée automatiquement le compte utilisateur** correspondant (`role = 'etudiant'`), clôturant le pipeline candidat → étudiant.

### 3.3 Authentification et compte

- `login.php` : connexion, avec décor animé (effet d'ondulation/goutte d'eau).
- `login_handler.php` / `logout.php`.
- `mot_de_passe_oublie.php` : réinitialisation par **code OTP envoyé par e-mail** (PHPMailer), avec limitation de tentatives/fréquence et **journal de sécurité** (`security_log`) pour tracer les tentatives.
- `reinitialiser_mdp.php` : saisie du nouveau mot de passe via jeton (`reset_token`/`reset_token_expires`).
- `profil.php` : édition du profil personnel — téléphone, bio, **et informations complémentaires** (date de naissance, ville, centres d'intérêt, liens Facebook/LinkedIn, site web personnel).
- `profil_public.php` : vue publique du profil d'un autre utilisateur (section « En savoir plus » affichant les champs ci-dessus quand ils sont renseignés).
- PWA : installation en application, cache hors-ligne (`offline.html`) pour les pages déjà visitées.

### 3.4 Espace communautaire / réseau social

- **`mes_amis.php`** (hub unique à onglets : Recherche / Reçues / Envoyées / Amis / Groupes) :
  - Recherche d'utilisateurs (`annuaire_partiel.php`) avec **suggestions d'amis automatiques** (priorité aux étudiants de la même filière).
  - Gestion des demandes d'amis (`ami_demande.php` / `ami_repondre.php` / `ami_supprimer.php`, table `amis_demandes`).
  - Groupes personnels créés par les utilisateurs (`mes_groupes_perso.php`, tables `groupes_utilisateurs`/`groupe_utilisateurs_membres`).
- **Messages privés** (`messages_prives.php`, `dm_*.php`) : conversations 1-à-1 entre amis, avec **recherche** (amis + conversations existantes dans une seule liste filtrable), **archive des médias échangés** par conversation (panneau latéral galerie), pièces jointes (`dm_attachments`), masquage/suppression de messages.
- **Fil communautaire** (`communaute.php`) : publications, commentaires (`communaute_comment_*.php`), réactions (`communaute_react.php`), médias joints (`communaute_post_media`), sidebar avec accès direct aux messages privés et au groupe de classe.
- **Notifications** :
  - Cloche dans l'en-tête (`communaute_notifications.php`), icône agrandie.
  - **Page dédiée `notifications.php`** : historique complet groupé par période (Aujourd'hui / Hier / Cette semaine / Plus ancien), actions individuelles (lu/non lu, suppression) et actions groupées par section, avec pagination (`notifications_action.php`, `notification_ouvrir.php`).
- **Annuaire** (`annuaire.php`) : recherche d'utilisateurs (fusionné dans le hub `mes_amis.php`, conservé pour compatibilité des liens existants).

### 3.5 Groupes de classe officiels

Distincts des groupes personnels : chaque promotion/filière dispose d'un groupe de classe officiel (table `groupes_classe`).

- **`mes_groupes.php`** : liste des groupes de classe de l'utilisateur.
- **`groupe_chat.php`** + AJAX (`groupe_send.php`, `groupe_message_partial.php`, `groupe_delete_message.php`, `groupe_media.php`) : messagerie de groupe avec pièces jointes.
- **`groupe_members.php`** / `groupe_ban_member.php` / `groupe_toggle_delegate.php` : gestion des membres et du **rôle de délégué de classe**.
- **`groupe_annonces_list.php`** / `groupe_annonce_create.php` / `groupe_annonce_delete.php` : annonces épinglées du groupe.
- **`groupe_presence.php`** / `groupe_presence_mark.php` / `groupe_presence_print.php` : **feuille de présence / appel**, avec impression (probablement pour les enseignants/délégués).

### 3.6 Messagerie interne (staff)

`messagerie.php` : conversation **unique et fixe** entre tous les comptes marqués `is_messagerie = 1` (Kakal, Scolarité, Administrateur) — pas de système multi-conversations, tout le monde voit tous les messages. Présence en ligne (rafraîchissement d'activité), recherche (`messagerie_search.php`), sondage périodique de nouveaux messages (`messagerie_poll.php`), pièces jointes (`messagerie_media.php`), suppression de message/conversation.

### 3.7 Bibliothèque numérique (`bibliotheque/`)

Sous-application PHP/MySQL **intégrée visuellement** au site (même `header.php`/`footer.php`/`style.css`/session/traduction) mais avec **sa propre base de données** (`bibliotheque`, connexion PDO séparée) :

- **Canevas** : un modèle officiel par année (Licence ou Master), valable pour toutes les filières, téléchargeable librement.
- **Mémoires & Projets** : travaux d'anciens étudiants, classés par filière + année universitaire + catégorie, ajoutés par la responsable de bibliothèque.
  - **Consultation en ligne uniquement** : visionneuse `pdf.js` avec filigrane, téléchargement et clic droit désactivés (`consulter_memoire.php`), flux protégé par jeton à usage limité valable 5 minutes (`memoire_flux.php`) — dissuade la copie sans bloquer la consultation légitime.
- **Horaire** d'ouverture/fermeture administrable, avec zone d'actualités (fermetures exceptionnelles) et photos jointes.
- **Recherche** dédiée (canevas + mémoires/projets).
- **Carrousel d'accueil** dédié au module.
- **Back-office propre** (`bibliotheque/admin/`) : gestion canevas/mémoires/mentions-filières/années/horaire/actualités/carrousel, avec **pont de session** — un admin ISSTM déjà connecté sur le site principal accède directement sans reconnexion.

### 3.8 Back-office administrateur

`administrateur.php` (tableau de bord) avec mini-calendrier (`admin_calendar_events.php`) et bloc-notes personnel auto-sauvegardé (`admin_notes_save.php`), puis 18 panneaux dédiés :

| Panneau | Domaine géré |
|---|---|
| `admin_contenu.php` | Contenu de la page d'accueil (héros, directeur, mission/vision, statistiques, témoignages, images globales, footer, contact), bannière « Inscription en ligne », **et depuis peu : Frais & Dates d'inscription** (voir §3.2). |
| `admin_utilisateurs.php` | Gestion des comptes utilisateurs (rôles, accès). |
| `admin_etudiants.php` | Préinscriptions reçues → validation → création de compte étudiant. |
| `admin_enseignants.php` | Fiches enseignants. |
| `admin_filieres.php` | Filières et leurs contenus détaillés. |
| `admin_actualites.php` | Articles d'actualité, catégories, photos. |
| `admin_evenements.php` | Événements du calendrier public. |
| `admin_galerie.php` | Albums et photos de la galerie. |
| `admin_campus.php` | Blocs/bâtiments du campus. |
| `admin_organigramme.php` | Organigramme du personnel (`org_people`). |
| `admin_partenaires.php` | Partenaires de l'école. |
| `admin_documents.php` | Documents administratifs téléchargeables. |
| `admin_banners.php` | Bannières de pages (`site_banners`). |
| `admin_newsletter.php` | Campagnes et abonnés newsletter. |
| `admin_communaute.php` | Modération du fil communautaire (posts/commentaires). |
| `admin_dashboard_stats.php` | Statistiques (vues, compteurs — `page_views`/`site_stats`). |

---

## 4. Modèle de données (résumé)

### 4.1 Base principale `isstm_db`

Comptes & réseau social : `utilisateurs`, `amis_demandes`, `groupes_utilisateurs`, `groupe_utilisateurs_membres`, `groupe_utilisateurs_messages`, `groupe_utilisateurs_attachments`, `groupe_utilisateurs_hides`, `dm_conversations`, `dm_messages`, `dm_attachments`, `dm_message_hides`.

Groupes de classe : `groupes_classe`, `groupe_membres`, `groupe_messages`, `groupe_message_attachments`, `groupe_message_hides`, `groupe_annonces`, `groupe_presence_sessions`, `groupe_presence_marks`.

Messagerie interne : `messagerie_messages`, `messagerie_attachments`, `messagerie_message_hides`.

Communauté (fil d'actualité) : `communaute_posts`, `communaute_comments`, `communaute_reactions`, `communaute_post_media`, `communaute_notifications`.

Contenu vitrine : `site_content`, `site_banners`, `hero_slides`, `testimonials`, `filieres`, `filiere_blocks`, `teachers`, `campus_blocs`, `org_people`, `partenaires`, `associations` (le cas échéant), `documents`, `preinscription_cta_media`.

Actualités & galerie : `news_articles`, `news_categories`, `news_photos`, `news_attachments`, `gallery_albums`, `gallery_categories`, `gallery_photos`.

Événements & candidatures : `evenements`, `preinscriptions`.

Divers : `newsletter_campaigns`, `newsletter_subscribers`, `admin_notes`, `security_log`, `page_views`, `site_stats`, `pays_nationalites` (référentiel).

### 4.2 Base séparée `bibliotheque`

`mentions`, `filieres`, `annees_universitaires`, `canevas`, `memoires`, `carousel_images`, `horaires`, `actualites_horaire`, `actualite_photos`, `admins`.

---

## 5. Exigences non-fonctionnelles

- **Multilingue** : toute chaîne visible passe par `t($cle)` (dictionnaire `translations.php`, fr/en/mg) ou par du contenu administrable multi-langue (`site_content`, colonnes `content_value_fr/en/mg`) via la fonction `dc()`/`dc_footer()`. Traduction automatique à la volée (Google non-officiel, repli MyMemory) pour certains contenus dynamiques (filières, enseignants), mise en cache en base après premier calcul.
- **Cohérence visuelle** : un seul design system (`style.css`), **aucune dépendance à Bootstrap ou un framework CSS/JS externe** — animations, glassmorphism (`backdrop-filter`), thèmes clair/sombre (`color-mix()`), pensés « maison ».
- **Accessibilité motion** : les animations décoratives respectent `prefers-reduced-motion: reduce`.
- **Responsive** : toutes les pages s'adaptent mobile/tablette/desktop (media queries dédiées par composant).
- **Sécurité** : mots de passe hachés (`password_hash`), jetons à expiration pour réinitialisation de mot de passe et consultation de mémoires, journal de sécurité (`security_log`), vérifications de rôle systématiques sur les endpoints admin/AJAX, protection anti-copie des mémoires (filigrane + flux à jeton).
- **PWA / disponibilité** : installable, cache statique + page hors-ligne via service worker.
- **SEO** : sitemap XML dynamique, `robots.txt`, structure sémantique des pages publiques.

---

## 6. Points d'attention / dette technique constatée

Ces éléments existent dans le code actuel et méritent une décision explicite avant toute mise en production ou publication du dépôt :

1. **Scripts de diagnostic à la racine** : `test_json.php`, `generate_hash.php`, `check_password.php` contiennent des identifiants/mots de passe codés en dur (ex. `mirindra123`, un e-mail admin) et ne sont pas des fonctionnalités du site — à supprimer ou déplacer hors du webroot avant mise en ligne publique.
2. **Secret en clair** : `NEWSLETTER_SECRET` est codé en dur dans `db_connect.php` — à sortir en variable d'environnement pour une mise en production publique.
3. **Compte bibliothèque par défaut** : identifiants `bibliotheque` / `bibliotheque123` documentés dans `bibliotheque/README.md`, à changer en production.
4. **`mail_config.php`** : mot de passe d'application Gmail en placeholder (`REMPLACER_PAR_UN_MOT_DE_PASSE_APPLICATION_GOOGLE`) — à configurer un vrai mot de passe d'application avant que les envois d'e-mail (OTP, newsletter) fonctionnent.
5. **Deux bases de données distinctes** (`isstm_db` et `bibliotheque`) : à prévoir dans toute procédure de sauvegarde/migration — un export de `isstm_db` seul ne suffit pas.
6. **Pas de gestionnaire de dépendances** pour PHPMailer (vendoré à la main, pas de `composer.json`) — toute mise à jour de la librairie est manuelle.

---

## 7. Historique des évolutions récentes (traçabilité)

Pour référence, les évolutions suivantes ont été livrées récemment et sont déjà incluses dans les sections ci-dessus :

- Calendrier des événements : saut direct par date en plus de la navigation par mois.
- Effet de décoration (ondulation) de la page de connexion rendu visible.
- Redesign du menu déroulant de l'en-tête (couleurs adaptées au thème + flou d'arrière-plan).
- Correction de la luminosité des bannières de page (overlay assombrissant réduit).
- Panneau d'administration « Inscription : Frais & Dates » (frais, date limite, adresse, compte bancaire configurables sans toucher au code).
- Redesign de la carte « Dernière actualité » du héros d'accueil (carte animée avec vignette, badge « live » pulsé, reflet lumineux, effets de survol).
