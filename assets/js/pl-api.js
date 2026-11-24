/**
 * API seam for the student, parent and admin pages (weeks 5-7: PLACEHOLDERS).
 *
 * Every call the pages make to the server goes through PL.api, and nothing else
 * in the frontend talks to the backend. Right now each method returns sample
 * data from the pl-sample-*.js files, so the pages work without the API.
 * Who is logged in is read from PL.viewer ({ user_id, role, first_name }), which each
 * page sets from the PHP session. Only the placeholders use it; the real server
 * always takes the user from the session.
 *
 * TO CONNECT THE REAL API, for each method below:
 *   1. replace the body with the code in its "TODO(API)" comment,
 *   2. set `placeholder` to false (this hides the "Sample data" badge and makes
 *      the pages treat saves as real),
 *   3. when all methods are done, delete pl-sample-data.js and its <script> tags.
 *
 * Contract (request and response shapes, error codes): api/README.md
 *
 * Dev switch while still on placeholders: add ?demo=... to a page URL to try
 * the unhappy paths.
 *     ?demo=empty      quiz with no questions
 *     ?demo=fail-load  loading fails
 *     ?demo=fail-save  saving a score fails
 *     ?demo=fail-feedback  loading messages fails (progress page)
 *     (progress page also: ?demo=empty = a brand new student with no activity)
 *     Parent/admin pages: ?demo=empty (no children / no students), fail-load, fail-feedback,
 *     fail-save (sending a message, adding/removing a person or link fails)
 */
(function (global, $) {
    'use strict';

    const PL = (global.PL = global.PL || {});

    const demoFromUrl = new URLSearchParams(global.location.search).get('demo') || '';

    const clone = (value) => JSON.parse(JSON.stringify(value));
    const later = (value, ms) => new Promise((resolve) => setTimeout(() => resolve(value), ms === undefined ? 250 : ms));
    const failLater = (message) => new Promise((_, reject) => setTimeout(() => reject(new Error(message)), 250));

    /**
     * Ready for use when wiring the real endpoints (unused by the placeholders).
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
    void request; // referenced here only so linters don't flag it while unused

    PL.api = {
        /** true while the methods below still return sample data. */
        placeholder: false,

        /** Current ?demo= value; the placeholders read it on every call. */
        demoMode: demoFromUrl,

        /**
         * Load one quiz with its questions and options.
         * Resolves with the quiz object:
         *   { quiz_id, title, lesson_id, lesson_title, course_id,
         *     questions: [{ question_id, question_text, code_snippet, explanation,
         *                   sort_order, options: [{ option_id, option_text, is_correct }] }] }
         */
        
getQuiz: function (quizId) {
return fetch('/play-learn/api/get_quiz.php?quiz_id=' + encodeURIComponent(quizId), {        credentials: 'same-origin'
    })
    .then(function (response) {
        if (!response.ok) {
            throw new Error('Unable to load quiz');
        }
        return response.json();
    })
    .then(function (data) {
        if (!data.ok || !data.quiz) {
            throw new Error(data.error || 'Quiz not found');
        }
        return data.quiz;
    });
},


        /**
         * Save a finished quiz attempt. The server grades the answers itself and
         * returns the authoritative score.
         *
         *   payload = { quizId, timeSpentSeconds, answers: [{ question_id, option_id }] }
         *   (skipped questions are simply left out of `answers`)
         *
         * Resolves with { ok, attempt_id, score, correct_count, total_questions, results }.
         */
        
saveQuizScore: function (payload) {
    return fetch('/play-learn/api/save_quiz_score.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
    })
    .then(async function (response) {
        const data = await response.json();

        if (!response.ok || !data.ok) {
            throw new Error(data.error || 'Unable to save quiz score');
        }

        return data;
    });
},

        /**
         * Load the data for one game.
         *   gameType: 'match_output' | 'memory'
         *   lessonId: the lesson the game belongs to (0 while in sample mode)
         *
         * Resolves with { title, context, instructions, pairs: [...] }; the shape of
         * `pairs` depends on the game (see FRONTEND_WEEK5.md, "Game data shapes").
         *
         * Note: week 5's API has NO endpoint for this yet. Either add one that reads
         * lessons.game_type / lessons.game_data, or have game.php embed that data.
         */
        getGameData: function (gameType, lessonId) {
            // TODO(API): replace this body once game data is available, e.g.
            //   return request('GET', '/api/get_game.php', { lesson_id: lessonId });
            void lessonId;
            if (this.demoMode === 'fail-load') {
                return failLater('Could not reach the server.');
            }
            const sample = PL.sample.games[gameType];
            return sample ? later(clone(sample)) : failLater('Unknown game type.');
        },

        /**
         * Save a finished game.
         *   payload = { lessonId, score, maxScore, timeSpentSeconds }
         * Resolves with { ok, game_attempt_id, score, max_score }.
         */
        saveGameScore: function (payload) {
            // TODO(API): replace this body with
            //   return request('POST', '/api/save_game_score.php', {
            //       lesson_id: payload.lessonId,
            //       score: payload.score,
            //       max_score: payload.maxScore,
            //       time_spent_seconds: payload.timeSpentSeconds,
            //   });
            void payload;
            if (this.demoMode === 'fail-save') {
                return failLater('Could not reach the server.');
            }
            return later({ ok: true, placeholder: true });
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
            // TODO(API): replace this body with
            //   return request('GET', '/api/get_progress.php', studentId ? { student_id: studentId } : undefined);
            if (this.demoMode === 'fail-load') {
                return failLater('Could not reach the server.');
            }
            if (studentId !== undefined && studentId !== null) {
                return attempt(() => PL.sample.server.progress(studentId));
            }
            const data = clone(PL.sample.progress);
            if (this.demoMode === 'empty') {
                data.totals = { lessons_completed: 0, total_lessons: 12, quizzes_taken: 0, quiz_average: null,
                                games_played: 0, time_spent_seconds: 0 };
                data.quiz_scores = [];
                data.daily_activity = [];
                data.game_scores = [];
                data.courses.forEach((c) => { c.completed_lessons = 0; });
            }
            return later(data);
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
            // TODO(API): replace this body with
            //   return request('GET', '/api/get_feedback.php', studentId ? { student_id: studentId } : undefined);
            if (this.demoMode === 'fail-feedback') {
                return failLater('Could not reach the server.');
            }
            if (studentId !== undefined && studentId !== null) {
                return attempt(() => PL.sample.server.feedback(studentId));
            }
            return later({ messages: this.demoMode === 'empty' ? [] : clone(PL.sample.feedback) });
        },

        /** Mark one message read (student pages). Resolves with { ok: true }. */
        markFeedbackRead: function (messageId) {
            // TODO(API): replace this body with
            //   return request('POST', '/api/mark_feedback_read.php', { message_id: messageId });
            void messageId;
            if (this.demoMode === 'fail-save') {
                return failLater('Could not reach the server.');
            }
            return later({ ok: true });
        },

        /**
         * Send a message to a student. The sender is always the logged-in user (never
         * sent by the browser). Admin: any student. Parent: only their own children (403
         * otherwise). Body: 1 to 2000 characters.
         * Resolves with { message: { message_id, sender_name, sender_role, body, created_at,
         *                            read_at: null, is_mine: true } }
         */
        sendFeedback: function (payload) {
            // TODO(API): replace this body with
            //   return request('POST', '/api/send_feedback.php', { student_id: payload.studentId, body: payload.body });
            if (this.demoMode === 'fail-save') {
                return failLater('Could not reach the server.');
            }
            return attempt(() => PL.sample.server.send(payload.studentId, payload.body));
        },

        /**
         * PARENT: the logged-in parent's linked children, each with a short summary.
         * Resolves with { children: [{ student_id, first_name, last_name,
         *   lessons_completed, total_lessons, quiz_average|null, games_played, last_active_at|null }] }
         */
        getChildren: function () {
            // TODO(API): replace this body with
            //   return request('GET', '/api/get_children.php');
            if (this.demoMode === 'fail-load') {
                return failLater('Could not reach the server.');
            }
            if (this.demoMode === 'empty') {
                return later({ children: [] });
            }
            return attempt(() => PL.sample.server.children());
        },

        /**
         * ADMIN: every enrolled student with summary stats and their parents.
         * Resolves with { students: [{ student_id, first_name, last_name, email,
         *   lessons_completed, total_lessons, quiz_average|null, games_played, time_spent_seconds,
         *   last_active_at|null, parents: [{ user_id, name }] }] }
         */
        getStudentsOverview: function () {
            // TODO(API): replace this body with
            //   return request('GET', '/api/admin/get_students_overview.php');
            if (this.demoMode === 'fail-load') {
                return failLater('Could not reach the server.');
            }
            if (this.demoMode === 'empty') {
                return later({ students: [] });
            }
            return attempt(() => PL.sample.server.overview());
        },

        /**
         * ADMIN: all students or all parents, each with who they are linked to.
         * Resolves with { users: [{ user_id, role, first_name, last_name, email, created_at,
         *                           links: [{ user_id, name }] }] }
         * (a parent's links are their children; a student's links are their parents)
         */
        getUsers: function (role) {
            // TODO(API): replace this body with
            //   return request('GET', '/api/admin/get_users.php', { role: role });
            if (this.demoMode === 'fail-load') {
                return failLater('Could not reach the server.');
            }
            if (this.demoMode === 'empty') {
                return later({ users: [] });
            }
            return attempt(() => PL.sample.server.users(role));
        },

        /**
         * ADMIN: create a student or parent account. The server hashes the password
         * (password_hash) and rejects a duplicate email with 409.
         * payload: { role:'student'|'parent', first_name, last_name, email, password }
         * Resolves with { user: { ...same shape as getUsers rows, links: [] } }
         */
        createUser: function (payload) {
            // TODO(API): replace this body with
            //   return request('POST', '/api/admin/create_user.php', payload);
            if (this.demoMode === 'fail-save') {
                return failLater('Could not reach the server.');
            }
            return attempt(() => PL.sample.server.createUser(payload));
        },

        /**
         * ADMIN: remove a student or parent (never an admin). Also removes their links, and for
         * a student their scores and progress; for a parent the messages they sent.
         * Resolves with { ok: true }.
         */
        deleteUser: function (userId) {
            // TODO(API): replace this body with
            //   return request('POST', '/api/admin/delete_user.php', { user_id: userId });
            if (this.demoMode === 'fail-save') {
                return failLater('Could not reach the server.');
            }
            return attempt(() => PL.sample.server.deleteUser(userId));
        },

        /** ADMIN: link a parent to a student (a row in parent_students). Resolves with { ok: true }. */
        addLink: function (parentId, studentId) {
            // TODO(API): replace this body with
            //   return request('POST', '/api/admin/add_link.php', { parent_id: parentId, student_id: studentId });
            if (this.demoMode === 'fail-save') {
                return failLater('Could not reach the server.');
            }
            return attempt(() => PL.sample.server.addLink(parentId, studentId));
        },

        /** ADMIN: remove one parent/child link. Resolves with { ok: true }. */
        removeLink: function (parentId, studentId) {
            // TODO(API): replace this body with
            //   return request('POST', '/api/admin/remove_link.php', { parent_id: parentId, student_id: studentId });
            if (this.demoMode === 'fail-save') {
                return failLater('Could not reach the server.');
            }
            return attempt(() => PL.sample.server.removeLink(parentId, studentId));
        },
    };

    /** Runs a sample "server" function and turns a thrown Error into a rejected promise. */
    function attempt(fn) {
        try {
            return later(fn());
        } catch (e) {
            return failLater(e.message);
        }
    }
})(window, window.jQuery);
