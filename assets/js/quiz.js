/**
 * Quiz page: one question at a time with instant feedback, then a results screen.
 *
 * Talks to the server only through PL.api (pl-api.js):
 *   PL.api.getQuiz(quizId)         when the page opens
 *   PL.api.saveQuizScore(payload)  once, when the last question is done
 *
 * Markup shell: student/quiz.php. Styles: assets/css/play.css.
 */
$(function () {
    'use strict';

    const PL = window.PL;
    const el = PL.util.el;

    const $app = $('#quiz-app');
    if (!$app.length) {
        return;
    }

    const quizId = parseInt($app.data('quizId'), 10);
    const backUrl = String($app.data('backUrl') || '/student/quizzes.php');

    const $title = $('#quiz-title');
    const $count = $('#quiz-count');
    const $progress = $('#quiz-progress');
    const $stage = $('#quiz-stage');
    const $sample = $('#quiz-sample');

    let quiz = null;
    // state.view: 'question' | 'results'
    // state.answers[i]: null (not reached) or { question_id, option_id, is_correct, skipped }
    let state = null;

    if (PL.api.placeholder) {
        $sample.prop('hidden', false);
    }

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
        $parent.append(el('a', 'btn btn-outline-secondary', 'Back to quizzes').attr('href', backUrl));
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
            is_correct: chosen.is_correct === true,
            skipped: false,
        };

        // Reveal the result: mark the chosen option, and always show the right one.
        $stage.find('.pl-option').each(function () {
            const $button = $(this);
            const id = parseInt($button.attr('data-option-id'), 10);
            const option = question.options.find(function (o) {
                return o.option_id === id;
            });
            $button.prop('disabled', true);
            if (option.is_correct) {
                $button.addClass('is-correct').append(el('span', 'pl-sr-only', ' (correct answer)'));
            } else if (id === chosen.option_id) {
                $button.addClass('is-wrong').append(el('span', 'pl-sr-only', ' (your answer, incorrect)'));
            } else {
                $button.addClass('is-dim');
            }
        });

        const correctOption = question.options.find(function (o) {
            return o.is_correct;
        });
        let message;
        if (chosen.is_correct) {
            message = 'Correct. ' + (question.explanation || '');
        } else {
            message =
                'Not quite.' +
                (correctOption ? ' The answer is “' + correctOption.option_text + '”.' : '') +
                (question.explanation ? ' ' + question.explanation : '');
        }
        $('#quiz-feedback')
            .text(message.trim())
            .removeClass('is-correct is-wrong')
            .addClass(chosen.is_correct ? 'is-correct' : 'is-wrong');

        $('#quiz-skip').prop('hidden', true);
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
        const total = quiz.questions.length;
        let correct = 0;
        let skipped = 0;
        state.answers.forEach(function (a) {
            if (a && a.skipped) {
                skipped += 1;
            } else if (a && a.is_correct) {
                correct += 1;
            }
        });
        return {
            correct: correct,
            total: total,
            skipped: skipped,
            percent: total > 0 ? Math.round((correct / total) * 100) : 0,
        };
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
        const $message = el('p', 'pl-result-msg', encouragement(summary.percent));
        const $save = el('p', 'pl-save-status').attr({ id: 'quiz-save-status', role: 'status', 'aria-live': 'polite' });
        const $retake = el('button', 'btn btn-pl-primary', 'Retake quiz').attr('type', 'button').on('click', startAttempt);
        const $actions = el('div', 'pl-result-actions').append($retake);
        showBackLink($actions);

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
        const parts = [summary.correct + ' of ' + summary.total + ' correct'];
        if (summary.skipped > 0) {
            parts.push(summary.skipped + ' skipped');
        }
        parts.push(PL.util.formatDuration(state.seconds));
        $('#quiz-result-score').text(summary.percent + '%');
        $('#quiz-result-sub').text(parts.join(' · '));
    }

    function buildReview() {
        const $list = el('ol', 'pl-review');
        quiz.questions.forEach(function (question, i) {
            const record = state.answers[i];
            const correctOption = question.options.find(function (o) {
                return o.is_correct;
            });
            const chosen = record && record.option_id !== null
                ? question.options.find(function (o) {
                      return o.option_id === record.option_id;
                  })
                : null;

            let chipClass = 'pl-chip pl-chip--skipped';
            let chipText = 'Skipped';
            if (chosen) {
                chipClass = 'pl-chip ' + (record.is_correct ? 'pl-chip--correct' : 'pl-chip--wrong');
                chipText = record.is_correct ? 'Correct' : 'Incorrect';
            }

            const $item = el('li', 'pl-review-item');
            $item.append(el('span', chipClass, chipText), el('p', 'pl-review-q', question.question_text));
            if (chosen && !record.is_correct) {
                $item.append(el('p', 'pl-review-line', 'Your answer: ').append(el('code', '', chosen.option_text)));
            }
            if (correctOption && !(chosen && record.is_correct)) {
                $item.append(el('p', 'pl-review-line', 'Correct answer: ').append(el('code', '', correctOption.option_text)));
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
                if (response && response.placeholder) {
                    setSaveStatus('Sample mode: this score was not saved.');
                    return;
                }
                // The server grades the quiz itself, so show its numbers if it sent them.
                if (response && typeof response.score === 'number') {
                    paintSummary({
                        correct: response.correct_count,
                        total: response.total_questions,
                        skipped: Math.max(0, response.total_questions - response.correct_count - countWrong()),
                        percent: Math.round(response.score),
                    });
                }
                setSaveStatus('Score saved.');
            })
            .catch(function (error) {
                console.error(error);
                if (state.view === 'results') {
                    setSaveStatus("We couldn't save your score.", 'error', save);
                }
            });
    }

    function countWrong() {
        return state.answers.filter(function (a) {
            return a && !a.skipped && !a.is_correct;
        }).length;
    }

    load();
});
