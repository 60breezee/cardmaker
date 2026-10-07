# CardMaker — sécurité

- CSRF et sessions sécurisées fournis par Laravel.
- Validation par Form Requests pour les entrées métier et les uploads.
- Hashage Laravel des mots de passe ; aucun secret ou mot de passe dans les journaux.
- Policies sur les cartes, exports et ressources administratives.
- Identifiants publics non séquentiels pour vérification et partage.
- Uploads renommés côté serveur, contrôlés par MIME, taille et dimensions, hors exécution directe.
- Rate limiting sur authentification, génération, uploads et vérification publique.
- Paiements confirmés par statut serveur/webhook signé, jamais par le retour navigateur.
- `.env` est ignoré par Git et `.env.example` ne contient aucun secret.

