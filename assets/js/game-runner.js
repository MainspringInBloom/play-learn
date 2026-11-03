/**
 * Game runner: everything the matching games have in common.
 *   - loads the game's data (PL.api.getGameData)
 *   - keeps score, the matched counter, and the clock
 *   - shows the results screen and saves the score (PL.api.saveGameScore)
 *   - Reset and Finish buttons
 *
 * The games themselves live in game-match-output.js and game-memory.js. Each one
 * registers an engine in PL.games[<type>] with a single method:
 *
 *     mount($board, data, ui)
 *
 * and reports what the player does through `ui`:
 *     ui.match()   the player found a correct pair
 *     ui.miss()    the player picked a wrong pair
 *
 * Scoring: 50 points per matched pair, minus 10 for every wrong pick, never below 0.
 * Markup shell: student/game.php. Styles: assets/css/play.css.
 */
$(function () {
    'use strict';

    const PL = window.PL;
    const el = PL.util.el;

    const POINTS_PER_PAIR = 50;
    const MISTAKE_PENALTY = 10;

    const $app = $('#game-app');
    if (!$app.length) {
        return;
    }

    const gameType = String($app.data('gameType'));
    const lessonId = parseInt($app.data('lessonId'), 10) || 0;
    const backUrl = String($app.data('backUrl') || '/student/games.php');
    const engine = PL.games[gameType];

    const $title = $('#game-title');
    const $sub = $('#game-sub');
    const $score = $('#game-score');
    const $matched = $('#game-matched');
    const $stage = $('#game-stage');
    const $status = $('#game-status');
    const $actionbar = $('#game-actionbar');
    const $sample = $('#game-sample');

    let data = null;
    let session = null;
    let run = 0; // bumped on every (re)start so stale timers and clicks are ignored
    let finished = false;

    if (PL.api.placeholder) {
        $sample.prop('hidden', false);
    }

    // ---- Session: the score for one play-through ---------------------------

    function newSession(total) {
        return {
            total: total,
            matched: 0,
            mistakes: 0,
            startedAt: Date.now(),
            score: function () {
                return Math.max(0, this.matched * POINTS_PER_PAIR - this.mistakes * MISTAKE_PENALTY);
            },
            maxScore: function () {
                return this.total * POINTS_PER_PAIR;
            },
            seconds: function () {
                return Math.round((Date.now() - this.startedAt) / 1000);
            },
        };
    }

    function paintStats() {
        $score.text(session ? session.score() : 0);
        $matched.text(session ? session.matched + '/' + session.total : '0/0');
    }

    function say(text, tone) {
        $status.removeClass('is-correct is-wrong').text(text || '');
        if (tone) {
            $status.addClass('is-' + tone);
        }
    }

    // ---- Loading and starting ------------------------------------------------

    function showMessage(text, buttonLabel, onClick) {
        $actionbar.prop('hidden', true);
        $stage.empty();
        const $box = el('div', 'pl-panel pl-state').append(el('p', '', text));
        if (buttonLabel) {
            $box.append(el('button', 'btn btn-pl-primary', buttonLabel).attr('type', 'button').on('click', onClick));
        }
        $stage.append($box);
    }

    function load() {
        session = null;
        paintStats();
        showMessage('Loading game…');

        PL.api
            .getGameData(gameType, lessonId)
            .then(function (loaded) {
                data = loaded;
                $title.text(data.title);
                $sub.text([data.context, data.instructions].filter(Boolean).join('. '));
                document.title = data.title + ' · Play & Learn';

                if (!data.pairs || data.pairs.length < 2) {
                    showMessage("This game isn't ready yet. Check back soon.");
                } else {
                    begin();
                }
            })
            .catch(function (error) {
                console.error(error);
                showMessage("We couldn't load this game. Check your connection and try again.", 'Try again', load);
            });
    }

    /** Starts (or restarts) a play-through with a fresh board and a clean score. */
    function begin() {
        run += 1;
        finished = false;
        session = newSession(data.pairs.length);
        paintStats();
        say(data.instructions || '');

        const $board = el('div', 'pl-board');
        $stage.empty().append($board);
        $actionbar.prop('hidden', false);

        engine.mount($board, data, makeUi(run));
    }

    /** The hooks handed to a game engine. Calls from an older run are ignored. */
    function makeUi(myRun) {
        return {
            match: function () {
                if (myRun !== run || finished) {
                    return;
                }
                session.matched += 1;
                paintStats();

                const left = session.total - session.matched;
                if (left === 0) {
                    say('All matched!', 'correct');
                    setTimeout(function () {
                        if (myRun === run) {
                            finish();
                        }
                    }, 700); // a beat to see the last pair before the results appear
                } else {
                    say(left === 1 ? 'Matched! One more to go.' : 'Matched! ' + left + ' more to go.', 'correct');
                }
            },
            miss: function () {
                if (myRun !== run || finished) {
                    return;
                }
                session.mistakes += 1;
                paintStats();
                say('Not a match. Try again.', 'wrong');
            },
        };
    }

    // ---- Results --------------------------------------------------------------

    function finish() {
        if (finished || !session) {
            return;
        }
        finished = true;
        run += 1; // anything still pending from the board is now stale

        const result = {
            score: session.score(),
            maxScore: session.maxScore(),
            matched: session.matched,
            total: session.total,
            mistakes: session.mistakes,
            seconds: session.seconds(),
        };
        const complete = result.matched === result.total;

        $actionbar.prop('hidden', true);

        const $save = el('p', 'pl-save-status').attr({ id: 'game-save-status', role: 'status', 'aria-live': 'polite' });
        const $stats = el('div', 'pl-result-stats').append(
            statBox('Matched', result.matched + '/' + result.total),
            statBox('Mistakes', String(result.mistakes)),
            statBox('Time', PL.util.formatDuration(result.seconds))
        );
        const $again = el('button', 'btn btn-pl-primary', 'Play again').attr('type', 'button').on('click', begin);
        const $back = el('a', 'btn btn-outline-secondary', 'Back to games').attr('href', backUrl);

        const $panel = el('section', 'pl-panel pl-results')
            .attr({ 'aria-labelledby': 'game-result-heading', tabindex: '-1' })
            .append(
                el('p', 'pl-eyebrow', complete ? 'Game complete' : 'Game finished').attr('id', 'game-result-heading'),
                el('p', 'pl-result-score', result.score + ' pts').attr('id', 'game-result-score'),
                el('p', 'pl-result-sub', 'out of ' + result.maxScore + ' possible'),
                $stats,
                $save,
                el('div', 'pl-result-actions').append($again, $back)
            );

        $stage.empty().append($panel);
        $panel.trigger('focus');
        say('');

        save(result);
    }

    function statBox(label, value) {
        return el('div', 'pl-stat').append(el('span', 'pl-stat-label', label), el('span', 'pl-stat-value', value));
    }

    function setSaveStatus(text, isError, retry) {
        const $s = $('#game-save-status').removeClass('is-error').text(text);
        if (isError) {
            $s.addClass('is-error');
        }
        if (retry) {
            $s.append(el('button', 'btn btn-sm btn-outline-secondary', 'Try again').attr('type', 'button').on('click', retry));
        }
    }

    function save(result) {
        setSaveStatus('Saving your score…');
        PL.api
            .saveGameScore({
                lessonId: lessonId,
                score: result.score,
                maxScore: result.maxScore,
                timeSpentSeconds: result.seconds,
            })
            .then(function (response) {
                setSaveStatus(response && response.placeholder ? 'Sample mode: this score was not saved.' : 'Score saved.');
            })
            .catch(function (error) {
                console.error(error);
                setSaveStatus("We couldn't save your score.", true, function () {
                    save(result);
                });
            });
    }

    // ---- Buttons ----------------------------------------------------------------

    $('#game-reset').on('click', function () {
        if (data && !finished) {
            begin();
        }
    });

    $('#game-finish').on('click', function () {
        finish();
    });

    if (!engine) {
        showMessage("We don't know how to play this game yet.");
        return;
    }
    load();
});
