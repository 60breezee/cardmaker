# CardMaker API

The versioned API is available under /api/v1.

## Authentication

Create a Sanctum token with POST /api/v1/auth/token and send the returned token as an Authorization Bearer token.

Request fields:

- email
- password
- device_name

Revoke the current token with DELETE /api/v1/auth/token.

## Resources

- GET /api/v1/templates lists active non-premium templates.
- GET /api/v1/cards lists the authenticated user's cards.
- POST /api/v1/cards creates a card and applies quota and template access rules.
- GET /api/v1/cards/{card} returns a card owned by the authenticated user.

Card creation expects template_id, name, and an optional data object. Validation errors use Laravel's standard JSON response format.
