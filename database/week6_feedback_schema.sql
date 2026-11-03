-- ===========================================================================
-- Play & Learn: week 6 additions (progress page + feedback messages)
--
-- The progress CHARTS need no new tables: they read quiz_attempts, game_attempts,
-- lesson_progress, lessons and courses from week 5.
--
-- The feedback messages (admin/parent -> student) need two tables:
--   parent_students    which students a parent may see and message. The base
--                      schema has no parent-to-student link, so without this the
--                      server cannot tell whose child is whose.
--   feedback_messages  the messages themselves.
--
-- Safe to re-run (IF NOT EXISTS). Run after week5_scores_schema.sql.
-- Who may send to whom (admin: anyone; parent: only linked children) is enforced
-- in the PHP endpoint, because MySQL CHECK constraints cannot look at other tables.
-- ===========================================================================

CREATE TABLE IF NOT EXISTS parent_students (
    parent_id  INT NOT NULL,
    student_id INT NOT NULL,
    linked_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (parent_id, student_id),
    INDEX idx_parent_students_student (student_id),
    CONSTRAINT fk_ps_parent FOREIGN KEY (parent_id)
        REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_ps_student FOREIGN KEY (student_id)
        REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT chk_ps_not_self CHECK (parent_id <> student_id)
);

CREATE TABLE IF NOT EXISTS feedback_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,               -- the recipient
    sender_id  INT NOT NULL,               -- an admin or a parent (checked in PHP)
    body       TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at    TIMESTAMP NULL DEFAULT NULL,   -- set when the student opens/marks it read
    INDEX idx_feedback_student_created (student_id, created_at),
    INDEX idx_feedback_sender (sender_id),
    CONSTRAINT fk_feedback_student FOREIGN KEY (student_id)
        REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_feedback_sender FOREIGN KEY (sender_id)
        REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT chk_feedback_body CHECK (CHAR_LENGTH(TRIM(body)) BETWEEN 1 AND 2000)
);
