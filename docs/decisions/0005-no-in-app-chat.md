# 0005 — No in-app chat (WhatsApp click-to-chat)

- **Status**: accepted
- **Date**: 2025-14-09
- **Deciders**: <Dev>

## Context
A two-sided marketplace benefits from on-platform messaging — it keeps
records, enables dispute resolution, and prevents disintermediation.
But building a real chat (presence, unread counts, push, abuse
handling) is a multi-month subproject. Evently is a capstone on a
deadline.

## Decision
Ship a WhatsApp click-to-chat button. Do **not** create
`conversations`/`messages` tables.

## Consequences
- **Good**: Zero backend surface area, familiar to Ugandan users
  (WhatsApp is the default communication channel), ships now.
- **Bad**: No on-platform record of client–provider communication, so
  disputes have no audit trail; users can trivially take the deal
  off-platform; we can't measure match quality.
- **Revisit if**: dispute rate rises, or we need to enforce policy on
  communication. Migration path: introduce `conversations` +
  `messages` with a `channel` enum (`whatsapp` | `in_app`), backfill
  nothing, let new bookings default to in-app.

## Related
- `AGENTS.md` § Known gaps
- `PROGRESS.md` Phase 4.3