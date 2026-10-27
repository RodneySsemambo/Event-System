# Architecture Decision Records (ADRs)

One file per decision that a future maintainer might otherwise reverse
by accident. Not every change gets an ADR — only decisions where
"why?" is not obvious from the code.

## When to write one
- A technology/pattern choice with tradeoffs (Sanctum vs tokens).
- A deliberate non-feature (no in-app chat).
- A security rule that must not be silently undone.
- Anything already in `AGENTS.md` under "non-negotiable" or "known gaps".

## Format
Copy `0000-template.md`, number sequentially, keep it under one screen.
The goal is the *reasoning*, not a spec.

## Index
| # | Title | Status |
|---|-------|--------|
| 0001 | Session Sanctum auth, not Bearer tokens | accepted |
| 0002 | Controller → Service → Model | accepted |
| 0003 | MarzPay webhook HMAC verification | accepted |
| 0004 | Failed payment auto-cancels booking | accepted |
| 0005 | No in-app chat (WhatsApp click-to-chat) | accepted |
| 0006 | Public disk for uploads via Storage facade | accepted |
| 0007 | In-memory SQLite for tests | accepted |