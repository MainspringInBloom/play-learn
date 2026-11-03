/**
 * Sample data for the week 5 quiz and game pages.
 *
 * TEMPORARY. It exists only so the pages work before the API is connected, and
 * is used only by the placeholder methods in pl-api.js. When those methods are
 * wired to the real endpoints, delete this file and its <script> tags.
 *
 * Shapes match the real thing:
 *   quiz  -> the `quiz` object returned by GET /api/get_quiz.php
 *   games -> what lessons.game_data should hold for each game_type
 *            (see FRONTEND_WEEK5.md, "Game data shapes")
 */
(function (global) {
    'use strict';

    const PL = (global.PL = global.PL || {});

    PL.sample = {
        quiz: {
            quiz_id: 1,
            title: 'Variables & Data Types',
            lesson_id: 1,
            lesson_title: 'Variables and Data Types',
            course_id: 1,
            questions: [
                {
                    question_id: 1,
                    question_text: 'What does this code print?',
                    code_snippet: 'x = 7\ny = "7"\nprint(type(x) == type(y))',
                    explanation: 'Different types are never equal.',
                    sort_order: 1,
                    options: [
                        { option_id: 1, option_text: 'True', is_correct: false },
                        { option_id: 2, option_text: 'False', is_correct: true },
                        { option_id: 3, option_text: 'TypeError', is_correct: false },
                        { option_id: 4, option_text: '7', is_correct: false },
                    ],
                },
                {
                    question_id: 2,
                    question_text: 'Which of these is a valid Python variable name?',
                    code_snippet: null,
                    explanation: 'Names can use letters, digits and underscores, but cannot start with a digit.',
                    sort_order: 2,
                    options: [
                        { option_id: 5, option_text: '2nd_place', is_correct: false },
                        { option_id: 6, option_text: 'first-name', is_correct: false },
                        { option_id: 7, option_text: 'total_score', is_correct: true },
                        { option_id: 8, option_text: 'my variable', is_correct: false },
                    ],
                },
                {
                    question_id: 3,
                    question_text: 'What type does Python report for age?',
                    code_snippet: 'age = 12\nprint(type(age))',
                    explanation: 'A whole number written without quotes is an int.',
                    sort_order: 3,
                    options: [
                        { option_id: 9, option_text: "<class 'int'>", is_correct: true },
                        { option_id: 10, option_text: "<class 'str'>", is_correct: false },
                        { option_id: 11, option_text: "<class 'float'>", is_correct: false },
                        { option_id: 12, option_text: "<class 'bool'>", is_correct: false },
                    ],
                },
                {
                    question_id: 4,
                    question_text: 'What does this code print?',
                    code_snippet: 'name = "Sam"\nprint(name + "!")',
                    explanation: 'The + operator joins two strings together.',
                    sort_order: 4,
                    options: [
                        { option_id: 13, option_text: 'Sam!', is_correct: true },
                        { option_id: 14, option_text: 'name!', is_correct: false },
                        { option_id: 15, option_text: '"Sam"!', is_correct: false },
                        { option_id: 16, option_text: 'TypeError', is_correct: false },
                    ],
                },
            ],
        },

        games: {
            match_output: {
                title: 'Match the Output',
                context: 'Python 101 · Loops & Conditionals',
                instructions: 'Pick a code card, then pick the output it prints.',
                pairs: [
                    { code: 'for i in range(3):\n    print(i)', output: '0\n1\n2' },
                    { code: 'x = 5\nif x > 3:\n    print("big")', output: 'big' },
                    { code: 'for c in "hi":\n    print(c)', output: 'h\ni' },
                    { code: 'n = 0\nwhile n < 2:\n    print(n)\n    n += 1', output: '0\n1' },
                ],
            },

            memory: {
                title: 'Memory Match',
                context: 'Python 101 · Variables & Data Types',
                instructions: 'Flip two cards to find each term and its meaning.',
                pairs: [
                    { a: 'int', b: 'A whole number' },
                    { a: 'str', b: 'Text in quotes' },
                    { a: 'float', b: 'A number with a decimal point' },
                    { a: 'bool', b: 'True or False' },
                    { a: 'list', b: 'An ordered collection of items' },
                    { a: 'len()', b: 'Counts the items in a value' },
                ],
            },
        },
    };
})(window);
