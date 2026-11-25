/**
 * Quiz page: one question at a time with instant feedback, then a results screen.
 *
 * Talks to the server only through PL.api (pl-api.js):
 *   PL.api.getQuiz(quizId)         when the page opens
 *   PL.api.saveQuizScore(payload)  once, when the last question is done
 *
 * Lesson flow: passing (60% by default, set in includes/progress.php) completes the lesson and
 * offers the next one; failing offers the lesson to re-read and a retake. A quiz that was
 * already passed runs as practice: it is graded but the server saves nothing.
 *
 * Markup shell: student/quiz.php. Styles: assets/css/play.css.
 */
$(function () {
    'use strict';

    const global = window;
    const PL = window.PL;
    const el = PL.util.el;

    const $app = $('#quiz-app');
    if (!$app.length) {
        return;
    }

    const quizId = parseInt($app.data('quizId'), 10);
    const backUrl = String($app.data('backUrl') || '/student/quizzes.php');
    const lessonUrl = String($app.data('lessonUrl') || '');
    const syllabusUrl = String($app.data('syllabusUrl') || backUrl);
    const nextUrl = String($app.data('nextUrl') || '');
    const nextLabel = String($app.data('nextLabel') || 'Next lesson');
    let alreadyPassed = String($app.attr('data-passed')) === '1'; // true: this run is practice

    const $title = $('#quiz-title');
    const $count = $('#quiz-count');
    const $progress = $('#quiz-progress');
    const $stage = $('#quiz-stage');
    const $practice = $('#quiz-practice');

    let quiz = null;
    // state.view: 'question' | 'results'
    // state.answers[i]: null (not reached) or { question_id, option_id, is_correct, skipped }
    let state = null;

    // ---- Small view helpers -------------------------------------------------

    function setProgress(done, total) {
        const percent = total > 0 ? Math.round((done / total) * 100) : 0;
        $progress.attr('aria-valuenow', percent).find('.pl-progress-bar').css('width', percent + '%');
    }

    function showMessage(text, buttonLabel, onClick) {
        $stage.empty();
        const $box = el('div', 'pl-panel pl-state');
        $box.append(el('p', '', text));
        if (buttonLabel) {
            $box.append(el('button', 'btn btn-pl-primary', buttonLabel).attr('type', 'button').on('click', onClick));
        }
        $stage.append($box);
    }

    function showBackLink($parent) {
        $parent.append(el('a', 'btn btn-outline-secondary', syllabusUrl === backUrl ? 'Back to quizzes' : 'Back to syllabus').attr('href', syllabusUrl));
    }

    // ---- Loading ------------------------------------------------------------

    function load() {
        $count.text('');
        setProgress(0, 1);
        showMessage('Loading quiz…');

        PL.api
            .getQuiz(quizId)
            .then(function (loaded) {
                quiz = loaded;
                $title.text(quiz.title);
                $practice.prop('hidden', !alreadyPassed);
                document.title = quiz.title + ' · Play & Learn';

                if (!quiz.questions || quiz.questions.length === 0) {
                    showEmpty();
                } else {
                    startAttempt();
                }
            })
            .catch(function (error) {
                console.error(error);
                $title.text('Quiz');
                if (/unlock/i.test(error.message || '')) {
                    showMessage(error.message || 'This quiz is locked.', 'Back to syllabus', function () {
                        global.location.href = syllabusUrl;
                    });
                    return;
                }
                showMessage("We couldn't load this quiz. Check your connection and try again.", 'Try again', load);
            });
    }

    function showEmpty() {
        $count.text('');
        setProgress(0, 1);
        $stage.empty();
        const $box = el('div', 'pl-panel pl-state');
        $box.append(el('p', '', "This quiz doesn't have any questions yet. Check back soon."));
        showBackLink($box);
        $stage.append($box);
    }

    // ---- Taking the quiz ----------------------------------------------------

    function startAttempt() {
        state = {
            view: 'question',
            index: 0,
            locked: false,
            startedAt: Date.now(),
            answers: quiz.questions.map(function () {
                return null;
            }),
        };
        renderQuestion();
    }

    function renderQuestion() {
        const total = quiz.questions.length;
        const question = quiz.questions[state.index];
        const isLast = state.index === total - 1;

        state.view = 'question';
        state.locked = false;
        $count.text('Question ' + (state.index + 1) + ' of ' + total);
        setProgress(state.index + 1, total);

        const $panel = el('section', 'pl-panel').attr({ 'aria-labelledby': 'quiz-question-label', tabindex: '-1' });
        $panel.append(el('p', 'pl-eyebrow', 'Question').attr('id', 'quiz-question-label'));

        if (question.code_snippet) {
            $panel.append(el('pre', 'pl-code-block').append(el('code', '', question.code_snippet)));
        }
        $panel.append(el('p', 'pl-question-text', question.question_text).attr('id', 'quiz-question-text'));

        const $options = el('div', 'pl-options').attr({ role: 'group', 'aria-labelledby': 'quiz-question-text' });
        question.options.forEach(function (option) {
            const $button = el('button', 'pl-option').attr({ type: 'button', 'data-option-id': option.option_id });
            $button.append(el('span', 'pl-radio').attr('aria-hidden', 'true'), el('span', 'pl-option-text', option.option_text));
            $options.append($button);
        });
        $panel.append($options);

        const $feedback = el('p', 'pl-feedback', 'Pick an answer, or skip for now.').attr({
            id: 'quiz-feedback',
            role: 'status',
            'aria-live': 'polite',
        });
        const $skip = el('button', 'btn btn-outline-secondary', 'Skip').attr({ type: 'button', id: 'quiz-skip' });
        const $next = el('button', 'btn btn-pl-primary', isLast ? 'Finish quiz' : 'Next question')
            .attr({ type: 'button', id: 'quiz-next' })
            .prop('hidden', true);
        const $bar = el('div', 'pl-actionbar').append($feedback, el('div', 'pl-actions').append($skip, $next));

        $stage.empty().append($panel, $bar);
        $panel.trigger('focus');

        $options.on('click', '.pl-option', function () {
            answer(parseInt($(this).attr('data-option-id'), 10));
        });
        $skip.on('click', skip);
        $next.on('click', next);
    }

    function answer(optionId) {
        if (state.view !== 'question' || state.locked) {
            return;
        }
        const question = quiz.questions[state.index];
        const chosen = question.options.find(function (o) {
            return o.option_id === optionId;
        });
        if (!chosen) {
            return;
        }
        state.locked = true;
       state.answers[state.index] = {
    question_id: question.question_id,
    option_id: chosen.option_id,
    is_correct: null,
    skipped: false
};

        // Record the selection without revealing the correct answer.
$stage.find('.pl-option').each(function () {
    const $button = $(this);
    const id = parseInt($button.attr('data-option-id'), 10);

    $button.prop('disabled', true);

    if (id === chosen.option_id) {
        $button.addClass('is-selected');
    } else {
        $button.addClass('is-dim');
    }
});

$('#quiz-feedback')
    .text('Answer recorded. Your results will appear after submission.')
    .removeClass('is-correct is-wrong');
        $('#quiz-next').prop('hidden', false).trigger('focus');
    }

    function skip() {
        if (state.view !== 'question' || state.locked) {
            return;
        }
        state.answers[state.index] = {
            question_id: quiz.questions[state.index].question_id,
            option_id: null,
            is_correct: null,
            skipped: true,
        };
        next();
    }

    function next() {
        if (state.view !== 'question') {
            return;
        }
        if (state.index < quiz.questions.length - 1) {
            state.index += 1;
            renderQuestion();
        } else {
            finish();
        }
    }

    // Number keys pick an option; Enter/Space on the focused button works natively.
    $(document).on('keydown', function (event) {
        if (!state || state.view !== 'question' || state.locked) {
            return;
        }
        if (event.ctrlKey || event.metaKey || event.altKey || $(event.target).is('input, textarea, select')) {
            return;
        }
        const number = parseInt(event.key, 10);
        const options = quiz.questions[state.index].options;
        if (number >= 1 && number <= options.length) {
            event.preventDefault();
            answer(options[number - 1].option_id);
        }
    });

    // ---- Results ------------------------------------------------------------

    function summarize() {
        // The browser does not know which options are correct (the server grades),
        // so this is only a provisional summary until the server responds.
        const total = quiz.questions.length;
        const skipped = state.answers.filter(function (a) {
            return a && a.skipped;
        }).length;
        return { correct: 0, total: total, skipped: skipped, percent: 0, provisional: true };
    }

    function encouragement(percent) {
        if (percent >= 90) {
            return 'Excellent work!';
        }
        if (percent >= 70) {
            return 'Nice job. You have the key ideas down.';
        }
        if (percent >= 50) {
            return "Getting there. Review the lesson and you'll nail it next time.";
        }
        return 'Keep practicing. Re-read the lesson and try again.';
    }

    function finish() {
        state.view = 'results';
        state.seconds = Math.round((Date.now() - state.startedAt) / 1000);

        const summary = summarize();
        $count.text('Finished');
        setProgress(1, 1);

        const $score = el('p', 'pl-result-score', summary.percent + '%').attr('id', 'quiz-result-score');
        const $sub = el('p', 'pl-result-sub').attr('id', 'quiz-result-sub');
        const $message = el('p', 'pl-result-msg', encouragement(summary.percent)).attr('id', 'quiz-result-msg');
        const $save = el('p', 'pl-save-status').attr({ id: 'quiz-save-status', role: 'status', 'aria-live': 'polite' });
        const $actions = el('div', 'pl-result-actions').attr('id', 'quiz-actions');

        const $results = el('section', 'pl-panel pl-results')
            .attr({ 'aria-labelledby': 'quiz-result-eyebrow', tabindex: '-1' })
            .append(el('p', 'pl-eyebrow', 'Quiz complete').attr('id', 'quiz-result-eyebrow'), $score, $sub, $message, $save, $actions);

        $stage.empty().append($results, buildReview());
        paintSummary(summary);
        $results.trigger('focus');

        save();
    }

    /** Fills in the "2 of 4 correct" line; called again if the server returns its own numbers. */
    function paintSummary(summary) {
        const parts = [summary.provisional ? 'Grading…' : summary.correct + ' of ' + summary.total + ' correct'];
        if (summary.skipped > 0) {
            parts.push(summary.skipped + ' skipped');
        }
        parts.push(PL.util.formatDuration(state.seconds));
        $('#quiz-result-score').text(summary.provisional ? '…' : summary.percent + '%');
        $('#quiz-result-sub').text(parts.join(' · '));
    }

    function buildReview() {
        const $list = el('ol', 'pl-review');
        quiz.questions.forEach(function (question, i) {
            const record = state.answers[i];
            const correctOption = question.options.find(function (o) {
                return o.is_correct === true || o.is_correct === 1 || o.is_correct === "1";
            });
            const chosen = record && record.option_id !== null
                ? question.options.find(function (o) {
                      return o.option_id === record.option_id;
                  })
                : null;

            let chipClass = 'pl-chip pl-chip--skipped';
let chipText = 'Skipped';

if (chosen) {
    chipClass = 'pl-chip';
    chipText = 'Answered';
}

            const $item = el('li', 'pl-review-item');
            $item.append(el('span', chipClass, chipText), el('p', 'pl-review-q', question.question_text));

if (chosen) {
    $item.append(
        el('p', 'pl-review-line', 'Your answer: ')
            .append(el('code', '', chosen.option_text))
    );
}

if (question.explanation) {
    $item.append(el('p', 'pl-review-line', question.explanation));
}

$list.append($item);


});

        return el('section', 'pl-panel').append(el('h2', 'pl-eyebrow pl-eyebrow--muted', 'Review'), $list);
    }

    // ---- Saving the score ---------------------------------------------------

    function setSaveStatus(text, tone, retry) {
        const $status = $('#quiz-save-status').removeClass('is-error').text(text);
        if (tone === 'error') {
            $status.addClass('is-error');
        }
        if (retry) {
            $status.append(el('button', 'btn btn-sm btn-outline-secondary', 'Try again').attr('type', 'button').on('click', retry));
        }
    }

    function save() {
        const payload = {
            quizId: quizId,
            timeSpentSeconds: state.seconds,
            // Skipped questions are left out; the server counts them as not correct.
            answers: state.answers
                .filter(function (a) {
                    return a && !a.skipped;
                })
                .map(function (a) {
                    return { question_id: a.question_id, option_id: a.option_id };
                }),
        };

        setSaveStatus('Saving your score…');
        PL.api
            .saveQuizScore(payload)
            .then(function (response) {
                if (state.view !== 'results') {
                    return; // the student already started a retake
                }
                // The server grades the quiz itself, so show its numbers.
                const percent = Math.round(response.score);
                const skipped = state.answers.filter(function (a) {
                    return a && a.skipped;
                }).length;
                paintSummary({
                    correct: response.correct_count,
                    total: response.total_questions,
                    skipped: skipped,
                    percent: percent,
                });
                applyResults(response.results);
                paintOutcome(response, percent);

                setSaveStatus(response.practice ? 'Practice run: nothing was saved, so your progress is unchanged.' : 'Score saved.');
            })
 .catch(function (error) {
                console.error(error);
                if (state.view === 'results') {
                    setSaveStatus("We couldn't save your score.", 'error', save);
                    paintActions(null);
                }
            });
    }

    /** Pass / fail message and the buttons that go with it. */
    function paintOutcome(response, percent) {
        const need = response.pass_percent;
        let message;
        if (response.practice) {
            message = 'Nice practice run. This lesson is already complete.';
        } else if (response.passed) {
            message = response.lesson_completed
                ? 'You passed, and this lesson is complete. ' + encouragement(percent)
                : 'You passed. ' + encouragement(percent);
        } else {
            message = 'You need ' + need + '% to move on. Re-read the lesson, then try again.';
        }
        $('#quiz-result-eyebrow').text(response.practice ? 'Practice complete' : response.passed ? 'Quiz passed' : 'Not quite yet');
        $('#quiz-result-msg').text(message);
        if (response.passed && !response.practice) {
            alreadyPassed = true; // a retake from here is practice
        }
        paintActions(response);
    }

    function paintActions(response) {
        const $actions = $('#quiz-actions').empty();
        const passed = response && response.passed;
        if (response && !passed && lessonUrl) {
            $actions.append(el('a', 'btn btn-outline-secondary', 'Review the lesson').attr('href', lessonUrl));
        }
        if (passed && !response.practice) {
            $actions.append(el('a', 'btn btn-pl-primary', nextLabel).attr('href', nextUrl || syllabusUrl));
        }
        if (response) {
            $actions.append(
                el('button', passed && !response.practice ? 'btn btn-outline-secondary' : 'btn btn-pl-primary',
                   response.practice || passed ? 'Practice again' : 'Retake quiz')
                    .attr('type', 'button')
                    .on('click', function () {
                        $practice.prop('hidden', !alreadyPassed);
                        startAttempt();
                    })
            );
        }
        showBackLink($actions);
    }

    /** After saving: mark each review item Correct / Incorrect and show the right answer (server results). */
    function applyResults(results) {
        if (!Array.isArray(results)) {
            return;
        }
        results.forEach(function (r) {
            const index = quiz.questions.findIndex(function (qu) {
                return qu.question_id === r.question_id;
            });
            if (index < 0 || r.selected_option_id === null || r.selected_option_id === undefined) {
                return; // unknown question, or skipped: keep the "Skipped" chip
            }
            const $item = $('#quiz-stage .pl-review-item').eq(index);
            $item.find('.pl-chip').first().text(r.is_correct ? 'Correct' : 'Incorrect');
            if (!r.is_correct) {
                const right = quiz.questions[index].options.find(function (o) {
                    return o.option_id === r.correct_option_id;
                });
                if (right) {
                    $item.find('.pl-review-line').first().after(
                        el('p', 'pl-review-line', 'Correct answer: ').append(el('code', '', right.option_text))
                    );
                }
            }
        });
    }

    load();
});
