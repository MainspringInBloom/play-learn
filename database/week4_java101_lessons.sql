-- Week 4: load the Java 101 lesson + quiz outline (titles only).
-- Lesson content, game_type, and game_data are left NULL on purpose; they get
-- filled in later through the Admin edit form as that functionality lands.
--
-- NOTE: This clears Java 101's existing lessons first (quizzes go with
-- them via ON DELETE CASCADE), then reloads the full outline. That also removes
-- the synthetic Java test lessons from the earlier seed data. Only run or re-run
-- this BEFORE students have saved results tied java101's lessons and quizzes.


USE play_and_learn;

START TRANSACTION;

-- Make sure the Java 101 course exists (it should from the week 2 seed data).
INSERT INTO courses (title, description, created_by)
SELECT 'Java 101',
       'Intro to Java: syntax, classes, and basic OOP concepts.',
       (SELECT user_id FROM users WHERE role = 'admin' ORDER BY user_id LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE title = 'Java 101');

SET @java_course_id = (SELECT course_id FROM courses WHERE title = 'Java 101' ORDER BY course_id LIMIT 1);

-- Clear the old Java 101 lessons (cascades to their quizzes).
DELETE FROM lessons WHERE course_id = @java_course_id;

-- Lessons: sort_order gives the teaching sequence.
INSERT INTO lessons (course_id, title, content, sort_order) VALUES
    (@java_course_id, 'Setting Up Your Environment',  NULL,  1),
    (@java_course_id, 'Your First Java Program',      NULL,  2),
    (@java_course_id, 'Variables and Data Types',     NULL,  3),
    (@java_course_id, 'Operators and Expressions',    NULL,  4),
    (@java_course_id, 'Working with Strings',         NULL,  5),
    (@java_course_id, 'Conditionals',                 NULL,  6),
    (@java_course_id, 'Loops',                        NULL,  7),
    (@java_course_id, 'Arrays',                       NULL,  8),
    (@java_course_id, 'Methods',                      NULL,  9),
    (@java_course_id, 'Classes and Objects',          NULL, 10);

-- Quizzes: one per lesson, titled "<Lesson title> Quiz".
INSERT INTO quizzes (lesson_id, title)
SELECT lesson_id, CONCAT(title, ' Quiz')
FROM lessons
WHERE course_id = @java_course_id
ORDER BY sort_order;

COMMIT;

-- Quick check: expect 10 lessons and 10 quizzes for Java 101.
SELECT l.sort_order, l.title AS lesson, q.title AS quiz
FROM lessons l
LEFT JOIN quizzes q ON q.lesson_id = l.lesson_id
WHERE l.course_id = @java_course_id
ORDER BY l.sort_order;
