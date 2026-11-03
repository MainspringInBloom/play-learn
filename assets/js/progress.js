/**
 * Student Progress page (week 6).
 * Loads one progress summary and one feedback list through PL.api, then draws:
 *   stat tiles, quiz score trend, daily activity, course completion, recent games,
 *   and the "Messages from your teachers and parents" panel.
 * Each panel loads and fails on its own, so a broken feedback call never hides charts.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;

    const DAYS = 14;
    let lastData = null;

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

    function formatMinutes(seconds) {
        const m = Math.round(seconds / 60);
        return m < 60 ? m + ' min' : Math.floor(m / 60) + ' h ' + (m % 60) + ' min';
    }

    function relative(iso) {
        const then = new Date(String(iso).replace(' ', 'T'));
        if (isNaN(then)) return '';
        const now = new Date();
        const startOf = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
        const days = Math.round((startOf(now) - startOf(then)) / 86400000); // calendar days, not 24-hour blocks
        if (days <= 0) return 'Today';
        if (days === 1) return 'Yesterday';
        if (days < 14) return days + ' days ago';
        return then.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }

    function showState($panel, message, retry) {
        const $s = el('p', 'pl-state', message);
        if (retry) {
            $s.append(' ', el('button', 'btn btn-link p-0 align-baseline', 'Try again').attr('type', 'button').on('click', retry));
        }
        $panel.empty().append($s);
    }

    // ---- Progress summary and charts -----------------------------------------

    function loadProgress() {
        const $tiles = $('#progress-tiles');
        const $charts = $('#progress-charts');
        $charts.attr('aria-busy', 'true');
        PL.api.getProgress().then(function (data) {
            $charts.attr('aria-busy', 'false');
            lastData = data;
            drawTiles($tiles, data.totals);
            drawCharts(data);
        }).catch(function (err) {
            $charts.attr('aria-busy', 'false');
            $tiles.empty();
            showState($charts, "We couldn't load your progress. " + (err.message || ''), loadProgress);
        });
    }

    function drawTiles($tiles, t) {
        const items = [
            ['Lessons done', t.lessons_completed + '/' + t.total_lessons],
            ['Quiz average', t.quiz_average === null ? '–' : Math.round(t.quiz_average) + '%'],
            ['Games played', String(t.games_played)],
            ['Time learning', formatMinutes(t.time_spent_seconds)],
        ];
        $tiles.empty();
        items.forEach(function (i) {
            $tiles.append(el('div', 'pl-stat').append(el('span', 'pl-stat-label', i[0]), el('span', 'pl-stat-value', i[1])));
        });
    }

    function drawCharts(data) {
        const nothingYet = !data.quiz_scores.length && !data.game_scores.length &&
            !data.courses.some((c) => c.completed_lessons > 0);
        $('#progress-empty').prop('hidden', !nothingYet);

        const scores = data.quiz_scores.map((q) => ({ label: q.quiz_title, score: Number(q.score), when: q.finished_at }));
        if (scores.length) PL.charts.line($('#chart-quiz'), scores, { label: 'Quiz scores over time' });
        else showState($('#chart-quiz'), 'Finish a quiz and your scores will appear here.');

        const days = fillDays(data.daily_activity);
        if (days.some((d) => d.lessons + d.quizzes + d.games > 0)) {
            PL.charts.bars($('#chart-activity'), days, { label: 'Activity in the last ' + DAYS + ' days' });
        } else {
            showState($('#chart-activity'), 'No activity in the last ' + DAYS + ' days. A lesson, quiz or game will show up here.');
        }

        PL.charts.hbars($('#chart-courses'), data.courses.map(function (c) {
            return { label: c.title, value: c.completed_lessons, max: c.total_lessons,
                     note: c.completed_lessons + ' of ' + c.total_lessons + ' lessons' };
        }));

        const games = data.game_scores.map((g) => ({
            label: g.lesson_title + ' (' + g.game_label + ')',
            value: g.score, max: g.max_score,
            note: g.score + '/' + g.max_score + ' · ' + relative(g.completed_at),
        }));
        if (games.length) PL.charts.hbars($('#chart-games'), games);
        else showState($('#chart-games'), 'Play a game and your results will appear here.');
    }

    // ---- Feedback messages ----------------------------------------------------

    function loadFeedback() {
        const $panel = $('#feedback-list');
        PL.api.getFeedback().then(function (res) {
            $panel.empty();
            $('#feedback-unread').text('').prop('hidden', true);
            if (!res.messages.length) {
                showState($panel, 'No messages yet. Notes from your teachers and parents will show up here.');
                return;
            }
            const unread = res.messages.filter((m) => !m.read_at).length;
            if (unread) $('#feedback-unread').text(unread + ' new').prop('hidden', false);
            res.messages.forEach((m) => $panel.append(messageCard(m)));
        }).catch(function (err) {
            showState($panel, "We couldn't load your messages. " + (err.message || ''), loadFeedback);
        });
    }

    function messageCard(m) {
        const roleLabel = m.sender_role === 'parent' ? 'Parent' : 'Teacher';
        const $card = el('article', 'pl-message' + (m.read_at ? '' : ' is-unread'));
        $card.append(
            el('header', 'pl-message-head').append(
                el('strong', '', m.sender_name),
                el('span', 'pl-chip', roleLabel),
                el('time', 'pl-message-when', relative(m.created_at))
            ),
            el('p', 'pl-message-body', m.body) // .text(): message text is never treated as HTML
        );
        if (!m.read_at) {
            $card.append(el('button', 'btn btn-outline-secondary btn-sm', 'Mark as read').attr('type', 'button').on('click', function () {
                const $b = $(this).prop('disabled', true);
                PL.api.markFeedbackRead(m.message_id).then(function () {
                    $card.removeClass('is-unread');
                    $b.remove();
                    const left = $('#feedback-list .is-unread').length;
                    $('#feedback-unread').text(left + ' new').prop('hidden', left === 0);
                }).catch(function () {
                    $b.prop('disabled', false).text('Try again');
                });
            }));
        }
        return $card;
    }

    $(function () {
        if (PL.api.placeholder) $('#progress-sample').prop('hidden', false);
        loadProgress();
        loadFeedback();

        // Charts are drawn at the real pixel width, so redraw when the width changes (rotate / resize).
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
    });
})(window, window.jQuery);
