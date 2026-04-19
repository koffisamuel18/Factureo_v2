# Factureo

**Factureo** est une application web professionnelle de gestion de facturation, conçue pour les PME, indépendants et prestataires de services. Elle permet de gérer facilement l'ensemble du cycle de facturation, du suivi client à l'édition de devis et factures PDF, dans une interface moderne et intuitive.

## Fonctionnalités principales

- **Gestion des clients** : Ajout, modification, suppression, recherche et affichage de la liste des clients avec leurs coordonnées.
- **Gestion des produits et services** : Création de produits/services avec prix, TVA, et description (pour les services).
- **Création de devis** : Génération rapide de devis multi-lignes, calcul automatique des totaux, gestion des remises, export PDF.
- **Gestion des factures** : Création de factures à partir de devis, suivi du statut (payée/impayée), génération de factures PDF professionnelles avec logo et mentions légales.
- **Gestion des paiements** : Enregistrement des paiements (total ou partiel), mise à jour automatique du statut de la facture.
- **Historique et suivi** : Visualisation de l’historique des actions, suppression sécurisée des factures, numérotation continue pour une meilleure lisibilité.
- **Interface moderne** : Tableaux harmonisés, boutons d’action colorés, messages de succès, confirmation avant suppression, responsive design.
- **Personnalisation** : Logo, coordonnées de l’entreprise, mentions légales et palette de couleurs personnalisables.

## Dépendances et intégrations

### Icônes

- **Font Awesome** ([fontawesome.com](https://fontawesome.com/))
  - Librairie d’icônes très complète, facile à intégrer via CDN.
  - **Intégration** :
    ```html
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <i class="fa-solid fa-trash"></i> <!-- Icône poubelle -->
    ```
- **Bootstrap Icons** ([icons.getbootstrap.com](https://icons.getbootstrap.com/))
  - Icônes modernes, légères, intégration simple.
  - **Intégration** :
    ```html
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <i class="bi bi-check-circle"></i>
    ```
- **SVG personnalisés** :
  - Téléchargez des SVG sur [svgrepo.com](https://www.svgrepo.com/) ou [feathericons.com](https://feathericons.com/), placez-les dans `assets/images/` et utilisez-les ainsi :
    ```html
    <img src="assets/images/delete.svg" alt="Supprimer">
    ```

### Interactions JavaScript

- **Confirmation avant suppression** :
  - Utilisation de `onclick` avec `confirm()` pour éviter les suppressions accidentelles.
    ```html
    <a href="..." onclick="return confirm('Voulez-vous vraiment supprimer ?');">Supprimer</a>
    ```
- **Messages dynamiques, masquage/affichage d’éléments** :
  - Utilisation de JavaScript natif ou de petites librairies (ex : [SweetAlert](https://sweetalert2.github.io/) pour des popups stylées).
  - Exemple de message de succès :
    ```js
    document.getElementById('success-message').style.display = 'block';
    ```
- **Fichiers JS** : Placez vos scripts dans `assets/js/main.js` et liez-les dans vos pages HTML/PHP :
    ```html
    <script src="/factureo/assets/js/main.js"></script>
    ```

### Génération de factures PDF

- **FPDF** ([fpdf.org](https://www.fpdf.org/))
  - Librairie PHP open source pour générer des PDF dynamiquement (factures, devis, reçus, etc.).
  - **Intégration** :
    - Placez le dossier `fpdf` dans `vendor/`.
    - Incluez la librairie dans vos fichiers PHP :
      ```php
      require_once '../vendor/fpdf/fpdf.php';
      $pdf = new FPDF();
      $pdf->AddPage();
      $pdf->SetFont('Arial','B',16);
      $pdf->Cell(0,10,'Facture n°1',0,1,'C');
      $pdf->Output('F', 'facture_1.pdf');
      ```
  - Dans Factureo, la fonction `generate_facture_pdf` s’occupe de la mise en page, du logo, des couleurs, et de l’export automatique du PDF pour chaque facture.

## Types de factures gérées

Factureo permet de gérer les **factures classiques de vente de biens et de prestations de services** :

- Vente de produits (matériels, fournitures, etc.)
- Prestations de services (développement, maintenance, consulting, formation, etc.)
- Factures issues de devis (transformation automatique d’un devis en facture)
- Gestion de la TVA, des remises par ligne, des paiements partiels ou complets
- Génération de factures PDF professionnelles avec logo et mentions légales
- Suivi du statut (payée/impayée)

> **Remarque :** Factureo ne gère pas nativement les factures d’acompte, de situation, d’avoir ou les factures récurrentes. Il est possible d’adapter l’application pour ces besoins spécifiques.

## Déploiement : local ou en ligne ?

- **Par défaut**, Factureo fonctionne en **local** (sur un serveur interne, XAMPP/WAMP, ou un serveur web privé). C’est idéal pour les PME et indépendants qui souhaitent garder la maîtrise de leurs données.
- **Optionnellement**, l’application peut être **hébergée en ligne** (sur un hébergement web ou un VPS) pour un accès distant, multi-utilisateur, et une utilisation en mode SaaS privé.

> **À retenir :** Factureo est flexible : usage local sécurisé ou déploiement en ligne selon vos besoins.

## Objectif

Faciliter la gestion commerciale et la facturation pour les professionnels, tout en offrant une expérience utilisateur fluide et un rendu PDF digne d’un reçu officiel.

---

Pour toute question ou contribution, contactez l’équipe Factureo.

## Installation

1. Placez le dossier du projet dans `htdocs` de XAMPP.
2. Importez le script SQL fourni dans MySQL Workbench.
3. Configurez la connexion à la base de données dans `config/db.php` si besoin.
4. Accédez à `http://localhost/factureo/pages/login.php` pour commencer.

## Structure du projet

- assets/ : CSS, JS, images, factures PDF
- config/ : configuration et connexion BDD
- includes/ : entête, pied de page, navbar, auth
- pages/ : toutes les pages principales (login, dashboard, clients, etc.)
- vendor/ : librairies externes (FPDF) 
- test jenkins pipeline
TEST VS CODE VM OK - CI/CD Factureo v1