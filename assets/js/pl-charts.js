/**
 * Tiny SVG chart helpers for the progress page (no chart library needed).
 *
 *   PL.charts.line ($host, points,  opts)  quiz scores over time
 *   PL.charts.bars ($host, days,    opts)  stacked daily activity
 *   PL.charts.hbars($host, rows,    opts)  horizontal progress bars
 *
 * All text goes in with textContent, so database strings can never become markup.
 * Each chart also gets a visually hidden <table> so screen readers get the numbers.
 */
(function (global, $) {
    'use strict';

    const PL = (global.PL = global.PL || {});
    const NS = 'http://www.w3.org/2000/svg';

    function svg(tag, attrs, text) {
        const node = document.createElementNS(NS, tag);
        Object.keys(attrs || {}).forEach((k) => node.setAttribute(k, attrs[k]));
        if (text !== undefined) node.textContent = text;
        return node;
    }

    /** Draw at the real pixel width so text stays a readable 12px (never scaled down on phones). */
    function sizeFor($host, ratio) {
        const w = Math.max(280, Math.min(760, Math.round($host.width() || 560)));
        return { W: w, H: Math.max(190, Math.round(w * ratio)) };
    }

    function frame(width, height, label) {
        return svg('svg', {
            viewBox: '0 0 ' + width + ' ' + height,
            class: 'pl-chart-svg',
            role: 'img',
            'aria-label': label,
            preserveAspectRatio: 'xMidYMid meet',
        });
    }

    /** Hidden data table mirror of a chart. rows: array of arrays. */
    function dataTable(caption, head, rows) {
        const $t = $('<table class="pl-sr-only"></table>');
        $t.append($('<caption></caption>').text(caption));
        const $head = $('<tr></tr>');
        head.forEach((h) => $head.append($('<th scope="col"></th>').text(h)));
        $t.append($('<thead></thead>').append($head));
        const $body = $('<tbody></tbody>');
        rows.forEach((r) => {
            const $tr = $('<tr></tr>');
            r.forEach((c) => $tr.append($('<td></td>').text(c)));
            $body.append($tr);
        });
        return $t.append($body);
    }

    function shortDate(iso) {
        const d = new Date(String(iso).slice(0, 10) + 'T00:00:00');
        return isNaN(d) ? String(iso) : d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }

    /** points: [{ label, score (0-100), when }] oldest first. */
    function line($host, points, opts) {
        opts = opts || {};
        const size = sizeFor($host, 0.43), W = size.W, H = size.H, L = 40, R = 16, T = 16, B = 44;
        const pw = W - L - R, ph = H - T - B;
        const chart = frame(W, H, opts.label || 'Quiz scores over time');

        [0, 25, 50, 75, 100].forEach((v) => {
            const y = T + ph - (v / 100) * ph;
            chart.appendChild(svg('line', { x1: L, x2: W - R, y1: y, y2: y, class: 'pl-grid' }));
            chart.appendChild(svg('text', { x: L - 8, y: y + 4, class: 'pl-axis', 'text-anchor': 'end' }, v + '%'));
        });

        const n = points.length;
        const xAt = (i) => (n === 1 ? L + pw / 2 : L + (i / (n - 1)) * pw);
        const yAt = (s) => T + ph - (Math.max(0, Math.min(100, s)) / 100) * ph;

        if (n > 1) {
            const d = points.map((p, i) => (i ? 'L' : 'M') + xAt(i).toFixed(1) + ' ' + yAt(p.score).toFixed(1)).join(' ');
            chart.appendChild(svg('path', { d: d, class: 'pl-line' }));
        }
        const step = Math.max(1, Math.ceil(n / Math.max(2, Math.floor(pw / 70)))); // keep x labels from colliding
        points.forEach((p, i) => {
            const dot = svg('circle', { cx: xAt(i), cy: yAt(p.score), r: 5, class: 'pl-dot' });
            dot.appendChild(svg('title', {}, p.label + ': ' + Math.round(p.score) + '% (' + shortDate(p.when) + ')'));
            chart.appendChild(dot);
            if (i % step === 0 || (i === n - 1 && (n - 1) % step >= step / 2)) {
                chart.appendChild(svg('text', { x: xAt(i), y: H - B + 18, class: 'pl-axis', 'text-anchor': 'middle' }, shortDate(p.when)));
            }
        });

        $host.empty().append(chart, dataTable(opts.label || 'Quiz scores', ['Quiz', 'Date', 'Score'],
            points.map((p) => [p.label, shortDate(p.when), Math.round(p.score) + '%'])));
    }

    /** days: [{ date, lessons, quizzes, games }] stacked bars. */
    function bars($host, days, opts) {
        opts = opts || {};
        const size = sizeFor($host, 0.43), W = size.W, H = size.H, L = 32, R = 8, T = 16, B = 44;
        const pw = W - L - R, ph = H - T - B;
        const kinds = [['lessons', 'pl-seg-lessons'], ['quizzes', 'pl-seg-quizzes'], ['games', 'pl-seg-games']];
        const totals = days.map((d) => d.lessons + d.quizzes + d.games);
        const max = Math.max(4, Math.max.apply(null, totals)); // never let one item fill the chart
        const niceMax = max % 2 ? max + 1 : max;
        const chart = frame(W, H, opts.label || 'Activity per day');

        [0, niceMax / 2, niceMax].forEach((v) => {
            const y = T + ph - (v / niceMax) * ph;
            chart.appendChild(svg('line', { x1: L, x2: W - R, y1: y, y2: y, class: 'pl-grid' }));
            chart.appendChild(svg('text', { x: L - 6, y: y + 4, class: 'pl-axis', 'text-anchor': 'end' }, String(v)));
        });

        const slot = pw / days.length;
        const bw = Math.min(26, slot * 0.62);
        const step = Math.max(1, Math.ceil(days.length / Math.max(2, Math.floor(pw / 64))));
        days.forEach((d, i) => {
            const x = L + i * slot + (slot - bw) / 2;
            let y = T + ph;
            kinds.forEach((k) => {
                const h = (d[k[0]] / niceMax) * ph;
                if (h > 0) {
                    y -= h;
                    const r = svg('rect', { x: x, y: y, width: bw, height: h, class: k[1] });
                    r.appendChild(svg('title', {}, shortDate(d.date) + ': ' + d[k[0]] + ' ' + k[0]));
                    chart.appendChild(r);
                }
            });
            if (i % step === 0 || (i === days.length - 1 && (days.length - 1) % step >= step / 2)) {
                chart.appendChild(svg('text', { x: x + bw / 2, y: H - B + 18, class: 'pl-axis', 'text-anchor': 'middle' }, shortDate(d.date)));
            }
        });

        $host.empty().append(chart, dataTable(opts.label || 'Activity per day', ['Date', 'Lessons', 'Quizzes', 'Games'],
            days.map((d) => [shortDate(d.date), d.lessons, d.quizzes, d.games])));
    }

    /** rows: [{ label, value, max, note }] as labelled progress bars (HTML, wraps on phones). */
    function hbars($host, rows, opts) {
        opts = opts || {};
        const $list = $('<ul class="pl-hbars"></ul>');
        rows.forEach((r) => {
            const pct = r.max > 0 ? Math.round((r.value / r.max) * 100) : 0;
            const $li = $('<li></li>');
            $li.append(
                $('<div class="pl-hbar-top"></div>').append(
                    $('<span class="pl-hbar-label"></span>').text(r.label),
                    $('<span class="pl-hbar-note"></span>').text(r.note)
                ),
                $('<div class="pl-hbar-track" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>')
                    .attr({ 'aria-valuenow': pct, 'aria-label': r.label + ' ' + pct + '%' })
                    .append($('<div class="pl-hbar-fill"></div>').css('width', pct + '%'))
            );
            $list.append($li);
        });
        $host.empty().append($list);
        void opts;
    }

    PL.charts = { line: line, bars: bars, hbars: hbars };
})(window, window.jQuery);
