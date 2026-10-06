# Frontend branch — frontend-dashboard

Owns: `includes/header.php`, `includes/footer.php`, `assets/css/style.css`,
`student/*`.

## What's here
- `includes/header.php` / `footer.php` — shared student-facing nav shell
  (Dashboard / Quizzes / Games / Progress), styled to match the mockup.
- `assets/css/style.css` — cream/dark-green palette from the mockup.
- `student/dashboard.php` — wired to real `courses`/`lessons` data. Stats
  (lessons completed, quiz average, games played) and the activity feed are
  intentionally placeholder `—` values with a comment — real data needs the
  `scores`/progress tables, which land in weeks 5–6.
- `student/syllabus.php`, `student/lesson.php` — real lesson content.
- `student/quizzes.php`, `student/games.php`, `student/progress.php` — nav
  stubs so header links don't 404; built out weeks 5–6.

## Depends on
- `config/db.php` (db-schema branch) and `includes/auth.php`
  (backend-admin-crud branch) — merge both first, or these pages will
  fatal-error on a missing file.
