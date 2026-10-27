# 0001 — Session Sanctum auth, not Bearer tokens

- **Status**: accepted
- **Date**: 2026-14-09
- **Deciders**: <Dev>

## Context
Evently's frontend is a React SPA served from the same top-level domain
as the API. Token auth (Bearer) was the default Laravel SPA suggestion,
but tokens must be stored somewhere client-accessible, and revocation
is non-trivial.

## Decision
Use Sanctum's session-based SPA auth (cookie + CSRF), not API tokens.

## Consequences
- **Good**: HttpOnly cookies mean no token in localStorage; revocation
  is just session invalidation; CSRF protection comes from Laravel's
  built-in middleware.
- **Bad**: Requires same-site deployment (or careful CORS + SameSite
  config); harder to authenticate a mobile client later.
- **Revisit if**: we build a native mobile app, or need third-party
  API consumers — at that point, add tokens *alongside* sessions, don't
  replace.

## Related
- `AGENTS.md` § Tech Stack
- `config/sanctum.php`, `routes/api.php`