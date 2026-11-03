/**
 * Sample data for the week 6 Progress page.
 *
 * TEMPORARY, like pl-sample-data.js: used only by the placeholder methods in
 * pl-api.js. Delete this file and its <script> tag when getProgress, getFeedback
 * and markFeedbackRead are connected. Dates are built relative to today so the
 * charts always look current.
 */
(function (global) {
    'use strict';

    const PL = (global.PL = global.PL || {});

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function daysAgo(n, hour) {
        const d = new Date();
        d.setDate(d.getDate() - n);
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(hour || 16) + ':00:00';
    }

    PL.sample = PL.sample || {};

    PL.sample.progress = {
        totals: { lessons_completed: 5, total_lessons: 12, quizzes_taken: 6, quiz_average: 76.7, games_played: 4, time_spent_seconds: 6420 },
        quiz_scores: [
            { attempt_id: 1, quiz_title: 'Variables & Data Types', score: 50, finished_at: daysAgo(12) },
            { attempt_id: 2, quiz_title: 'Variables & Data Types', score: 75, finished_at: daysAgo(10) },
            { attempt_id: 3, quiz_title: 'Operators', score: 80, finished_at: daysAgo(8) },
            { attempt_id: 4, quiz_title: 'Conditionals', score: 70, finished_at: daysAgo(5) },
            { attempt_id: 5, quiz_title: 'Loops', score: 90, finished_at: daysAgo(3) },
            { attempt_id: 6, quiz_title: 'Loops', score: 95, finished_at: daysAgo(1) },
        ],
        daily_activity: [
            { date: daysAgo(12).slice(0, 10), lessons: 1, quizzes: 1, games: 0 },
            { date: daysAgo(10).slice(0, 10), lessons: 0, quizzes: 1, games: 1 },
            { date: daysAgo(8).slice(0, 10), lessons: 1, quizzes: 1, games: 0 },
            { date: daysAgo(5).slice(0, 10), lessons: 1, quizzes: 1, games: 1 },
            { date: daysAgo(3).slice(0, 10), lessons: 1, quizzes: 1, games: 1 },
            { date: daysAgo(1).slice(0, 10), lessons: 0, quizzes: 1, games: 1 },
        ],
        courses: [
            { course_id: 1, title: 'Python 101', completed_lessons: 5, total_lessons: 6 },
            { course_id: 2, title: 'Java 101', completed_lessons: 0, total_lessons: 6 },
        ],
        game_scores: [
            { lesson_title: 'Loops', game_label: 'Memory Match', score: 270, max_score: 300, completed_at: daysAgo(1) },
            { lesson_title: 'Conditionals', game_label: 'Match the Output', score: 150, max_score: 200, completed_at: daysAgo(3) },
            { lesson_title: 'Operators', game_label: 'Memory Match', score: 210, max_score: 300, completed_at: daysAgo(5) },
            { lesson_title: 'Variables', game_label: 'Match the Output', score: 100, max_score: 200, completed_at: daysAgo(10) },
        ],
    };

    PL.sample.feedback = [
        { message_id: 1, sender_name: 'Ms. Rivera', sender_role: 'admin',
          body: 'Great jump on the Loops quiz! Next, try the Conditionals game again to lock in if/else.', created_at: daysAgo(1, 9), read_at: null },
        { message_id: 2, sender_name: 'Alex (parent)', sender_role: 'parent',
          body: 'Proud of the steady practice this week. Keep it up!', created_at: daysAgo(4, 18), read_at: daysAgo(4, 19) },
    ];
})(window);
