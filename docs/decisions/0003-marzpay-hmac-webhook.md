# 0003 — MarzPay webhook HMAC verification

- **Status**: accepted
- **Date**: 2025-14-09
- **Deciders**: <Dev>

## Context
MarzPay calls `PaymentWebhookController` directly with no logged-in
user. The payload is authoritative: it tells us whether a mobile-money
collection succeeded, which flips a `Payment` row and (via 0004) can
cancel a booking. Without verification, anyone who can POST to that URL
can mark bookings paid, trigger notifications, or cancel records. That
is a money-touching endpoint with no session to rely on.

## Decision
`PaymentWebhookController::verifySignature` computes an HMAC over the
raw request body using a shared secret and compares it (constant-time)
against the `X-MarzPay-Signature` header. If it does not match, the
request is rejected before the payload is parsed or any Service is
called. The raw body — not `$request->all()` — is what's signed.

## Consequences
- **Good**: Tamper-proof. A test asserting an invalid signature is
  rejected is the single most important test on the payments path.
  Replay of a captured payload by a third party is not useful without
  the secret.
- **Bad**: Local testing requires generating a valid signature (a test
  helper or a fixed test secret). Signature failures are opaque — the
  only signal is a 401, so debugging MarzPay integration means logging
  the *expected* vs *received* signature carefully, never the secret
  itself.
- **Revisit if**: MarzPay changes its signing scheme (unlikely in a
  capstone, common in production). Update the verification code — do
  **not** remove the check "temporarily". If MarzPay adds timestamp-
  based replay protection, adopt it; don't invent your own.

## Related
- `app/Http/Controllers/PaymentWebhookController.php`
- `AGENTS.md` § Security decisions
- `tests/Feature/PaymentWebhookTest.php` (must include invalid-signature case)