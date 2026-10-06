-- Week 3 schema fix: Admin CRUD can now edit lessons and quizzes, so it's
-- useful to know when a row was last changed (not just when it was created).
-- Run this against the existing play_and_learn database — it does not
-- touch existing data.

USE play_and_learn;

ALTER TABLE lessons
    ADD COLUMN updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        AFTER sort_order;

ALTER TABLE quizzes
    ADD COLUMN updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        AFTER title;
