# Superpowers for Antigravity

You have superpowers.

This profile adapts Superpowers workflows for Antigravity with strict single-flow execution.

## Core Rules

1. Prefer local skills in `.agent/skills/<skill-name>/SKILL.md`.
2. Execute one core task at a time with `task_boundary`.
3. Use `browser_subagent` only for browser automation tasks.
4. Track checklist progress in `<project-root>/docs/plans/task.md` (table-only live tracker).
5. Keep changes scoped to the requested task and verify before completion claims.

## Tool Translation Contract

When source skills reference legacy tool names, use these Antigravity equivalents:

- Legacy assistant/platform names -> `Antigravity`
- `Task` tool -> `browser_subagent` for browser tasks, otherwise sequential `task_boundary`
- `Skill` tool -> `view_file ~/.gemini/skills/<skill-name>/SKILL.md` (or project-local `.agent/skills/<skill-name>/SKILL.md`)
- `TodoWrite` -> update `<project-root>/docs/plans/task.md` task list
- File operations -> `view_file`, `write_to_file`, `replace_file_content`, `multi_replace_file_content`
- Directory listing -> `list_dir`
- Code structure -> `view_file_outline`, `view_code_item`
- Search -> `grep_search`, `find_by_name`
- Shell -> `run_command`
- Web fetch -> `read_url_content`
- Web search -> `search_web`
- Image generation -> `generate_image`
- User communication during tasks -> `notify_user`
- MCP tools -> `mcp_*` tool family

## Skill Loading

- First preference: project skills at `.agent/skills`.
- Second preference: user skills at `~/.gemini/skills`.
- If both exist, project-local skills win for this profile.
- Optional parity assets may exist at `.agent/workflows/*` and `.agent/agents/*` as entrypoint shims/reference profiles.
- These assets do not change the strict single-flow execution requirements in this file.

## Single-Flow Execution Model

- Do not dispatch multiple coding agents in parallel.
- Decompose large work into ordered, explicit steps.
- Keep exactly one active task at a time in `<project-root>/docs/plans/task.md`.
- If browser work is required, isolate it in a dedicated browser step.

## Verification Discipline

Before saying a task is done:

1. Run the relevant verification command(s).
2. Confirm exit status and key output.
3. Update `<project-root>/docs/plans/task.md`.
4. Report evidence, then claim completion.

---

# Elearning_CMS Project Guidelines & Rules

## 1. Project Architecture & Design Patterns
- **Framework & Stack**: Laravel 11, PHP 8.2+, MySQL 8.0, Vite (TailwindCSS/Bootstrap 5).
- **Service Layer Pattern**: All business logic, third-party API interactions (VNPAY, Vimeo, S3), complex database transactions, and data formatting MUST reside inside `app/Services/` classes (e.g., `CourseServices`, `PaymentServices`, `CouponServices`).
- **Thin Controllers**: Controllers inside `app/Http/Controllers/` must remain lightweight. Their sole responsibility is to receive requests, trigger Form Requests, invoke the corresponding Service method, and return a view or standardized JSON response.
- **Form Request Validation**: Never perform inline validation inside Controller methods for complex actions. Create and use dedicated `FormRequest` classes in `app/Http/Requests/`.
- **Authentication & Authorization**:
  - Web Admin: Session-based auth with `laravel-adminlte`.
  - Customer API: JWT-based auth via `tymon/jwt-auth`.
  - RBAC: Role and permission checks MUST use `spatie/laravel-permission` policies/middleware.

## 2. Coding Standards & Conventions
- **PHP Standards**: Follow PSR-12 coding standard. Use explicit type hinting for function arguments and return types.
- **Eloquent Best Practices**:
  - Always define `$fillable` or `$guarded` explicitly.
  - Avoid N+1 query problems by using eager loading (`with()`).
  - Use Server-side DataTables (`yajra/laravel-datatables-oracle`) for listing large datasets in the admin panel.
- **Standardized API Response**:
  - Return JSON responses with a consistent format:
    ```json
    {
      "success": true,
      "message": "Response message",
      "data": {},
      "errors": null
    }
    ```
- **Queue & Async Processing**: Heavy or time-consuming tasks (video processing with Vimeo API, sending verification emails, generating PDFs) MUST be dispatched to Laravel Queue Jobs (`app/Jobs/`).

## 3. Git & Safety Rules (STRICT)
- **NO AUTO COMMIT / PUSH**: **NEVER** execute `git commit`, `git push`, `git checkout -b`, `git merge`, or `git rebase` commands automatically. Always ask for explicit user confirmation before committing or pushing code.
- **Scope Contained Changes**: Keep code modifications strictly focused on the requested task. Do not refactor unrelated files without user request.
- **Environment & Secret Protection**: Never hardcode API keys, secrets, or credentials in codebase files. Always use `.env` and retrieve values via `config('services...')`.

## 4. Verification & Testing Discipline
- **Pre-completion Verification**: Run relevant syntax/lint checks or test commands (e.g., `php artisan test`, `php -l`) before claiming work is finished.
- **Live Task Tracking**: Maintain updated progress in `<project-root>/docs/plans/task.md`.

