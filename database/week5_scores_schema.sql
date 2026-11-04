-- Week 5: score-saving schema (quizzes, game results, lesson progress).
--
-- Run AFTER schema.sql (users, courses, lessons, quizzes must already exist).
--
-- Synthetic data not included.
--
-- Tables added:
--   questions        quiz questions (+ code snippet and explanation for the quiz page)
--   answer_options   choices for each question, with the correct one flagged
--   lesson_progress  per-student lesson status + time spent ("lessons completed")
--   quiz_attempts    one row per quiz attempt (percent, raw points, question count)
--   quiz_responses   the option picked for each question in an attempt
--   game_attempts    one row per finished game (points, max points, time)
--
-- Not included (week 6): parent_student, needed for the Parent view.
--
-- Run from Command Prompt:  mysql -u root -p < database/week5_scores_schema.sql

USE play_and_learn;

-- ---------------------------------------------------------------------------
-- Dev reset (uncomment to rebuild; children are dropped before parents)
-- ---------------------------------------------------------------------------
-- DROP TABLE IF EXISTS quiz_responses;
-- DROP TABLE IF EXISTS quiz_attempts;
-- DROP TABLE IF EXISTS game_attempts;
-- DROP TABLE IF EXISTS lesson_progress;
-- DROP TABLE IF EXISTS answer_options;
-- DROP TABLE IF EXISTS questions;

-- ---------------------------------------------------------------------------
-- Quiz content
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS questions (
    question_id   INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id       INT NOT NULL,
    question_text TEXT NOT NULL,
    code_snippet  TEXT NULL,               -- code block shown above the question
    explanation   TEXT NULL,               -- feedback shown after answering
    sort_order    INT NOT NULL DEFAULT 0,
    INDEX idx_questions_quiz_order (quiz_id, sort_order),
    CONSTRAINT fk_questions_quiz FOREIGN KEY (quiz_id)
        REFERENCES quizzes (quiz_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS answer_options (
    option_id   INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    is_correct  BOOLEAN NOT NULL DEFAULT FALSE,
    sort_order  INT NOT NULL DEFAULT 0,
    -- Lets quiz_responses prove an option really belongs to its question.
    UNIQUE KEY uq_option_question (question_id, option_id),
    INDEX idx_options_question_order (question_id, sort_order),
    CONSTRAINT fk_options_question FOREIGN KEY (question_id)
        REFERENCES questions (question_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------------
-- Student activity
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS lesson_progress (
    progress_id        INT AUTO_INCREMENT PRIMARY KEY,
    student_id         INT NOT NULL,
    lesson_id          INT NOT NULL,
    status             ENUM('not_started','in_progress','completed')
                           NOT NULL DEFAULT 'not_started',
    time_spent_seconds INT NOT NULL DEFAULT 0,
    completed_at       TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_student_lesson (student_id, lesson_id),   -- one row per student+lesson (upsert)
    INDEX idx_progress_lesson (lesson_id),
    INDEX idx_progress_student_completed (student_id, completed_at),
    CONSTRAINT fk_progress_student FOREIGN KEY (student_id)
        REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_lesson FOREIGN KEY (lesson_id)
        REFERENCES lessons (lesson_id) ON DELETE CASCADE,
    CONSTRAINT chk_progress_time CHECK (time_spent_seconds >= 0),
    -- A completed lesson must carry a timestamp, or the weekly queries miss it.
    CONSTRAINT chk_progress_completed CHECK (status <> 'completed' OR completed_at IS NOT NULL)
);

CREATE TABLE IF NOT EXISTS quiz_attempts (
    attempt_id      INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT NOT NULL,
    quiz_id         INT NOT NULL,
    score           DECIMAL(5,2) NULL,     -- percent, e.g. 85.00; NULL until finished
    correct_count   INT NULL,
    total_questions INT NULL,              -- frozen at attempt time (Admin may edit the quiz later)
    started_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    finished_at     TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_attempts_student_finished (student_id, finished_at),
    INDEX idx_attempts_quiz_student (quiz_id, student_id),
    CONSTRAINT fk_attempts_student FOREIGN KEY (student_id)
        REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_attempts_quiz FOREIGN KEY (quiz_id)
        REFERENCES quizzes (quiz_id) ON DELETE CASCADE,
    CONSTRAINT chk_attempt_score CHECK (score IS NULL OR (score >= 0 AND score <= 100)),
    CONSTRAINT chk_attempt_counts CHECK (
        correct_count IS NULL OR total_questions IS NULL OR correct_count <= total_questions),
    -- An unfinished attempt must not have a score (keeps averages honest).
    CONSTRAINT chk_attempt_finished CHECK (score IS NULL OR finished_at IS NOT NULL)
);

CREATE TABLE IF NOT EXISTS quiz_responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id  INT NOT NULL,
    question_id INT NOT NULL,
    option_id   INT NOT NULL,              -- the option the student picked
    is_correct  BOOLEAN NOT NULL,          -- snapshot at answer time; later edits don't rewrite history
    UNIQUE KEY uq_attempt_question (attempt_id, question_id),   -- one answer per question per attempt
    INDEX idx_responses_option (question_id, option_id),
    CONSTRAINT fk_responses_attempt FOREIGN KEY (attempt_id)
        REFERENCES quiz_attempts (attempt_id) ON DELETE CASCADE,
    -- Composite key: the picked option must belong to this question. Cascades, so
    -- deleting a question/option from Admin never trips over saved answers.
    CONSTRAINT fk_responses_option FOREIGN KEY (question_id, option_id)
        REFERENCES answer_options (question_id, option_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS game_attempts (
    game_attempt_id    INT AUTO_INCREMENT PRIMARY KEY,
    student_id         INT NOT NULL,
    lesson_id          INT NOT NULL,
    score              INT NOT NULL,       -- points earned
    max_score          INT NOT NULL,       -- best possible for that game
    time_spent_seconds INT NOT NULL DEFAULT 0,
    completed_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_games_student_completed (student_id, completed_at),
    INDEX idx_games_lesson (lesson_id),
    CONSTRAINT fk_games_student FOREIGN KEY (student_id)
        REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_games_lesson FOREIGN KEY (lesson_id)
        REFERENCES lessons (lesson_id) ON DELETE CASCADE,
    CONSTRAINT chk_game_score CHECK (max_score > 0 AND score >= 0 AND score <= max_score),
    CONSTRAINT chk_game_time CHECK (time_spent_seconds >= 0)
);

-- Quick check: expect 10 tables in total (the 4 from schema.sql + these 6).
SHOW TABLES;
