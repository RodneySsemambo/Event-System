# 0002 — Controller → Service → Model

- **Status**: accepted
- **Date**: 2025-14-09  
- **Deciders**: <Dev>

## Context
Laravel's default scaffolds and most tutorials put validation, business
rules, DB writes, and side effects (mail, SMS, external API calls)
directly inside controllers. That works for CRUD demos and falls apart
the moment an action has to happen from more than one place — a web
request, a queued job, an artisan command, a test. Evently has several
such actions (`BookingService::create`, `PaymentService::initiateCollection`)
that will eventually be called from webhooks and scheduled jobs, not
just HTTP controllers.

## Decision
Every request follows a strict three-layer flow:
- **Controller** — validates (FormRequest), authorizes (Policy), calls
  exactly one Service method, returns a response. 3–6 lines. No business
  logic, no `if` statements containing rules.
- **Service** — business rules, orchestration, DB writes, external API
  calls, notification dispatch. One method per action. Referenced by
  concrete class in controllers via constructor injection.
- **Model** — persistence, relationships, casts, scopes. No cross-model
  orchestration.

Reference implementations: `BookingService`, `PaymentService`,
`NotificationService`, `ProviderProfileService`, `PortfolioService`,
`ReviewService`.

## Consequences
- **Good**: Business rules live in one place and are callable from
  anywhere. Controllers become trivially reviewable. Services are
  unit-testable without HTTP. Onboarding is faster because the pattern
  is mechanical.
- **Bad**: More files per feature (Request, Policy, Service, Controller,
  Route, Test). Trivial CRUD gets ceremony it doesn't need. Services
  can grow into god-objects if not split. Anemic models are possible if
  teams over-apply the rule to data that belongs on the model.
- **Revisit if**: a Service exceeds ~300 lines or starts needing another
  Service to do its job — that's usually a signal to split by
  sub-domain, not to move logic back into the controller.

## Related
- `.opencode/skills/evently-backend-architecture/SKILL.md`
- `AGENTS.md` § Non-negotiables
- `app/Services/`