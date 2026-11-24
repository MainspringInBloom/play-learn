-- Week 7: Java 101 content: lesson text, quiz questions and one game per lesson.
--
-- Run AFTER schema.sql, week3_updates.sql, week4_java101_lessons.sql and week5_scores_schema.sql:
--     mysql -u root -p play_and_learn < database/week7_java101_content.sql
--
-- Safe to run more than once:
--   * lesson content is written only where it is still empty (admin edits are kept);
--   * a lesson gets its game only if it has no game_type yet;
--   * quiz questions are added only to Java 101 quizzes that have NO questions
--     (a quiz you already filled in by hand is left alone).
--
-- Rows are matched by lesson title inside the Java 101 course, never by id, so the
-- script works with whatever ids your database has.

USE play_and_learn;

SET @java := (SELECT course_id FROM courses WHERE title = 'Java 101' ORDER BY course_id LIMIT 1);

-- Which Java quizzes are empty right now? (remembered before any rows are added)
DROP TEMPORARY TABLE IF EXISTS tmp_empty_java_quizzes;
CREATE TEMPORARY TABLE tmp_empty_java_quizzes AS
    SELECT q.quiz_id
    FROM quizzes q
    JOIN lessons l ON l.lesson_id = q.lesson_id
    WHERE l.course_id = @java
      AND NOT EXISTS (SELECT 1 FROM questions x WHERE x.quiz_id = q.quiz_id);


-- ===== Setting Up Your Environment =====
UPDATE lessons SET content = 'Before you write Java, you need the right tools.

The JDK (Java Development Kit) is the toolbox. It includes javac, the compiler that turns your source code (.java files) into bytecode (.class files). The JVM (Java Virtual Machine) then runs that bytecode, which is why Java programs can run on many kinds of computers.

Setup checklist:
1. Install a JDK (version 17 or newer).
2. Check it works: open a terminal and type  java -version  and  javac -version.
3. Pick an editor or IDE, such as VS Code, IntelliJ IDEA or Eclipse.

Tip: if the terminal says "command not found", the JDK''s bin folder is probably not on your PATH.'
WHERE course_id = @java AND title = 'Setting Up Your Environment' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'memory', game_data = '{"instructions": "Flip two cards to match each Java setup term with its meaning.", "pairs": [{"a": "JDK", "b": "Tools for building Java programs"}, {"a": "JVM", "b": "Runs compiled bytecode"}, {"a": "javac", "b": "The Java compiler"}, {"a": ".java", "b": "A source code file"}, {"a": ".class", "b": "A compiled bytecode file"}, {"a": "IDE", "b": "An editor for writing code"}]}'
WHERE course_id = @java AND title = 'Setting Up Your Environment' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does JDK stand for?', NULL, 'The JDK is the Java Development Kit: the compiler (javac) and other tools you need to build Java programs.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Setting Up Your Environment' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'Java Debug Kit' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'Java Development Kit' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'Java Deployment Key' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'Java Dynamic Kernel' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Setting Up Your Environment' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'Which tool compiles Java source code into bytecode?', NULL, 'javac is the Java compiler. The java command runs compiled programs.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Setting Up Your Environment' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'java' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'javadoc' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'javac' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'jar' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Setting Up Your Environment' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What file extension do Java source files use?', NULL, 'Source code is saved in .java files. The compiler produces .class files.', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Setting Up Your Environment' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '.class' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '.java' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '.jar' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT '.jvm' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Setting Up Your Environment' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does the JVM do?', NULL, 'The Java Virtual Machine runs compiled bytecode, so the same program can run on different computers.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Setting Up Your Environment' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'Writes your source code' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'Runs compiled bytecode' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'Downloads libraries' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'Draws the user interface' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Setting Up Your Environment' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

-- ===== Your First Java Program =====
UPDATE lessons SET content = 'Every Java program lives inside a class, and it starts running at the main method.

public class Hello {
    public static void main(String[] args) {
        System.out.println("Hello, world!");
    }
}

Things to notice:
- The file must be named Hello.java because the public class is called Hello.
- Each statement ends with a semicolon ( ; ).
- System.out.println prints text and then moves to a new line. System.out.print prints without a new line.
- Text goes in double quotes.

Compile with  javac Hello.java  and run with  java Hello.'
WHERE course_id = @java AND title = 'Your First Java Program' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'match_output', game_data = '{"instructions": "Pick a code card, then pick the output it prints.", "pairs": [{"code": "System.out.println(\\"Hi\\");", "output": "Hi"}, {"code": "System.out.print(\\"A\\");\\nSystem.out.print(\\"B\\");", "output": "AB"}, {"code": "System.out.println(2 + 3);", "output": "5"}, {"code": "System.out.println(\\"2 + 3\\");", "output": "2 + 3"}, {"code": "System.out.println(\\"A\\");\\nSystem.out.println(\\"B\\");", "output": "A\\nB"}]}'
WHERE course_id = @java AND title = 'Your First Java Program' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'Which method is where a Java program starts running?', NULL, 'The JVM looks for public static void main(String[] args) to start your program.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Your First Java Program' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'start' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'main' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'run' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'init' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Your First Java Program' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this code print?', 'System.out.print("Hi");
System.out.print("!");', 'print does not add a new line, so both pieces appear together.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Your First Java Program' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'Hi !' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '!Hi' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'Hi!' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'Nothing is printed' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Your First Java Program' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'How does a Java statement end?', NULL, 'Statements end with a semicolon.', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Your First Java Program' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'With a period' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'With a semicolon' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'With a colon' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'With nothing' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Your First Java Program' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'A public class named Hello must be saved in which file?', NULL, 'A public class must live in a file with exactly the same name plus .java.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Your First Java Program' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'hello.txt' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'Main.java' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'Hello.java' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'Hello.class' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Your First Java Program' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

-- ===== Variables and Data Types =====
UPDATE lessons SET content = 'A variable is a named box that holds a value. In Java every variable has a type that says what kind of value it can hold.

Common types:
- int: whole numbers, like 42
- double: numbers with a decimal point, like 3.14
- boolean: true or false
- char: a single character in single quotes, like ''A''
- String: text in double quotes, like "Sam"

int count = 5;
double price = 9.99;
boolean done = false;
String name = "Sam";

Careful: dividing two ints gives an int, so 7 / 2 is 3, not 3.5. Use the keyword final to make a value that cannot change.'
WHERE course_id = @java AND title = 'Variables and Data Types' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'memory', game_data = '{"instructions": "Flip two cards to match each data type with its meaning.", "pairs": [{"a": "int", "b": "A whole number"}, {"a": "double", "b": "A number with a decimal point"}, {"a": "boolean", "b": "true or false"}, {"a": "char", "b": "A single character"}, {"a": "String", "b": "Text in double quotes"}, {"a": "final", "b": "A variable that cannot change"}]}'
WHERE course_id = @java AND title = 'Variables and Data Types' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'Which type stores a whole number?', NULL, 'int holds whole numbers. double holds numbers with decimals.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Variables and Data Types' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'String' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'boolean' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'double' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'int' AS option_text, 1 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Variables and Data Types' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'Which declaration is valid Java?', NULL, 'The value must match the type: int needs a whole number, and strings need double quotes.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Variables and Data Types' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'int count = "5";' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'int count = 5;' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'String s = 5;' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'boolean b = yes;' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Variables and Data Types' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What type should hold the text "Sam"?', 'name = "Sam";', 'Text is stored in a String.', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Variables and Data Types' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'char' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'String' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'int' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'text' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Variables and Data Types' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this code print?', 'double x = 7 / 2;
System.out.println(x);', '7 / 2 is integer division, so the result is 3 before it is stored as the double 3.0.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Variables and Data Types' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '3.5' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '3' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '3.0' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT '4.0' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Variables and Data Types' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

-- ===== Operators and Expressions =====
UPDATE lessons SET content = 'Operators combine values into expressions.

Arithmetic: +  -  *  /  %
- % is the remainder: 10 % 3 is 1.
- Integer division drops the decimal part: 9 / 2 is 4.
- Multiplication and division happen before addition and subtraction: 2 + 3 * 4 is 14. Use parentheses to change the order.

Shortcuts: x += 2 means x = x + 2, and x++ adds one.

Comparison: ==  !=  >  <  >=  <=  (these give true or false)
Logical: &&  (and),  ||  (or),  !  (not)'
WHERE course_id = @java AND title = 'Operators and Expressions' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'match_output', game_data = '{"instructions": "Pick a code snippet, then pick the output it prints.", "pairs": [{"code": "System.out.println(10 % 3);", "output": "1"}, {"code": "int x = 4;\\nx++;\\nSystem.out.println(x);", "output": "5"}, {"code": "System.out.println(9 / 2);", "output": "4"}, {"code": "System.out.println(2 + 3 * 4);", "output": "14"}, {"code": "System.out.println(5 > 3);", "output": "true"}]}'
WHERE course_id = @java AND title = 'Operators and Expressions' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'System.out.println(10 % 3);', '% gives the remainder of the division: 10 divided by 3 leaves 1.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Operators and Expressions' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '3' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '1' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '0' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT '3.33' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Operators and Expressions' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'int x = 5;
x += 2;
System.out.println(x);', 'x += 2 is the same as x = x + 2.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Operators and Expressions' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '5' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '2' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '7' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT '52' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Operators and Expressions' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'System.out.println(7 / 2);', 'Both numbers are ints, so Java drops the decimal part.', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Operators and Expressions' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '3' AS option_text, 1 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '3.5' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '4' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT '2' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Operators and Expressions' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What is the value of b?', 'boolean b = (4 > 2) && (3 < 1);', '&& is true only if both sides are true. 3 < 1 is false.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Operators and Expressions' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'true' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'false' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '1' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'An error' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Operators and Expressions' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

-- ===== Working with Strings =====
UPDATE lessons SET content = 'A String holds text. Strings are objects, so they come with useful methods.

String word = "Java";
word.length()        gives 4
word.charAt(0)       gives ''J'' (positions start at 0)
word.toUpperCase()   gives "JAVA"
word.substring(1, 3) gives "av" (from position 1 up to, but not including, 3)
"Hi" + "!"           gives "Hi!" (+ joins strings)

Compare strings with .equals(), not ==. The == operator checks whether two variables point to the same object, while .equals() checks whether the text is the same:

"cat".equals("cat")  is true'
WHERE course_id = @java AND title = 'Working with Strings' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'match_output', game_data = '{"instructions": "Pick a code snippet, then pick the output it prints.", "pairs": [{"code": "System.out.println(\\"Java\\".length());", "output": "4"}, {"code": "System.out.println(\\"hello\\".charAt(0));", "output": "h"}, {"code": "System.out.println(\\"Hi\\" + \\"!\\");", "output": "Hi!"}, {"code": "System.out.println(\\"abc\\".toUpperCase());", "output": "ABC"}, {"code": "System.out.println(\\"Java\\".substring(1, 3));", "output": "av"}]}'
WHERE course_id = @java AND title = 'Working with Strings' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'System.out.println("Java".length());', 'length() counts the characters: J, a, v, a.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Working with Strings' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '3' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '5' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '4' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'Java' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Working with Strings' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'System.out.println("hello".charAt(1));', 'Positions start at 0, so charAt(1) is the second character.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Working with Strings' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'h' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'e' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'l' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'o' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Working with Strings' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'Which is the right way to compare the text of two Strings?', NULL, 'Use .equals() to compare text. == compares whether they are the same object.', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Working with Strings' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'a == b' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'a = b' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'a.equals(b)' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'a.compare(b)' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Working with Strings' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'System.out.println("Java".substring(1, 3));', 'substring(1, 3) starts at position 1 and stops before position 3.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Working with Strings' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'Ja' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'av' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'ava' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'va' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Working with Strings' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

-- ===== Conditionals =====
UPDATE lessons SET content = 'Conditionals let a program choose what to do.

int score = 85;
if (score >= 90) {
    System.out.println("A");
} else if (score >= 80) {
    System.out.println("B");
} else {
    System.out.println("Keep going");
}

Java checks each condition from the top and runs only the first block whose condition is true. The condition must be a boolean.

Combine conditions with && (and), || (or) and ! (not). Use == for numbers and .equals() for Strings.'
WHERE course_id = @java AND title = 'Conditionals' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'match_output', game_data = '{"instructions": "Pick a code snippet, then pick the output it prints.", "pairs": [{"code": "if (5 > 3) {\\n    System.out.println(\\"yes\\");\\n}", "output": "yes"}, {"code": "int a = 2;\\nif (a == 3) {\\n    System.out.println(\\"three\\");\\n} else {\\n    System.out.println(\\"other\\");\\n}", "output": "other"}, {"code": "int age = 20;\\nif (age >= 18 && age < 65) {\\n    System.out.println(\\"adult\\");\\n}", "output": "adult"}, {"code": "boolean ok = false;\\nif (!ok) {\\n    System.out.println(\\"retry\\");\\n}", "output": "retry"}]}'
WHERE course_id = @java AND title = 'Conditionals' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'int x = 5;
if (x > 3) {
    System.out.println("big");
} else {
    System.out.println("small");
}', 'x > 3 is true, so the if block runs.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Conditionals' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'small' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'big' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'bigsmall' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'Nothing' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Conditionals' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'Which keyword handles the "otherwise" case of an if?', NULL, 'else runs when the if condition is false.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Conditionals' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'then' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'otherwise' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'else' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'elif' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Conditionals' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'int n = 10;
if (n % 2 == 0) {
    System.out.println("even");
} else {
    System.out.println("odd");
}', '10 % 2 is 0, so n is even.', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Conditionals' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'odd' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'even' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '10' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'Nothing' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Conditionals' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does && mean in a condition?', NULL, '&& is logical AND: both sides must be true.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Conditionals' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'or' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'not' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'and' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'equals' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Conditionals' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

-- ===== Loops =====
UPDATE lessons SET content = 'Loops repeat code.

for loop (when you know how many times):
for (int i = 0; i < 3; i++) {
    System.out.println(i);
}
prints 0, 1 and 2. The three parts are: start (int i = 0), keep going while (i < 3), and step (i++).

while loop (repeat while a condition is true):
int n = 3;
while (n > 0) {
    n--;
}

break leaves a loop early. continue skips to the next round. Be careful: a loop whose condition never becomes false runs forever.'
WHERE course_id = @java AND title = 'Loops' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'match_output', game_data = '{"instructions": "Pick a code snippet, then pick the output it prints.", "pairs": [{"code": "for (int i = 0; i < 3; i++) {\\n    System.out.println(i);\\n}", "output": "0\\n1\\n2"}, {"code": "int n = 0;\\nwhile (n < 2) {\\n    System.out.println(n);\\n    n++;\\n}", "output": "0\\n1"}, {"code": "for (int i = 1; i <= 3; i++) {\\n    System.out.print(i);\\n}", "output": "123"}, {"code": "int sum = 0;\\nfor (int i = 1; i <= 4; i++) {\\n    sum += i;\\n}\\nSystem.out.println(sum);", "output": "10"}]}'
WHERE course_id = @java AND title = 'Loops' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'for (int i = 0; i < 3; i++) {
    System.out.println(i);
}', 'i starts at 0 and the loop stops when i reaches 3, so it prints 0, 1 and 2 on separate lines.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Loops' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '1 2 3' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '0 1 2' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '0 1 2 3' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'Nothing' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Loops' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'int n = 3;
while (n > 0) {
    n--;
}
System.out.println(n);', 'n counts down from 3 and the loop ends when n is 0.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Loops' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '3' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '1' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '-1' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT '0' AS option_text, 1 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Loops' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'Which keyword leaves a loop immediately?', NULL, 'break exits the nearest loop. continue only skips to the next round.', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Loops' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'stop' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'break' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'exit' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'continue' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Loops' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'How many times does this loop run?', 'for (int i = 1; i <= 5; i++) {
    // ...
}', 'i takes the values 1, 2, 3, 4 and 5.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Loops' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '4' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '5' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '6' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'It never stops' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Loops' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

-- ===== Arrays =====
UPDATE lessons SET content = 'An array holds several values of the same type.

int[] scores = {90, 75, 82};
scores[0]        is 90 (the first element: positions start at 0)
scores[2]        is 82
scores.length    is 3 (the number of elements; there are no parentheses)

int[] zeros = new int[3];   // three ints, each starting at 0

Loop over an array:
for (int i = 0; i < scores.length; i++) {
    System.out.println(scores[i]);
}
or with a for-each loop:
for (int s : scores) {
    System.out.println(s);
}

An array''s size is fixed once it is created. Using an index outside the array causes an ArrayIndexOutOfBoundsException.'
WHERE course_id = @java AND title = 'Arrays' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'memory', game_data = '{"instructions": "Flip two cards to match each array term with its meaning.", "pairs": [{"a": "array", "b": "A fixed-size list of same-type values"}, {"a": "index", "b": "A position number, starting at 0"}, {"a": "length", "b": "The number of elements in an array"}, {"a": "new", "b": "Creates a new array or object"}, {"a": "a[0]", "b": "The first element of a"}, {"a": "for-each", "b": "Visits every element in turn"}]}'
WHERE course_id = @java AND title = 'Arrays' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'int[] a = {4, 8, 15};
System.out.println(a[1]);', 'Positions start at 0, so a[1] is the second element.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Arrays' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '4' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '8' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '15' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT '1' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Arrays' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What is the index of the first element of an array?', NULL, 'Java arrays are zero-indexed.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Arrays' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '1' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '-1' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '0' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'It depends' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Arrays' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'int[] a = new int[3];
System.out.println(a[0]);', 'A new int array is filled with zeros.', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Arrays' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'null' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '0' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '1' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'An error' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Arrays' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'How do you get the number of elements in an array named a?', NULL, 'For arrays, length is a field, so there are no parentheses.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Arrays' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'a.size()' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'a.length()' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'a.length' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'length(a)' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Arrays' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

-- ===== Methods =====
UPDATE lessons SET content = 'A method is a named block of code you can run whenever you need it.

static int add(int a, int b) {
    return a + b;
}

- int before the name is the return type: the kind of value the method gives back.
- (int a, int b) are the parameters, the inputs.
- return sends the result back to the caller.
- void means the method returns nothing.

Calling it:
int total = add(2, 3);   // total is 5

Methods keep programs organized and let you reuse code instead of copying it. A method can call other methods.'
WHERE course_id = @java AND title = 'Methods' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'match_output', game_data = '{"instructions": "Pick a code snippet, then pick the output it prints.", "pairs": [{"code": "static int sq(int n) {\\n    return n * n;\\n}\\nSystem.out.println(sq(4));", "output": "16"}, {"code": "static void hi() {\\n    System.out.println(\\"Hi\\");\\n}\\nhi();\\nhi();", "output": "Hi\\nHi"}, {"code": "static int add(int a, int b) {\\n    return a + b;\\n}\\nSystem.out.println(add(2, 3));", "output": "5"}, {"code": "static String greet(String n) {\\n    return \\"Hi \\" + n;\\n}\\nSystem.out.println(greet(\\"Sam\\"));", "output": "Hi Sam"}]}'
WHERE course_id = @java AND title = 'Methods' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'static int add(int a, int b) {
    return a + b;
}
// ...
System.out.println(add(2, 3));', 'add returns the sum of its two inputs.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Methods' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '23' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '5' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '6' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'Nothing' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Methods' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does the keyword void mean for a method?', NULL, 'A void method does its work but does not return a value.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Methods' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'It returns zero' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'It returns nothing' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'It cannot be called' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'It returns text' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Methods' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'The inputs listed in a method''s parentheses are called:', NULL, 'They are the method''s parameters (the values you pass in are arguments).', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Methods' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'parameters' AS option_text, 1 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'return values' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'classes' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'loops' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Methods' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'static int twice(int n) {
    return n * 2;
}
// ...
System.out.println(twice(twice(2)));', 'twice(2) is 4, and twice(4) is 8.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Methods' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT '4' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT '6' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT '8' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT '16' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Methods' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

-- ===== Classes and Objects =====
UPDATE lessons SET content = 'A class is a blueprint. An object is something built from that blueprint.

class Dog {
    String name;

    Dog(String name) {      // constructor
        this.name = name;
    }

    void bark() {
        System.out.println(name + " says woof");
    }
}

Dog d = new Dog("Rex");
d.bark();                   // Rex says woof

- Fields (like name) store an object''s data.
- Methods (like bark) are what an object can do.
- A constructor runs when you create an object with new. It has the same name as the class.
- this means "the current object".

You can make many objects from one class, and each keeps its own field values.'
WHERE course_id = @java AND title = 'Classes and Objects' AND (content IS NULL OR content = '');
UPDATE lessons SET game_type = 'memory', game_data = '{"instructions": "Flip two cards to match each object-oriented term with its meaning.", "pairs": [{"a": "class", "b": "A blueprint for objects"}, {"a": "object", "b": "An instance of a class"}, {"a": "new", "b": "Creates an object"}, {"a": "constructor", "b": "Runs when an object is created"}, {"a": "field", "b": "A variable inside a class"}, {"a": "this", "b": "The current object"}]}'
WHERE course_id = @java AND title = 'Classes and Objects' AND game_type IS NULL;
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'Which keyword creates a new object?', NULL, 'new creates an object from a class and calls its constructor.', 1
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Classes and Objects' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'make' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'create' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'new' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'object' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Classes and Objects' AND qu.sort_order = 1
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What is a class?', NULL, 'A class describes what its objects store and can do.', 2
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Classes and Objects' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'A blueprint for objects' AS option_text, 1 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'A kind of loop' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'A single variable' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'A file extension' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Classes and Objects' AND qu.sort_order = 2
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What does this print?', 'class Dog { String name; }
// ...
Dog d = new Dog();
d.name = "Rex";
System.out.println(d.name);', 'The object d stores "Rex" in its name field.', 3
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Classes and Objects' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'Dog' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'name' AS option_text, 0 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'Rex' AS option_text, 1 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'null' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Classes and Objects' AND qu.sort_order = 3
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order)
SELECT q.quiz_id, 'What is a constructor?', NULL, 'A constructor has the same name as the class and sets up a new object.', 4
FROM quizzes q JOIN lessons l ON l.lesson_id = q.lesson_id
WHERE l.course_id = @java AND l.title = 'Classes and Objects' AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);
INSERT INTO answer_options (question_id, option_text, is_correct, sort_order)
SELECT qu.question_id, v.option_text, v.is_correct, v.sort_order
FROM questions qu
JOIN quizzes q ON q.quiz_id = qu.quiz_id
JOIN lessons l ON l.lesson_id = q.lesson_id
JOIN (
  SELECT 'A loop that builds arrays' AS option_text, 0 AS is_correct, 1 AS sort_order
  UNION ALL SELECT 'A method that runs when an object is created' AS option_text, 1 AS is_correct, 2 AS sort_order
  UNION ALL SELECT 'A comment' AS option_text, 0 AS is_correct, 3 AS sort_order
  UNION ALL SELECT 'A type of variable' AS option_text, 0 AS is_correct, 4 AS sort_order
) v
WHERE l.course_id = @java AND l.title = 'Classes and Objects' AND qu.sort_order = 4
  AND q.quiz_id IN (SELECT quiz_id FROM tmp_empty_java_quizzes);

DROP TEMPORARY TABLE IF EXISTS tmp_empty_java_quizzes;

-- Quick check: every Java 101 lesson should have content, a game, and a quiz with questions.
SELECT l.sort_order, l.title,
       (l.content IS NOT NULL AND l.content <> '') AS has_content,
       l.game_type,
       (SELECT COUNT(*) FROM questions x JOIN quizzes q ON q.quiz_id = x.quiz_id WHERE q.lesson_id = l.lesson_id) AS questions
FROM lessons l
WHERE l.course_id = @java
ORDER BY l.sort_order;
