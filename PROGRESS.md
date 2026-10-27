# Evently — Progress Tracker

> Updated by the `evently-feature-verification` skill after every feature.
> Status values: `todo` · `in-progress` · `blocked` · `done`
> A feature may only be marked `done` after: tests pass, docs written,
> review checklist signed off. See `.opencode/skills/evently-feature-verification/SKILL.md`.

## Legend
- **Tests** — link to the test file(s) covering this feature
- **Docs** — link to the doc section (README, `docs/*.md`, or inline docblock)
- **Review** — `✅` reviewed against AGENTS.md, `⏳` pending, `❌` blocked

## Phase 1 — Foundation
| # | Feature | Status | Tests | Docs | Review | Notes |
|---|---------|--------|-------|------|--------|-------|
| 1.1 | Auth (Sanctum session SPA) | done | `tests/Feature/AuthTest.php` | README §Auth | ✅ | |
| 1.2 | Roles (client/provider) | done | `tests/Feature/RoleTest.php` | README §Roles | ✅ | |
| 1.3 | Provider profile CRUD | in-progress | `tests/Feature/ProviderProfileTest.php` | ⏳ | ⏳ | Service exists; policy pending |

## Phase 2 — Discovery
| # | Feature | Status | Tests | Docs | Review | Notes |
|---|---------|--------|-------|------|--------|-------|
| 2.1 | Search + filters | todo | — | — | — | |
| 2.2 | Portfolio (image upload) | todo | — | — | — | Must use `Storage::disk('public')` |
| 2.3 | Public provider page | todo | — | — | — | |

## Phase 3 — Transactions
| # | Feature | Status | Tests | Docs | Review | Notes |
|---|---------|--------|-------|------|--------|-------|
| 3.1 | Booking create (client) | todo | — | — | — | |
| 3.2 | Booking accept/reject (provider) | todo | — | — | — | |
| 3.3 | Payment initiate (MarzPay) | todo | — | — | — | |
| 3.4 | Payment webhook (HMAC) | todo | — | — | — | MUST have signature test |
| 3.5 | Auto-cancel on payment failure | todo | — | — | — | `BookingService::cancelForPaymentFailure` |
| 3.6 | Refund (local only — TODO) | todo | — | — | — | Real disbursement deferred |

## Phase 4 — Trust & Comms
| # | Feature | Status | Tests | Docs | Review | Notes |
|---|---------|--------|-------|------|--------|-------|
| 4.1 | Reviews (post-completion) | todo | — | — | — | |
| 4.2 | Notifications (DB + SMS + mail) | todo | — | — | — | Africa's Talking |
| 4.3 | WhatsApp click-to-chat | todo | — | — | — | No `conversations` table |

## Phase 5 — Admin
| # | Feature | Status | Tests | Docs | Review | Notes |
|---|---------|--------|-------|------|--------|-------|
| 5.1 | Filament panel scaffold | todo | — | — | — | Planned |

## phase 6 - Polish Section
| 6.1 | Hero video + 3D depth enhancement | in-progress | — | — | ⏳ | See UI skill |

## Blocked / Known Deferred (do not mark done)
- Cancellation from `accepted` state — documented limitation.
- Real MarzPay refund disbursement — TODO in `PaymentService`.
- In-app chat — deliberately dropped.