# Play & Learn

A website with 3 user types — Admin (manage lessons/quizzes), Student
(play games, take quizzes, see scores), Parent (see their child's
progress) — built around two demo courses, Python 101 and Java 101.

**Stack:** HTML, CSS, JavaScript, jQuery, Bootstrap (frontend) · PHP
(backend) · MySQL (database)

## Status: week 6 frontend/backend, plus Java 101 quiz integration

- Login/session logic for all 3 roles
- Admin CRUD (add/view/edit/delete) for lessons and quizzes
- Student dashboard shell, wired to real course/lesson data
- Matching game and quiz page
- Progress page charts
- **Java 101 quiz backend (real data, no placeholders):**
  `GET /api/get_quiz.php` loads a quiz from MySQL and `POST /api/save_quiz_score.php`
  grades it on the server and saves the attempt (`quiz_attempts`) and each answer
  (`quiz_responses`). `GET /api/get_java_lessons.php` lists the Java 101 lessons.

Not yet built: complete sample lessons for Java and Python.
Game data and game score-saving API (the game pages still use sample data).
Progress and feedback endpoints (those pages still use sample data).
Admin/Parent feedback message logic.
Parent dashboard.

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
   - Ask a teammate for the test passwords; do not commit them.

## Current known gaps and placeholders

- **No student↔course enrollment table yet.** The dashboard shows the
  first course in the table rather than an enrolled one.
- **Courses are read-only in Admin.** CRUD thus far covers lessons and
  quizzes only, not courses themselves.
- **Deleting a lesson cascades to its quizzes** via `ON DELETE CASCADE`.
  There's a confirm dialog, but no separate "this also deletes N quizzes"
  warning — worth a team decision on whether that needs to be louder.

## API endpoints (quiz and lessons)

| Endpoint | Method | Who | What it does |
| --- | --- | --- | --- |
| `/api/get_java_lessons.php` | GET | anyone | Java 101 lessons: `{success, lessons[]}` |
| `/api/get_quiz.php?quiz_id=N` | GET | logged-in student | One quiz with questions and options. **Never sends `is_correct`**, so answers can't be read from the browser. |
| `/api/save_quiz_score.php` | POST (JSON) | logged-in student | Body: `{quizId, timeSpentSeconds, answers:[{question_id, option_id}]}`. The server grades, inserts one `quiz_attempts` row and one `quiz_responses` row per answer, and returns `{ok, attempt_id, score, correct_count, total_questions, results[]}`. Skipped questions are left out and count as not correct. |

All endpoints return JSON. Errors look like `{"ok": false, "error": "..."}` with a 4xx/5xx status.
Frontend code calls them only through `assets/js/pl-api.js` (`PL.api`), using root-relative
paths such as `/api/get_quiz.php`, so **serve the site from the repo root** (`php -S localhost:8000`).

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
