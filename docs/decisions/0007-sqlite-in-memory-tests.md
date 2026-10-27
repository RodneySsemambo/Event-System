# 0007 — In-memory SQLite for tests

- **Status**: accepted
- **Date**: 2025-14-09
- **Deciders**: <Dev>

## Context
The test suite runs on every push via GitHub Actions
(`.github/workflows/ci.yml`). Production uses MySQL. Two options:
spin up a MySQL service container in CI (slow, ~30–60s per job, more
moving parts), or use SQLite in-memory (instant, no external service).

## Decision
`phpunit.xml` is configured with `DB_CONNECTION=sqlite` and
`DB_DATABASE=:memory:`. Tests run against an in-memory SQLite database
that is created and torn down per test process. `RefreshDatabase` is
used for isolation.

## Consequences
- **Good**: Tests start in milliseconds; CI has no DB service to
  provision, wait on, or clean up. Full isolation — no test can leak
  state into another. Contributors can run the full suite locally with
  no setup.
- **Bad**: SQLite and MySQL differ in meaningful ways — JSON column
  operators, full-text search, `ALTER TABLE` semantics, some generated
  columns, case sensitivity of string comparisons, and strict-mode
  behaviors. A test can pass on SQLite and the corresponding query
  can fail on MySQL in production. Migrations that use MySQL-specific
  features will be silently skipped or fail to build the schema.
- **Revisit if**: we adopt a MySQL-specific feature (full-text search,
  spatial indexes, JSON path expressions, generated columns). At that
  point, either (a) add a small set of tests against a MySQL service
  container in CI for those features specifically, or (b) switch the
  whole suite to a MySQL container and accept the CI slowdown. Do not
  let a critical query ship with only a SQLite test covering it.

## Related
- `phpunit.xml`
- `.github/workflows/ci.yml`
- `AGENTS.md` § Testing & CI