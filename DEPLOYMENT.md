# CardMaker — déploiement

La cible de production est Ubuntu avec Nginx, PHP-FPM 8.3+, MySQL 8+, Redis et Supervisor.

Étapes prévues : installer les extensions PHP nécessaires, configurer les variables `.env`, exécuter `php artisan migrate --force`, `php artisan storage:link`, construire les assets avec `npm run build`, puis lancer le worker `php artisan queue:work` sous Supervisor.

En développement Windows, l’instance utilisée pendant la validation est MySQL 8.4 sur `127.0.0.1:3306`, base `cardmaker`, avec un datadir dédié. En production, créer un utilisateur MySQL dédié avec un mot de passe fort ; ne jamais utiliser un compte root sans mot de passe.

Pour traiter les générations bulk en production :

```bash
php artisan queue:work database --tries=3 --timeout=900
```

Le statut des générations est conservé dans `bulk_generations` (`queued`, `processing`, `completed`, `failed`) et l’archive n’est téléchargeable que par son propriétaire.

Le scheduler Laravel sera exécuté chaque minute par cron. Les sauvegardes doivent couvrir la base MySQL, les fichiers utilisateurs et la configuration secrète hors dépôt. Les fichiers temporaires et exports expirés seront nettoyés par une commande planifiée.
