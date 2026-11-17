/**
 * The progress charts for ONE student, shared by every page that shows them:
 *   student/progress.php   the student's own
 *   parent/dashboard.php   a linked child
 *   admin/student.php      any enrolled student
 *
 *   const view = PL.progressView.create($host, { extra: $panelOrNull });
 *   view.load(studentId)      // omit studentId for the logged-in student's own
 *   // returns a promise that resolves with the data (data.student has the name)
 *
 * It builds the tiles and four chart panels inside $host (and appends `extra`, e.g. the
 * messages panel, as the last panel). If loading fails only the chart panels are replaced
 * by an error with "Try again"; the extra panel stays. Needs pl-charts.js and pl-format.js.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;
    const DAYS = 14;

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function isoDay(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }

    /** Always returns exactly DAYS entries, oldest first, filling quiet days with zeros. */
    function fillDays(rows) {
        const byDate = {};
        (rows || []).forEach((r) => { byDate[String(r.date).slice(0, 10)] = r; });
        const out = [];
        const today = new Date();
        for (let i = DAYS - 1; i >= 0; i--) {
            const d = new Date(today.getFullYear(), today.getMonth(), today.getDate() - i);
            const key = isoDay(d);
            const r = byDate[key] || {};
            out.push({ date: key, lessons: r.lessons || 0, quizzes: r.quizzes || 0, games: r.games || 0 });
        }
        return out;
    }

    function showState($panel, message) {
        $panel.empty().append(el('p', 'pl-state', message));
    }

    function panel(title, sub, hostId, wide) {
        const $p = el('section', 'pl-panel pl-chart-panel' + (wide ? ' pl-panel--wide' : ''));
        $p.append(el('h2', '', title), el('p', 'pl-panel-sub', sub), el('div', '').attr('id', hostId).append(el('p', 'pl-state', 'Loading…')));
        return $p;
    }

    function create($host, options) {
        options = options || {};

        const $tiles = el('div', 'pl-stats').attr({ id: 'progress-tiles', 'aria-label': 'Summary' });
        const $empty = el('p', 'pl-empty-banner', 'Nothing here yet. Finish a lesson, quiz or game and the charts will fill in.')
            .attr('id', 'progress-empty').prop('hidden', true);
        const $error = el('div', 'pl-panel pl-panel--wide').attr('id', 'progress-error').prop('hidden', true);
        const $grid = el('div', 'pl-progress-grid').attr({ id: 'progress-charts', 'aria-busy': 'true' });

        const $legend = $('<ul class="pl-legend" aria-hidden="true"><li><i class="k-lessons"></i>Lessons</li>' +
            '<li><i class="k-quizzes"></i>Quizzes</li><li><i class="k-games"></i>Games</li></ul>');
        const $activity = panel('Activity, last 14 days', 'Lessons, quizzes and games finished each day.', 'chart-activity');
        $activity.append($legend);

        $grid.append(
            $error,
            panel('Quiz scores', 'The result on each quiz finished, oldest to newest.', 'chart-quiz', true),
            $activity,
            panel('Course progress', 'Lessons completed in each course.', 'chart-courses'),
            panel('Recent games', 'Points earned out of the best possible.', 'chart-games')
        );
        if (options.extra) $grid.append(options.extra);

        $host.empty().append($tiles, $empty, $grid);

        let lastData = null;
        let current = 0; // guards against a slow earlier load overwriting a newer one

        function drawTiles(t) {
            const items = [
                ['Lessons done', t.lessons_completed + '/' + t.total_lessons],
                ['Quiz average', PL.fmt.percent(t.quiz_average)],
                ['Games played', String(t.games_played)],
                ['Time learning', PL.fmt.minutes(t.time_spent_seconds)],
            ];
            $tiles.empty();
            items.forEach((i) => $tiles.append(el('div', 'pl-stat').append(el('span', 'pl-stat-label', i[0]), el('span', 'pl-stat-value', i[1]))));
        }

        function drawCharts(data) {
            const nothingYet = !data.quiz_scores.length && !data.game_scores.length &&
                !data.courses.some((c) => c.completed_lessons > 0);
            $empty.prop('hidden', !nothingYet);

            const scores = data.quiz_scores.map((q) => ({ label: q.quiz_title, score: Number(q.score), when: q.finished_at }));
            if (scores.length) PL.charts.line($('#chart-quiz'), scores, { label: 'Quiz scores over time' });
            else showState($('#chart-quiz'), 'No quizzes finished yet.');

            const days = fillDays(data.daily_activity);
            if (days.some((d) => d.lessons + d.quizzes + d.games > 0)) {
                PL.charts.bars($('#chart-activity'), days, { label: 'Activity in the last ' + DAYS + ' days' });
            } else {
                showState($('#chart-activity'), 'No activity in the last ' + DAYS + ' days.');
            }

            PL.charts.hbars($('#chart-courses'), data.courses.map((c) => ({
                label: c.title, value: c.completed_lessons, max: c.total_lessons,
                note: c.completed_lessons + ' of ' + c.total_lessons + ' lessons',
            })));

            const games = data.game_scores.map((g) => ({
                label: g.lesson_title + ' (' + g.game_label + ')',
                value: g.score, max: g.max_score,
                note: g.score + '/' + g.max_score + ' · ' + PL.fmt.relative(g.completed_at),
            }));
            if (games.length) PL.charts.hbars($('#chart-games'), games);
            else showState($('#chart-games'), 'No games played yet.');
        }

        function load(studentId) {
            const mine = ++current;
            $error.prop('hidden', true);
            $grid.attr('aria-busy', 'true');
            return PL.api.getProgress(studentId).then(function (data) {
                if (mine !== current) return data;
                $grid.attr('aria-busy', 'false');
                $grid.children('.pl-chart-panel').prop('hidden', false);
                lastData = data;
                drawTiles(data.totals);
                drawCharts(data);
                return data;
            }).catch(function (err) {
                if (mine !== current) return null;
                $grid.attr('aria-busy', 'false');
                lastData = null;
                $tiles.empty();
                $empty.prop('hidden', true);
                $grid.children('.pl-chart-panel').prop('hidden', true);
                const $msg = el('p', 'pl-state', "We couldn't load the progress. " + (err.message || '') + ' ');
                $msg.append(el('button', 'btn btn-link p-0 align-baseline', 'Try again').attr('type', 'button').on('click', function () { load(studentId); }));
                $error.empty().append($msg).prop('hidden', false);
                return null;
            });
        }

        // Charts are drawn at the real pixel width, so redraw when the width changes.
        let lastWidth = $(window).width();
        let timer = null;
        $(window).on('resize', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                if (lastData && $(window).width() !== lastWidth) {
                    lastWidth = $(window).width();
                    drawCharts(lastData);
                }
            }, 150);
        });

        return { load: load };
    }

    PL.progressView = { create: create };
})(window, window.jQuery);
