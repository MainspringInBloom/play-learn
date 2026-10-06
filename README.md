# Backend branch — backend-admin-crud

Owns: `includes/auth.php`, `includes/admin_header.php`, `login.php`,
`logout.php`, `admin/*`.

## What's here
- `includes/auth.php` — session helpers (`require_login`, `require_role`,
  `redirect_for_role`) used by every protected page, frontend included.
- `login.php` / `logout.php` — session-based login for all 3 roles,
  `password_verify()` against `users.password_hash`.
- `admin/` — full CRUD for lessons and quizzes: list, add, edit, delete,
  all via prepared statements. Delete is POST-only with a confirm dialog.

## Depends on
- `config/db.php` and the current `users`/`lessons`/`quizzes` schema from
  the **db-schema** branch — merge that branch first or these pages will
  fatal-error on a missing file/column.

## Known gap before demo
Seeded `users.password_hash` values are still the
`$2y$10$REPLACE_WITH_REAL_HASH` placeholder from week 2 — generate real
hashes with `password_hash()` before testing login end-to-end.
