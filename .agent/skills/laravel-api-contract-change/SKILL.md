---
name: laravel-api-contract-change
description: Safely create, change, deprecate, or debug a Laravel API contract in Elearning CMS. Use for changes to routes, JWT/auth middleware, request validation, API controllers, JSON responses, pagination, errors, ownership checks, or when Vue and Laravel request/response behavior differs or an API returns 4xx/5xx unexpectedly.
---

# Laravel API Contract Change

Use this playbook to make an API change as an explicit, verified contract change
instead of an isolated controller edit.

## Required preflight

1. Read `docs/ai/PROJECT_RULES.md` and every scoped rule before any change.
2. Run `git status --short`; preserve all pre-existing changes.
3. Trace the current path before proposing a fix:

```text
route → middleware → Form Request/validation → controller → service
→ model/query/transaction → JSON response → tests → frontend consumer
```

4. Inspect the Vue consumer when it is available. Check endpoint configuration,
   API service/composable, Vue Query key/mutation, and UI loading/error handling.
   If the frontend workspace is unavailable, report this as downstream validation.

## Map the contract

Before editing, record the following in the task analysis or implementation plan:

| Contract part | Verify |
|---|---|
| Route | HTTP method, path, route name, path/query parameters |
| Access | JWT/session middleware, customer identity, ownership/permission rule |
| Request | Required/optional fields, types, ranges, defaults, normalization |
| Success | HTTP status, JSON envelope, fields, nullable/empty semantics |
| Errors | Applicable 401, 403, 404, 409, 422, and 5xx behavior |
| Side effects | Database writes, transaction boundary, jobs/events/provider calls |
| Consumers | Vue endpoint/composable/query key and any CMS or webhook caller |

Do not infer the contract from a frontend call alone. Treat the route and
server-side behavior as the authority, then align all consumers deliberately.

## Choose the change class

- **Additive:** adding an optional request/response field or endpoint without
  changing existing behavior. Preserve old consumers and add focused coverage.
- **Coordinated behavior change:** changing required fields, response shape,
  ownership, price/progress calculation, or status code. Update Laravel tests
  and all known Vue consumers in the same task.
- **Breaking change:** removing/renaming a field or path, changing a field type,
  or changing authorization semantics for existing clients. Stop and obtain an
  explicit migration/compatibility decision before implementation.

## Implement at the correct boundary

### Route and access

- Put only transport mapping in `routes/api.php`.
- Reuse `JWTVerifyCustomer` and existing authorization patterns; UI visibility is
  never authorization.
- Resolve the authenticated customer from the approved server-side identity, not
  a client-supplied customer ID.

### Validation and controller

- Use a Form Request for new or materially changed request contracts when
  practical in the surrounding module.
- Keep the controller as an HTTP adapter: receive validated input, invoke a
  focused operation, and return the mapped response.
- Never trust client-supplied price, entitlement, completion, ownership, role,
  or aggregate progress. Recompute or authorize it on the server.

### Service, persistence, and providers

- Put multi-step domain work in a focused service method.
- Wrap dependent database writes in one transaction at the business-operation
  boundary.
- Do not keep external R2, payment, Vimeo, mail, or queue work inside a long
  database transaction. Persist state, commit, then dispatch/process safely.
- Make retryable request/job side effects idempotent with state checks, locks,
  idempotency keys, or unique constraints as appropriate.

### Response and failure behavior

- Follow the existing API JSON envelope and use the correct HTTP status.
- Keep validation, authentication, authorization, not-found, conflict, and
  unexpected server failures distinguishable.
- Do not return success with empty/stale data when persistence or a required
  provider action failed.
- Do not expose stack traces, SQL, paths, tokens, or provider credentials.

## Verify the full contract

Add or update the smallest relevant feature test. Cover every applicable case:

```text
unauthenticated request     → 401
authenticated wrong owner   → 403
invalid request             → 422 with field errors
missing accessible resource → 404
valid request               → expected status, JSON shape, and persistence
duplicate/retry request     → no duplicated side effect when relevant
```

For an existing API regression, write the focused failing test before the fix
when the test environment can exercise it. Fake provider boundaries; never call
real R2, VNPay, Vimeo, SMTP, or production services from tests.

Run checks proportional to the change:

```powershell
php artisan test --filter=<RelevantTest>
vendor\bin\pint --test
php artisan test
git diff --check
```

If PHP, database configuration, or another dependency is unavailable, report the
exact limitation and do not claim the API was verified end-to-end.

## Completion report

Report:

1. Old versus new contract, including compatibility class.
2. Authorization and server-side trust checks performed.
3. Laravel files and known frontend consumers changed.
4. Tests/checks actually run and their results.
5. Any consumer, migration, deployment, or provider verification still required.

Never commit, push, deploy, or create a pull request.
