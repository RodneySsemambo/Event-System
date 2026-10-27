
# Evently — Project Context for AI Coding Agents

This file is the **index**. Depth lives in the files it points to.
Read this first; follow the link only for the task at hand.

## The Problem (30 seconds)
Event planning in Uganda is fragmented and informal. Evently is a
two-sided marketplace — providers list verified profiles with pricing
and availability; clients search, message, book, and pay. This is a
real BSc IT capstone (Metropolitan International University) built
toward a startup. Treat it with production seriousness: security, data
integrity, and architecture decisions are not shortcuts.

## Tech Stack
- **Backend**: Laravel 12, PHP 8.2+, MySQL, Sanctum (session-SPA — see ADR-0001)
- **Frontend**: React (Vite), React Router, Axios, Framer Motion
- **Payments**: MarzPay (MTN/Airtel mobile money)
- **Notifications**: Africa's Talking (SMS), Laravel Mail, DB notifications
- **Admin**: Filament (planned — ADR pending)
- **Deploy**: Railway + Docker
- **Tests**: PHPUnit, in-memory SQLite

## Non-negotiables (read every time)
1. **Controller → Service → Model.** Controllers validate, authorize,
   call one Service method, return. No business logic in controllers.
2. **Policies answer "who". Services answer "what state".**
   `auth:sanctum` proves login; a Policy proves ownership. Never rely
   on role middleware alone for record-level access.
3. **MarzPay webhook HMAC must be verified** before trusting a payload.
   Never bypass, even to make a test pass.
4. **File uploads go through `Storage` on the `public` disk.** Never
   trust a client-submitted path.
5. **No rewriting git history.** Conventional Commits, one logical
   change per commit. Some repos are graded on history integrity.
6. **No feature is "done"** until it passes the feature-verification
   skill below.
   
   Before creating ANY file, verify it does not already exist at a
different path. "Not found at path X" is never permission to create
it. If a file the skills or PROGRESS.md reference is missing, STOP
and ask the user. Scaffolding a duplicate of an existing file is a
failure, not a fix.

## Skills — load the one that matches the task

| Task | Skill |
|---|---|
| Any backend endpoint, service, policy | `.opencode/skills/evently-backend-architecture/SKILL.md` |
| Any React/CSS/page/component work | `.opencode/skills/evently-ui-ux/SKILL.md` |
| After implementing any feature | `.opencode/skills/evently-feature-verification/SKILL.md` |
| When rules, skills, or ADRs conflict | `.opencode/skills/evently-rule-conflicts/SKILL.md` |

## State — read these before starting work
- `PROGRESS.md` — the live feature tracker. Pick up from here.
- `docs/decisions/` — ADRs. If a decision is documented, do not
  silently reverse it.

## Known gaps / deliberately deferred
Do **not** "fix" without asking. Full reasoning in the ADR for each.
- Cancellation from `accepted` (only from `pending`) — documented limit.
- Real MarzPay refund disbursement — TODO in `PaymentService`.
- Filament admin panel — planned, not built.
- In-app chat — dropped for WhatsApp click-to-chat (ADR-0005).
  `conversations`/`messages` tables do not exist. Do not add them.

## Testing gate
Every new feature needs: happy-path test, authorization-failure test,
and — for webhooks — an invalid-signature test. No assertion-free
tests. Full process in the feature-verification skill.

## Commit conventions
Conventional Commits: `feat:`, `fix:`, `test:`, `docs:`, `chore:`,
`ci:`. One logical change per commit.


nylon api key = npk_wWCtMyFSn8Z-kH9mI0WYAuPnU3G1uE6-
nylon secret = nps_LM7qHkcLZQoZt5un-HQqNz9mbcPzVqR-qyo-0qddEfBCB5E2