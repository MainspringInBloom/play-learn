# Play & Learn

A website with 3 user types — Admin (manage lessons/quizzes), Student
(play games, take quizzes, see scores), Parent (see their child's
progress) — built around two demo courses, Python 101 and Java 101.

**Stack:** HTML, CSS, JavaScript, jQuery, Bootstrap (frontend) · PHP
(backend) · MySQL (database)

## Status: week 3 complete

- Login/session logic for all 3 roles
- Admin CRUD (add/view/edit/delete) for lessons and quizzes
- Student dashboard shell, wired to real course/lesson data

Not yet built (weeks 4–8, per the project plan): course content loaded
for both courses, the matching game, quiz-taking with scoring, the
scores/progress tables, and the progress charts.


## Setup

1. **Install PHP and MySQL** if you haven't — see each tool's own docs for
   Windows installers. Add both `bin` folders to your PATH (Environment
   Variables → User variables → Path), then open a **new** terminal before
   testing — PATH changes never apply to already-open windows.

2. **Load the schema:**
   ```
   mysql -u root -p < database/schema.sql
   mysql -u root -p play_and_learn < database/week3_updates.sql
   ```

3. **Set your DB password** in `config/db.php` (`DB_PASS`).
By default db.php has a placeholder password, this is by design, the host
should open db.php and replace the placeholder field with their local MySQL password.
NOTE: ALWAYS exclude db.php from commits unless you explicitly made a design change to
it and are certain the placeholder is still there.

4. **Run the dev server** from the repo root:
   ```
   php -S localhost:8000
   ```
   Keep that terminal visible — PHP fatal errors print there even when the
   browser just shows a blank page.

5. **Log in** at `http://localhost:8000/login.php`:
   - `admin@test.com` → `/admin/index.php`
   - `student@test.com` → `/student/dashboard.php`
   - `parent@test.com` → not built yet (later week)

## Current known gaps and placeholders

- **Dashboard stats and activity feed.** 
  "Lessons completed," "quiz average," "games played," and recent activity all show
  `—` or a stub message — there's no `scores`/progress table yet (weeks
  5–6 per the schedule). Structure is already wired so swapping in real
  queries later is a drop-in change.
- **No student↔course enrollment table yet.** The dashboard shows the
  first course in the table rather than an enrolled one.
- **Courses are read-only in Admin.** CRUD thus far covers lessons and
  quizzes only, not courses themselves.
- **Deleting a lesson cascades to its quizzes** via `ON DELETE CASCADE`.
  There's a confirm dialog, but no separate "this also deletes N quizzes"
  warning — worth a team decision on whether that needs to be louder.

