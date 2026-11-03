/**
 * API seam for the student quiz and game pages (week 5: PLACEHOLDERS).
 *
 * Every call the pages make to the server goes through PL.api, and nothing else
 * in the frontend talks to the backend. Right now each method returns sample
 * data from pl-sample-data.js, so the pages work without the API.
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
        placeholder: true,

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
            // TODO(API): replace this body with
            //   return request('GET', '/api/get_quiz.php', { quiz_id: quizId })
            //       .then((res) => res.quiz);
            if (this.demoMode === 'fail-load') {
                return failLater('Could not reach the server.');
            }
            const quiz = clone(PL.sample.quiz);
            quiz.quiz_id = quizId;
            if (this.demoMode === 'empty') {
                quiz.questions = [];
            }
            return later(quiz);
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
            // TODO(API): replace this body with
            //   return request('POST', '/api/save_quiz_score.php', {
            //       quiz_id: payload.quizId,
            //       time_spent_seconds: payload.timeSpentSeconds,
            //       answers: payload.answers,
            //   });
            void payload;
            if (this.demoMode === 'fail-save') {
                return failLater('Could not reach the server.');
            }
            return later({ ok: true, placeholder: true });
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
    };
})(window, window.jQuery);
