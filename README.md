# CardMaker

CardMaker est un SaaS Laravel permettant de créer, personnaliser et exporter des cartes générées à partir de templates. Les cartes sont des créations personnalisées du SaaS et ne reproduisent pas de documents officiels.

## Prérequis

- PHP 8.3+ avec OpenSSL, PDO MySQL, Mbstring, Fileinfo, GD/Imagick et ZIP
- Composer 2+, Node.js et npm, MySQL 8+
- Redis en production pour les queues

## Installation locale

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

Configurer `DB_*`, `FILESYSTEM_DISK`, `QUEUE_CONNECTION`, `CACHE_STORE` et le mailer dans `.env` avant les migrations.

## Lancement

```powershell
php artisan serve
```

En production, utiliser PHP-FPM/Nginx, un worker `php artisan queue:work` supervisé, Redis et le scheduler Laravel.

Fonctionnalités actuellement implémentées : inscription/connexion, dashboard, templates seedés, création/modification/duplication/suppression de cartes, upload photo, exports PNG/JPG/PDF, vérification publique optionnelle, QR Code PNG via renderer GD, quotas configurables, administration de statistiques, API `/api/v1` protégée par Sanctum, et import CSV en queue avec ZIP.

Le serveur MySQL doit être initialisé et démarré pour un environnement local conforme à la cible. Les tests automatisés utilisent SQLite en mémoire ; ils vérifient notamment le rendu PNG, l’export PDF et l’isolation entre utilisateurs.

Les notifications sont stockées dans `notifications`. La commande planifiée `php artisan cardmaker:cleanup` supprime les exports de plus de 30 jours et les générations bulk de plus de 7 jours ; le cron de production doit appeler `php artisan schedule:run` chaque minute.

## Tests et qualité

```powershell
php artisan test
vendor/bin/pint --test
```

## Documentation

- [PROJECT_ANALYSIS.md](PROJECT_ANALYSIS.md) — état initial et plan
- [ARCHITECTURE.md](ARCHITECTURE.md) — organisation technique
- [SECURITY.md](SECURITY.md) — règles de sécurité
- [DEPLOYMENT.md](DEPLOYMENT.md) — cible de déploiement
- [API.md](API.md) — API versionnée prévue

Ne jamais commiter `.env`, des clés privées ou des mots de passe réels.
