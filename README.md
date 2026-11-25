# Play & Learn

A website with 3 user types — Admin (manage lessons/quizzes), Student
(play games, take quizzes, see scores), Parent (see their child's
progress) — built around two demo courses, Python 101 and Java 101.

**Stack:** HTML, CSS, JavaScript, jQuery, Bootstrap (frontend) · PHP
(backend) · MySQL (database)

## Status

Everything the frontend does now talks to the real backend; there is no sample data left.

- Login/session logic for all 3 roles
- **Students** follow each course in order: lesson > quiz > next lesson > quiz.
  Passing a quiz (60% or higher, `PL_PASS_PERCENT` in `includes/progress.php`) completes the
  lesson and opens the next one. Lessons with no quiz have a "Mark lesson complete" button.
  The syllabus shows Completed / In progress / Locked for every lesson and lets students
  revisit finished lessons and practise passed quizzes; practice runs are graded but never
  saved, so scores and progress are never overwritten.
- **Admins** create, edit and delete lessons, quizzes, the questions and answer options
  inside each quiz, and one game per lesson (Match the Output or Memory Match).
- Student dashboard with real progress, Quizzes and Games hubs, Progress page with charts and messages
- Parent dashboard (linked children, progress, messages) and Admin Students / Users / Family links pages
- Server-side grading (`api/save_quiz_score.php`); `is_correct` is never sent to the browser

Java 101 content (lesson text, quiz questions and one game per lesson) comes from
`database/week7_java101_content.sql`; Python 101 content is added in Admin.

Not built yet: student enrollment in courses, creating/deleting whole courses in Admin,
password reset, and the complete Python 101 quizzes and games.

## Setup

1. **Install PHP and MySQL** if you haven't — see each tool's own docs for
   Windows installers. Add both `bin` folders to your PATH (Environment
   Variables → User variables → Path), then open a **new** terminal before
   testing — PATH changes never apply to already-open windows.

2. **Load the schema:**
   ```
   mysql -u root -p < database/schema.sql
   mysql -u root -p play_and_learn < database/week3_updates.sql
   mysql -u root -p play_and_learn < database/week4_java101_lessons.sql
   mysql -u root -p play_and_learn < database/week5_scores_schema.sql
   mysql -u root -p play_and_learn < database/week6_feedback_schema.sql
   mysql -u root -p play_and_learn < database/week7_java101_content.sql
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
   - `parent@test.com` → `/parent/dashboard.php`
   - Ask a teammate for the test passwords; do not commit them.

## How the lesson flow works

- A lesson is **complete** when the student passes every quiz of that lesson that has at least one
  question. A quiz with no questions is hidden from students and does not block the lesson.
- A lesson is **locked** until every earlier lesson in its course (by *Order*, then id) is complete.
  Set `PL_LOCK_FUTURE_LESSONS` to `false` in `includes/progress.php` to open everything.
- Quizzes and games of a locked lesson are locked too (the API answers 403, not just the page).
- A quiz the student already passed is **practice**: `save_quiz_score.php` grades it and returns
  `practice: true` without writing anything.
- Admins control the order with the lesson's *Order* number, and can add quizzes, questions and a
  game from **Courses > lessons**. New quizzes open straight on their question editor.
- Editing a question updates its existing options in place, so students' past answers stay linked;
  removing an option removes the answers that picked it. Deleting a quiz or lesson cascades to scores.

## Known gaps

- **No student↔course enrollment table yet.** The dashboard offers every course through a switcher.
- **Courses are read-only in Admin.** CRUD covers lessons, quizzes, questions and games, not courses.
- **Deleting a lesson cascades to its quizzes and scores** via `ON DELETE CASCADE`. There is a
  confirm dialog but no per-item count in the warning.
- Admin forms have no CSRF tokens (same as before); add them before any real deployment.
- `api/get_java_lessons.php` is not used by any page and returns `success` instead of `ok`.

## API endpoints

All return JSON; errors look like `{"ok": false, "error": "..."}` with a 4xx/5xx status.
The user always comes from the session, never from the request.

| Endpoint | Method | Who | What it does |
| --- | --- | --- | --- |
| `/api/get_quiz.php?quiz_id=N` | GET | student | Quiz with questions and options (never `is_correct`), plus `passed`. 403 if the lesson is locked. |
| `/api/save_quiz_score.php` | POST | student | `{quizId, timeSpentSeconds, answers[]}` → graded result with `passed`, `practice`, `lesson_completed`, `results[]`. |
| `/api/get_game.php?lesson_id=N&type=T` | GET | student | The lesson's game: `{ok, game:{title, context, instructions, pairs[]}}`. 404 if the lesson has no game or `type` doesn't match; 403 if the lesson is locked. |
| `/api/save_game_score.php` | POST | student | `{lesson_id, score, max_score, time_spent_seconds}`. The server checks `max_score` equals pairs × 50 and `0 <= score <= max_score`; 403 if the lesson is locked. |
| `/api/save_lesson_progress.php` | POST | student | `{lesson_id}`: completes a lesson that has no quiz. |
| `/api/get_progress.php[?student_id=N]` | GET | student / parent / admin | Totals, charts data, courses, game scores. Parents only for linked children (403). |
| `/api/get_feedback.php[?student_id=N]` | GET | student / parent / admin | Messages, newest first. |
| `/api/mark_feedback_read.php` | POST | student | `{message_id}`. |
| `/api/send_feedback.php` | POST | parent / admin | `{student_id, body}` (1–2000 chars). Parents only to their children. |
| `/api/get_children.php` | GET | parent | Linked children with summaries. |
| `/api/admin/get_students_overview.php` | GET | admin | Every student with stats and parents. |
| `/api/admin/get_users.php?role=student\|parent` | GET | admin | Users with their links. |
| `/api/admin/create_user.php` | POST | admin | `{role, first_name, last_name, email, password}` (hashed; 409 on duplicate email). |
| `/api/admin/delete_user.php` | POST | admin | `{user_id}`; students and parents only. |
| `/api/admin/add_link.php`, `remove_link.php` | POST | admin | `{parent_id, student_id}`. |
| `/api/get_java_lessons.php` | GET | anyone | Java 101 lessons (legacy, unused). |

Frontend code calls them only through `assets/js/pl-api.js` (`PL.api`), using root-relative
paths, so **serve the site from the repo root** (`php -S localhost:8000`).

## Game data

A lesson's game lives in `lessons.game_type` + `lessons.game_data` (JSON), edited in Admin
(**Courses > lesson > Add game**). Shapes: Match the Output pairs are `{code, output}`;
Memory Match pairs are `{a, b}`; each game needs 2–12 pairs. To add a new game type, add it to
`includes/games.php`, write an engine in `assets/js/game-*.js`, and add a label in `includes/scores.php`.

## Verifying a quiz save

1. Log in as the student account and open `http://localhost:8000/student/quiz.php?quiz_id=5`.
2. Answer and finish the quiz; the page should say **Score saved.**
3. In MySQL:
   ```sql
   SELECT * FROM quiz_attempts  ORDER BY attempt_id DESC LIMIT 3;
   SELECT * FROM quiz_responses ORDER BY response_id DESC LIMIT 5;
   ```

## Working copy tip

Edit and run from the same folder. A separate XAMPP copy (`htdocs/play-learn`) does not
update when you edit your Git clone and uses different URL paths.
