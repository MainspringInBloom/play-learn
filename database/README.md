# Database branch — db-schema

Owns: `database/schema.sql`, `database/week3_updates.sql`, `config/db.php`.

## What's here
- `schema.sql` — full week 2 schema (users, courses, lessons, quizzes) with seed data.
- `week3_updates.sql` — adds `updated_at` to `lessons` and `quizzes` now that
  backend branch can edit them.
- `config/db.php` — a single shared PDO connection file. Backend pages
  require this directly; frontend student pages that query the DB also require it.

## Setup
```
mysql -u root -p < database/schema.sql
mysql -u root -p play_and_learn < database/week3_updates.sql
```
Then set `DB_PASS` in `config/db.php` to your local MySQL password.
NOTE: DO NOT INCLUDE CONFIG FILE IN COMMITS, your local mySQL password may end up being part of said commit.
