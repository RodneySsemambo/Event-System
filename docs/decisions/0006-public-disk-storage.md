# 0006 — Public disk for uploads via the Storage facade

- **Status**: accepted
- **Date**: 2025-14-09
- **Deciders**: <Devepublic>

## Context
Portfolio images and provider avatars are user-uploaded. A naive
implementation accepts a filesystem path from the client and stores it,
or writes to a raw `public/` path picked by the client. Both open
real vulnerabilities: path traversal (`../../.env`), overwriting
existing files, serving arbitrary local files through the web server,
and stored XSS via SVG or HTML uploads.

## Decision
All uploads go through Laravel's `Storage` facade on the `public` disk.
The client sends file content, never a path. Laravel generates the
filename. The `public` disk is symlinked via `php artisan
storage:link` and serves through `/storage/*`. Business rules about
what's allowed to be uploaded (mime, size) live in the FormRequest for
that endpoint.

## Consequences
- **Good**: Predictable URLs (`/storage/portfolios/abc123.jpg`).
  Laravel handles naming and mime detection. No path from the client
  ever reaches the filesystem layer. Clean separation from `private`
  storage for future needs.
- **Bad**: `public` disk means every uploaded file is web-accessible
  by URL to anyone with the link — fine for portfolio images and
  avatars, **wrong** for anything sensitive (national IDs, contracts,
  verification documents). This ADR is scoped to public assets only.
- **Revisit if**: we add provider verification documents or any
  contract PDFs. Those must go on a **private** disk with signed,
  expiring URLs — do not extend this ADR to cover them. If SVG upload
  is ever allowed, sanitize or rasterize server-side.

## Related
- `app/Services/PortfolioService.php`
- `AGENTS.md` § Security decisions
- `config/filesystems.php`