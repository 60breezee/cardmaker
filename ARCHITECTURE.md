# CardMaker — architecture

CardMaker est un monolithe Laravel avec Blade, Tailwind CSS et Alpine.js. Cette approche garde le produit simple à déployer tout en séparant l’interface, l’orchestration HTTP et le métier.

## Couches

- `app/Http/Controllers` : orchestration des requêtes et réponses
- `app/Http/Requests` : validation serveur
- `app/Models` : Eloquent, relations et casts
- `app/Services` : moteur de templates, génération, exports, quotas et paiements
- `app/Jobs` : génération asynchrone et traitements bulk
- `app/Policies` : autorisation par ressource
- `app/Enums` : rôles, statuts, formats et plans
- `resources/views` : présentation Blade
- `resources/js` : interactions et prévisualisation légère

## Principes

- Les cartes sont privées par défaut et utilisent un identifiant public aléatoire pour les URLs publiques.
- Les fichiers passent par le filesystem Laravel et ne font jamais confiance au nom fourni par le navigateur.
- Le rendu final est serveur et reproductible ; la prévisualisation frontend reste indicative et rapide.
- Les règles de quota sont centralisées dans `QuotaService`.
- Les paiements sont isolés derrière `PaymentServiceInterface` et confirmés par webhook côté serveur.
- Les endpoints de ressources appliquent une Policy pour empêcher les accès inter-utilisateurs.

## Infrastructure cible

Développement local : PHP 8.3+, MySQL 8+, filesystem local et queue database.

Production : PHP-FPM, Nginx, MySQL, Redis, stockage S3-compatible et Supervisor pour les workers Laravel.

