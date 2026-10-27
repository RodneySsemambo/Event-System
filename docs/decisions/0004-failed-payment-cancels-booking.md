# 0004 — Failed payment auto-cancels the booking

- **Status**: accepted
- **Date**: 2025-14-09
- **Deciders**: <Dev>

## Context
A booking moves from `pending` → `accepted` when the provider agrees.
At that point a payment is initiated. If the collection fails (no
balance, wrong PIN, network drop reported as failure by MarzPay), the
booking is in limbo: the provider has reserved time on their calendar,
the client has not paid, and nothing in the system resolves it.

Alternatives considered:
1. Leave the booking `accepted` and let the client retry manually.
2. Move to a separate `payment_failed` state, neither accepted nor
   cancelled, requiring manual resolution.
3. Auto-cancel.

## Decision
Auto-cancel on payment failure via
`BookingService::cancelForPaymentFailure`. The cancellation is
attributed to the system, not the client, and the provider is notified
so they can release the slot.

## Consequences
- **Good**: Provider's calendar is freed automatically. Booking has a
  clear terminal state (`cancelled`) rather than an ambiguous middle
  one. No manual cleanup queue. The rule is testable in isolation.
- **Bad**: A transient failure (MarzPay timeout that later resolves as
  success, or a network blip the client could have retried) cancels a
  booking the client genuinely intended to pay for. There is no
  "retry payment" path — the client must create a new booking, losing
  their original slot if it's been taken. This is a deliberate product
  tradeoff for a capstone, not an oversight.
- **Revisit if**: we add a retry-payment flow (add a `payment_failed`
  state with a grace window instead of cancelling), or MarzPay starts
  distinguishing transient from terminal failures in its webhook
  payload — at that point only terminal failures should cancel.

## Related
- `app/Services/BookingService.php::cancelForPaymentFailure`
- `AGENTS.md` § Known gaps
- `PROGRESS.md` Phase 3.5