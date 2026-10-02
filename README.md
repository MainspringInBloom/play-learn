# Play and Learn – Week 1 (Oct 1 – Oct 7)
**Yohannis Alem** · Python 101 v2.0 & Java 101 v2.0

## What is done this week
- Local setup: XAMPP (Apache + PHP + MySQL) and a GitHub repository
- The database: all **11 tables** in `db/schema.sql`
- Sample data in `db/seed.sql`: 6 demo users (1 admin, 2 parents, 3 students) and the 2 courses
- **One login page for all 3 roles.** After login, each user goes to their own home page:
  - Student → `student/index.php` (welcome + the 2 courses)
  - Parent → `parent/index.php` (their linked children)
  - Admin → `admin/index.php` (every table with its row count, and all users)
- Security: passwords stored with `password_hash()`, prepared SQL statements, escaped output, CSRF token on the login form, and a role check on every page

## How to run it
1. Start **Apache** and **MySQL** in XAMPP.
2. Copy the `play-and-learn` folder into `C:\xampp\htdocs\`.
3. Open http://localhost/play-and-learn/install.php and click **Install / Reset database**.
   (Or import `db/schema.sql` and then `db/seed.sql` in phpMyAdmin.)
4. Open http://localhost/play-and-learn/ and log in.

If your MySQL user or password is not `root` with no password, change it in `config.php`.

## Demo accounts
| Role    | Email                 | Password   |
|---------|-----------------------|------------|
| Admin   | admin@playlearn.com   | admin123   |
| Student | student@playlearn.com | student123 |
| Parent  | parent@playlearn.com  | parent123  |

## Database tables
| Table | What it stores |
|---|---|
| `users` | Everyone who logs in. `role` = admin, student or parent. `parent_id` links a student to a parent. |
| `courses` | Python 101 and Java 101 |
| `lessons` | The lessons in each course |
| `games`, `game_items` | One game per lesson and its questions |
| `quizzes`, `questions` | One quiz per lesson and its questions (each question has a topic) |
| `lesson_progress` | Which lessons each student finished |
| `game_scores` | Every game a student played |
| `quiz_attempts`, `attempt_answers` | Every quiz try and each answer in it |

## Files
```
play-and-learn/
├── config.php            database settings
├── install.php           one-click database setup
├── index.php             sends you to login or your home page
├── login.php, logout.php one login for all 3 roles
├── includes/             db.php (connection), auth.php (login + roles), header.php, footer.php
├── student/index.php     student home
├── parent/index.php      parent home
├── admin/index.php       admin home
├── db/schema.sql         the 11 tables
├── db/seed.sql           demo users + 2 courses
└── assets/               style.css + Bootstrap, jQuery, icons (works offline)
```

## Next week (Oct 8 – Oct 14)
Admin pages to add, edit and delete courses, lessons, games, quiz questions and users, and load all Python 101 and Java 101 content.
