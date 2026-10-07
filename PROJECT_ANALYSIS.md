# Analyse du projet CardMaker

## État actuel

CardMaker est une application Laravel 13 utilisant PHP 8.3, Blade, Tailwind CSS et Alpine.js. Le dépôt contient déjà une fondation fonctionnelle : authentification, dashboard, modèles de cartes, CRUD des cartes, upload d’images, génération PNG/JPG/PDF, QR Code de vérification, quotas, facturation abstraite, administration, API versionnée et génération CSV asynchrone.

La base locale est MySQL 8.4 (`cardmaker`) et les tests utilisent SQLite en mémoire lorsque cela est configuré par l’environnement de test. Les assets frontend sont compilés avec Vite. Le serveur de développement est disponible sur `http://127.0.0.1:8000`.

## Vérifications effectuées

- PHP 8.3.33 et Composer 2.10.3 disponibles via les outils locaux.
- Laravel 13 détecté et démarrable.
- Node/npm installés et build Vite déjà généré.
- MySQL 8.4 démarré localement, migrations exécutées et base `cardmaker` disponible.
- Routes web, API, administration, exports, vérification et bulk présentes.
- Tests automatisés existants couvrant création, génération, PDF, upload, permissions et administration.

## Problèmes et écarts identifiés

1. Le document d’analyse initial n’existait pas.
2. L’export PDF est encore construit dans `CardController`; il doit être isolé dans `ExportService`.
3. Le rendu raster utilise actuellement la police GD intégrée. Un `FontManager` et des polices distribuées avec le projet restent à finaliser.
4. L’éditeur fournit une prévisualisation légère, mais le drag-and-drop avancé n’est pas encore implémenté.
5. Le paiement Stripe nécessite des clés et un webhook configurés pour une validation d’intégration réelle.
6. Les tests navigateur et la vérification visuelle responsive restent à compléter.
7. La documentation et certains textes doivent être normalisés en UTF-8 propre.

## Architecture proposée

- **HTTP** : routes, contrôleurs minces, Form Requests et middleware.
- **Domaine applicatif** : `CardTemplateEngine`, `CardGeneratorService`, `ExportService`, `QuotaService`, services de paiement et actions dédiées.
- **Données** : modèles Eloquent, migrations, factories et seeders; UUID/public identifiers pour les URLs exposées.
- **Asynchrone** : Jobs Laravel pour la génération bulk, queue database en local et Redis/Supervisor en production.
- **Présentation** : Blade + Tailwind + Alpine, composants réutilisables et prévisualisation client indicative.
- **Stockage** : filesystem Laravel local en développement, S3-compatible en production.
- **Sécurité** : Policies, validation serveur, CSRF, sessions sécurisées, rate limiting, stockage privé et contrôle MIME.

## Dépendances principales

- Laravel Framework 13
- Laravel Sanctum pour l’API
- Barryvdh Dompdf pour les exports PDF
- chillerlan/php-qrcode pour les QR Codes
- GD/ImageMagick selon l’environnement pour le rendu image
- Stripe PHP derrière `PaymentServiceInterface`
- Alpine.js, Tailwind CSS et Vite

## Risques techniques

- La disponibilité de GD/Imagick et des polices doit être vérifiée sur chaque serveur.
- Les fichiers générés peuvent croître rapidement : quotas, expiration et nettoyage sont nécessaires.
- Les générations bulk doivent rester en queue pour éviter les timeouts HTTP.
- Les webhooks de paiement doivent être signés et idempotents.
- Les données publiques de vérification doivent rester explicitement limitées par l’utilisateur.

## Plan de développement

1. Fondation Laravel, configuration et documentation.
2. Schéma de données, modèles, factories et seeders.
3. Authentification, rôles, profils et autorisation.
4. Dashboard et galerie de templates.
5. Création, édition, upload et prévisualisation.
6. Moteur de templates et génération serveur PNG/JPG.
7. Service d’export PDF et téléchargement.
8. QR Code, vérification et partage public optionnel.
9. Quotas, plans, abonnements et paiements confirmés par webhook.
10. Génération CSV, jobs, progression et ZIP.
11. Administration, notifications, nettoyage et observabilité.
12. Sécurité approfondie, tests unitaires/feature/E2E, performance et déploiement.

Chaque étape doit être inspectée, implémentée, testée, corrigée et documentée avant de poursuivre.
