# Elearning CMS Agent Rules

This file is the canonical rule source for every AI coding agent working in this
Laravel repository. The words MUST, MUST NOT, REQUIRED, SHOULD, and SHOULD NOT
are normative.

## 1. Rule loading and precedence

1. Before searching, running commands, planning, reviewing, or editing, the agent
   MUST read this file completely.
2. The agent MUST also read every applicable `AGENTS.md` or tool-specific rule
   file between the repository root and the target file.
3. More specific scoped rules override general architecture or style rules.
4. Safety, data-preservation, secret-handling, and the prohibition on committing
   or pushing MUST NOT be weakened by a more specific rule.
5. If a user request conflicts with these non-overridable rules, the agent MUST
   stop and explain the conflict instead of silently ignoring a rule.

## 2. Absolute Git and workspace safety rules

- The agent MUST NOT run `git commit`, `git push`, create a pull request, merge,
  rebase, tag, or publish code. The human owner performs those operations.
- The agent MUST NOT run destructive Git commands such as `git reset --hard`,
  `git clean`, or checkout/restore commands that discard local changes.
- The agent MUST inspect `git status --short` before editing.
- Existing modified and untracked files belong to the user. The agent MUST
  preserve them and MUST NOT reformat, revert, delete, or include them in scope
  without explicit task requirements.
- The agent MUST keep changes narrowly scoped to the requested behavior.
- The agent MUST NOT edit generated, cached, dependency, or runtime files such as
  `vendor/`, `node_modules/`, `.cache/`, `storage/framework/`, compiled assets, or
  logs unless the task explicitly targets them.
- Destructive database or filesystem operations require explicit authorization
  and an exact, verified target. Broad deletion commands are forbidden.
- Secrets, real tokens, passwords, private keys, and production credentials MUST
  NOT be printed, committed, copied into documentation, or added to fixtures.

## 3. Mandatory workflow for every task

### Before making changes

1. Read all applicable rules completely.
2. Inspect `git status --short` and identify pre-existing changes.
3. Locate the real request path: route, middleware, controller, validation,
   service, model/query, resource/response, job/event, and relevant tests.
4. Read neighboring code and follow an established pattern when that pattern is
   safe and maintainable.
5. State assumptions when missing information could materially change behavior.
6. For a bug, determine and explain the root cause before implementing a fix.
7. For a non-trivial feature, define the contract and a focused implementation
   plan before editing.

### Reusable playbooks

- When a task creates, changes, deprecates, or diagnoses an API contract, the
  agent MUST read and follow
  `.agent/skills/laravel-api-contract-change/SKILL.md` before editing. This
  includes routes, middleware/auth, validation, API controllers, JSON responses,
  pagination, ownership checks, request/response mismatches, and API 4xx/5xx
  incidents.
- Project-local skills are repeatable playbooks. They supplement this canonical
  rule file; they MUST NOT weaken its safety, Git, security, or verification
  requirements.

### While making changes

- Make the smallest coherent change that solves the task.
- Preserve public API and database contracts unless the task explicitly changes
  them.
- Do not perform opportunistic refactors unrelated to the requested behavior.
- Improve legacy code only in the touched path and only when needed for safety,
  clarity, or testability.
- Add or update focused tests for behavior changes and regressions.
- Never hide an error with an empty catch, silent fallback, fabricated success
  response, or disabled validation.

### Before reporting completion

1. Review the complete diff for accidental scope expansion and secrets.
2. Run the most focused relevant tests first, then broader checks proportional to
   risk.
3. Run formatting/static checks applicable to changed files.
4. Run `git diff --check`.
5. Report the exact checks executed, their results, and any check that could not
   run. Never claim a check passed when it was not executed.
6. Report changed files and remaining operational steps separately.

## 4. Laravel architecture boundaries

The project uses Laravel 11 and PHP 8.2. New and changed code MUST respect these
boundaries without forcing unnecessary patterns onto unaffected legacy code.

### Routes and middleware

- Route files define transport mapping only; they MUST NOT contain business
  logic or database queries.
- Authentication, authorization, throttling, and cross-cutting request concerns
  MUST use middleware, policies, gates, or framework facilities.
- Route names and API paths MUST be stable and explicit. A breaking route change
  requires an explicit migration plan for consumers.

### Controllers

- Controllers MUST remain thin HTTP adapters.
- A controller may validate/receive input, invoke one application/service
  operation, and map the result to an HTTP response.
- Controllers MUST NOT contain reusable business rules, long query chains,
  transaction orchestration, file-storage workflows, or loops that perform
  repeated writes.
- API and web concerns MUST remain separated under their existing namespaces.

### Validation and request contracts

- New or materially changed endpoints SHOULD use dedicated Form Request classes.
- Validation rules, authorization, and user-facing validation messages MUST NOT
  be duplicated across controllers.
- Never trust IDs, prices, ownership, completion state, roles, or calculated
  totals supplied by the client. Recompute or verify them server-side.
- Normalize optional values deliberately; do not confuse missing, `null`, empty
  string, zero, and `false`.

### Services and application logic

- Multi-step business use cases belong in focused service/action classes.
- A service MUST have one clear business responsibility and a small public API.
- Dependencies SHOULD be injected through constructors when practical.
- Service methods MUST NOT depend on HTTP request globals or construct HTTP
  responses.
- Do not introduce a Repository abstraction when Eloquent scopes and a focused
  service already provide a clear testable boundary.
- Avoid static helpers for stateful business behavior. Pure deterministic helper
  functions are acceptable when named and tested clearly.

### Models and queries

- Models define relationships, casts, scopes, and model-level invariants; they
  MUST NOT become request or response handlers.
- Relationship names MUST describe their domain meaning and declare correct
  foreign/local keys when conventions do not apply.
- Reusable query conditions SHOULD use local scopes or query objects.
- Prevent N+1 queries with intentional eager loading. Do not eager-load unrelated
  graphs by default.
- Select only needed columns for high-volume or sensitive queries.
- Raw SQL is permitted only when the query builder cannot express the operation
  clearly; all values MUST be bound, never concatenated.

### Transactions and concurrency

- A business operation that performs multiple dependent writes MUST use one
  database transaction covering the complete consistency boundary.
- External network calls and slow file uploads SHOULD NOT run inside a database
  transaction. Persist intent/state, commit, then dispatch external work safely.
- Code that can receive duplicate requests or job retries MUST be idempotent or
  protected by unique constraints, locks, idempotency keys, or state checks.
- Never use unconstrained parallel writes against the same records.

### API responses

- API endpoints MUST return a consistent JSON envelope and appropriate HTTP
  status codes following the existing project contract.
- Successful responses MUST NOT mask partial persistence or failed downstream
  work.
- Validation errors, authentication failures, authorization failures, missing
  resources, conflicts, and server errors MUST remain distinguishable.
- Do not expose stack traces, SQL, filesystem paths, tokens, or provider secrets.
- Use API Resources/transformers when response mapping is non-trivial or reused.

### Jobs, events, and external providers

- Queue jobs MUST be safe to retry and MUST define observable failure behavior.
- Pass stable identifiers to jobs rather than large mutable model graphs.
- Provider-specific code for R2, payment gateways, email, or video services MUST
  remain behind a focused service boundary.
- Log provider request identifiers and safe context, but never credentials or
  sensitive payloads.
- A provider failure MUST NOT be reported as application success unless the
  contract explicitly defines an accepted asynchronous state.

## 5. Database and migration rules

- Every schema change MUST use a migration with a valid `down()` strategy, or
  clearly document why rollback is unsafe.
- Foreign keys, nullability, defaults, unsigned types, and column widths MUST
  match the domain and existing referenced columns.
- Add indexes for foreign keys and proven lookup/sort patterns; do not add indexes
  speculatively without considering write cost.
- Use unique constraints to enforce true invariants at the database boundary.
- Destructive migrations MUST include a safe rollout/backfill strategy and MUST
  NOT silently discard production data.
- Monetary values MUST use integer minor units or fixed decimal types, never
  binary floating point.
- Timestamps and week/date boundaries MUST use an explicit application timezone.
- Seeders and factories MUST be deterministic enough for repeatable tests and
  MUST NOT contain production secrets or depend on external services.

## 6. Security rules

- Every protected operation MUST authenticate the caller and authorize the
  specific resource/action.
- Use policies, gates, or explicit service authorization; hiding a UI control is
  not authorization.
- Guard mass assignment with `$fillable`/`$guarded` and map accepted fields
  explicitly.
- Escape output in Blade by default. Raw HTML rendering requires trusted,
  sanitized content and a documented reason.
- File uploads MUST validate size, media type, extension, ownership, and storage
  key. Never trust the original filename as a storage path.
- Refresh tokens and session cookies MUST use the approved HttpOnly/Secure/SameSite
  contract. Access tokens and secrets MUST NOT be logged.
- Error messages MUST not reveal whether unrelated accounts, tokens, or private
  objects exist.

## 7. PHP syntax and style

- Code MUST be compatible with PHP `^8.2` and Laravel 11.
- Follow PSR-12 and Laravel conventions. Laravel Pint is the formatting authority.
- New PHP files SHOULD declare strict types when consistent with the surrounding
  module; do not mix coercion assumptions within one boundary.
- Use scalar, object, union, nullable, and return types wherever the contract is
  known. Avoid `mixed` unless the boundary genuinely requires it.
- Class names use `PascalCase`; methods and variables use `camelCase`; database
  columns use `snake_case`; constants use `UPPER_SNAKE_CASE`.
- Names MUST describe domain meaning. Avoid vague names such as `data`, `info`,
  `handleThing`, `temp`, or `result` when a precise name exists.
- Methods SHOULD be short and operate at one abstraction level. Extract logic
  when a method combines validation, querying, mutation, and presentation.
- Prefer early returns over deeply nested conditionals.
- Avoid boolean parameters that obscure behavior; use separate methods or a
  value object when the modes have different meaning.
- Comments explain why, invariants, or provider constraints; they MUST NOT narrate
  obvious code or preserve dead code.
- Remove unused imports and dead code. Do not leave debug calls such as `dd()`,
  `dump()`, `var_dump()`, or temporary logging.
- Translation keys MUST be used for user-facing CMS strings where the surrounding
  feature is localized.

## 8. Testability and testing rules

- Every bug fix MUST include a regression test when the behavior can be exercised
  reliably in the repository.
- Unit tests cover pure domain logic, range/date calculations, value objects, and
  deterministic service decisions.
- Feature tests cover routes, middleware, validation, authorization, persistence,
  transactions, and JSON contracts.
- Tests MUST assert observable behavior, not private implementation details.
- Tests MUST be isolated, deterministic, timezone-aware, and independent of test
  execution order.
- External providers MUST be faked or mocked at their boundary. Tests MUST NOT
  call real R2, VNPay, Vimeo, SMTP, or production APIs.
- Use factories/builders for relevant state; avoid oversized fixtures containing
  unrelated fields.
- For database regressions, assert both the intended write and the absence of
  invalid/duplicate writes.

## 9. Required verification commands

Choose commands proportional to the change. The preferred checks are:

```powershell
vendor\bin\pint --test
php artisan test
git diff --check
```

For focused work, run the smallest relevant test first, for example:

```powershell
php artisan test --filter=CartPriceTest
php artisan test tests/Unit/LearningStreak/RangeMergerTest.php
```

- Formatting MAY be applied only to files in task scope.
- If PHP, Composer, a database, Redis, or another required runtime is unavailable,
  the agent MUST report that limitation and MUST NOT claim tests passed.
- A pre-existing failure MUST be separated clearly from failures introduced by
  the task.

## 10. Definition of done

A task is complete only when:

- The requested behavior and contract are implemented.
- Authorization, validation, failure behavior, and edge cases were considered.
- Relevant tests were added or an explicit reason was given for not adding them.
- Applicable checks were executed and accurately reported.
- The diff contains no secrets, debug code, accidental generated files, or
  unrelated rewrites.
- Documentation/configuration is updated when the public contract or operational
  procedure changed.
- No commit or push was performed.
