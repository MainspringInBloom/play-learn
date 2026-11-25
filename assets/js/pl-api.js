/**
 * API seam for the student, parent and admin pages.
 *
 * Every call the pages make to the server goes through PL.api, and nothing else
 * in the frontend talks to the backend. Who is logged in always comes from the
 * PHP session on the server; the browser never sends its own user id.
 *
 * Contract (request and response shapes, error codes): the table in README.md
 * and the comment above each method.
 */
(function (global, $) {
    'use strict';

    const PL = (global.PL = global.PL || {});

    /**
     * Wraps $.ajax in a native Promise and turns a failed request into an Error
     * carrying the server's {"ok": false, "error": "..."} message.
     *
     *   request('GET',  '/api/get_quiz.php', { quiz_id: 1 })
     *   request('POST', '/api/save_game_score.php', { lesson_id: 1, ... })
     */
    function request(method, url, payload) {
        const options = { url: url, method: method, dataType: 'json' };
        if (method === 'GET') {
            options.data = payload;
        } else {
            options.contentType = 'application/json';
            options.data = JSON.stringify(payload);
        }
        return new Promise((resolve, reject) => {
            $.ajax(options)
                .done(resolve)
                .fail((xhr) => {
                    const message = xhr.responseJSON && xhr.responseJSON.error;
                    reject(new Error(message || 'Network or server error.'));
                });
        });
    }

    PL.api = {
        /**
         * Load one quiz with its questions and options.
         * Resolves with the quiz object:
         *   { quiz_id, title, lesson_id, lesson_title, course_id,
         *     questions: [{ question_id, question_text, code_snippet, explanation,
         *                   sort_order, options: [{ option_id, option_text, is_correct }] }] }
         */
        
getQuiz: function (quizId) {
            // Root-relative path: works when the site is served from the project root
            // (php -S localhost:8000). The server never sends is_correct to the browser.
            return request('GET', '/api/get_quiz.php', { quiz_id: quizId }).then(function (res) {
                if (!res || !res.ok || !res.quiz) {
                    throw new Error((res && res.error) || 'Quiz not found');
                }
                return res.quiz;
            });
        },

        /**
         * Save a finished quiz attempt. The server grades the answers itself and
         * returns the authoritative score.
         *
         *   payload = { quizId, timeSpentSeconds, answers: [{ question_id, option_id }] }
         *   (skipped questions are simply left out of `answers`)
         *
         * Resolves with { ok, attempt_id, score, correct_count, total_questions,
         *   results: [{ question_id, selected_option_id, correct_option_id, is_correct }] }.
         */
        saveQuizScore: function (payload) {
            return request('POST', '/api/save_quiz_score.php', payload).then(function (res) {
                if (!res || !res.ok) {
                    throw new Error((res && res.error) || 'Unable to save quiz score');
                }
                return res;
            });
        },

        /**
         * Load the data for one game.
         *   gameType: 'match_output' | 'memory'
         *   lessonId: the lesson the game belongs to 
         *
         * Resolves with { title, context, instructions, pairs: [...] }; the shape of
         * `pairs` depends on the game (see FRONTEND_WEEK5.md, "Game data shapes").
         *
         * Served by api/get_game.php, which reads lessons.game_type / lessons.game_data.
         */
        getGameData: function (gameType, lessonId) {
            return request('GET', '/api/get_game.php', { lesson_id: lessonId, type: gameType }).then(function (res) {
                if (!res || !res.ok || !res.game) {
                    throw new Error((res && res.error) || 'Game not found');
                }
                return res.game;
            });
        },

        /**
         * Save a finished game.
         *   payload = { lessonId, score, maxScore, timeSpentSeconds }
         * Resolves with { ok, game_attempt_id, score, max_score }.
         */
        saveGameScore: function (payload) {
            return request('POST', '/api/save_game_score.php', {
                lesson_id: payload.lessonId,
                score: payload.score,
                max_score: payload.maxScore,
                time_spent_seconds: payload.timeSpentSeconds,
            }).then(function (res) {
                if (!res || !res.ok) {
                    throw new Error((res && res.error) || 'Unable to save game score');
                }
                return res;
            });
        },

        /** Mark a lesson that has no quiz as complete. Resolves with { ok, completed, newly_completed }. */
        completeLesson: function (lessonId) {
            return request('POST', '/api/save_lesson_progress.php', { lesson_id: lessonId });
        },

        /**
         * Everything the Progress page charts, for ONE student.
         *   getProgress()           the logged-in student's own (student pages)
         *   getProgress(studentId)  a linked child (parent) or any student (admin)
         * Resolves with:
         *   { student:{ student_id, first_name, last_name, email },
         *     totals:{ lessons_completed, total_lessons, quizzes_taken, quiz_average|null,
         *              games_played, time_spent_seconds },
         *     quiz_scores:    [{ attempt_id, quiz_title, score, finished_at }]  oldest first, last 20
         *     daily_activity: [{ date:'YYYY-MM-DD', lessons, quizzes, games }]  last 14 days (gaps ok)
         *     courses:        [{ course_id, title, completed_lessons, total_lessons }]
         *     game_scores:    [{ lesson_title, game_label, score, max_score, completed_at }]  newest first, last 6 }
         * The server must refuse (403) a parent asking for a student who is not linked to
         * them. See FRONTEND_WEEK7.md for the rules and suggested SQL.
         */
        getProgress: function (studentId) {
            return request('GET', '/api/get_progress.php', studentId ? { student_id: studentId } : undefined);
        },

        /**
         * Messages about a student, newest first.
         *   getFeedback()           the logged-in student's own inbox (student pages)
         *   getFeedback(studentId)  the thread about that student (parent: linked child, admin: any)
         * Resolves with { messages: [{ message_id, sender_name, sender_role:'admin'|'parent',
         *                              body, created_at, read_at|null, is_mine? }] }
         * is_mine (parent/admin views) is true for messages the logged-in user wrote.
         */
        getFeedback: function (studentId) {
            return request('GET', '/api/get_feedback.php', studentId ? { student_id: studentId } : undefined);
        },

        /** Mark one message read (student pages). Resolves with { ok: true }. */
        markFeedbackRead: function (messageId) {
            return request('POST', '/api/mark_feedback_read.php', { message_id: messageId });
        },

        /**
         * Send a message to a student. The sender is always the logged-in user (never
         * sent by the browser). Admin: any student. Parent: only their own children (403
         * otherwise). Body: 1 to 2000 characters.
         * Resolves with { message: { message_id, sender_name, sender_role, body, created_at,
         *                            read_at: null, is_mine: true } }
         */
        sendFeedback: function (payload) {
            return request('POST', '/api/send_feedback.php', { student_id: payload.studentId, body: payload.body });
        },

        /**
         * PARENT: the logged-in parent's linked children, each with a short summary.
         * Resolves with { children: [{ student_id, first_name, last_name,
         *   lessons_completed, total_lessons, quiz_average|null, games_played, last_active_at|null }] }
         */
        getChildren: function () {
            return request('GET', '/api/get_children.php');
        },

        /**
         * ADMIN: every enrolled student with summary stats and their parents.
         * Resolves with { students: [{ student_id, first_name, last_name, email,
         *   lessons_completed, total_lessons, quiz_average|null, games_played, time_spent_seconds,
         *   last_active_at|null, parents: [{ user_id, name }] }] }
         */
        getStudentsOverview: function () {
            return request('GET', '/api/admin/get_students_overview.php');
        },

        /**
         * ADMIN: all students or all parents, each with who they are linked to.
         * Resolves with { users: [{ user_id, role, first_name, last_name, email, created_at,
         *                           links: [{ user_id, name }] }] }
         * (a parent's links are their children; a student's links are their parents)
         */
        getUsers: function (role) {
            return request('GET', '/api/admin/get_users.php', { role: role });
        },

        /**
         * ADMIN: create a student or parent account. The server hashes the password
         * (password_hash) and rejects a duplicate email with 409.
         * payload: { role:'student'|'parent', first_name, last_name, email, password }
         * Resolves with { user: { ...same shape as getUsers rows, links: [] } }
         */
        createUser: function (payload) {
            return request('POST', '/api/admin/create_user.php', payload);
        },

        /**
         * ADMIN: remove a student or parent (never an admin). Also removes their links, and for
         * a student their scores and progress; for a parent the messages they sent.
         * Resolves with { ok: true }.
         */
        deleteUser: function (userId) {
            return request('POST', '/api/admin/delete_user.php', { user_id: userId });
        },

        /** ADMIN: link a parent to a student (a row in parent_students). Resolves with { ok: true }. */
        addLink: function (parentId, studentId) {
            return request('POST', '/api/admin/add_link.php', { parent_id: parentId, student_id: studentId });
        },

        /** ADMIN: remove one parent/child link. Resolves with { ok: true }. */
        removeLink: function (parentId, studentId) {
            return request('POST', '/api/admin/remove_link.php', { parent_id: parentId, student_id: studentId });
        },
    };
})(window, window.jQuery);
