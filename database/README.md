# Database branch — db-schema

Owns: `database/schema.sql`, `database/week3_updates.sql`, `config/db.php`.

## What's here
- `schema.sql` — full week 2 schema (users, courses, lessons, quizzes) with seed data.
- `week3_updates.sql` — adds `updated_at` to `lessons` and `quizzes` now that
  backend branch can edit them.
- `week4_java101_lessons.sql` - adds the course `Java 101` and ten sequential lessons with accompanying quizzes to the database.
- `week5_scores_schema.sql` - adds several tables: `questions, answer_options, lesson_progress, quiz_attempts, quiz_responses, and game_attempts` to support quiz and scorekeeping functionality
- `config/db.php` — a single shared PDO connection file. Backend pages
  require this directly; frontend student pages that query the DB also require it.

## Setup
```
mysql -u root -p < database/schema.sql
mysql -u root -p play_and_learn < database/week3_updates.sql
mysql -u root -p play_and_learn < database/week4_java101_lessons.sql
mysql -u root -p play_and_learn < database/week5_scores_schema.sql
```
Then set `DB_PASS` in `config/db.php` to your local MySQL password.
NOTE: DO NOT INCLUDE CONFIG FILE IN COMMITS, your local mySQL password may end up being part of said commit.
