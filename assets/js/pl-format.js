/**
 * Small display helpers shared by the progress, parent and admin pages.
 *   PL.fmt.relative(ts)   "Today" / "Yesterday" / "3 days ago" / "Nov 2"  (calendar days)
 *   PL.fmt.minutes(secs)  "47 min" / "1 h 47 min"
 *   PL.fmt.name(person)   "First Last"
 *   PL.fmt.percent(n)     "77%" or an en dash when there is no value
 */
(function (global) {
    'use strict';

    const PL = (global.PL = global.PL || {});

    function parse(ts) {
        const d = new Date(String(ts).replace(' ', 'T'));
        return isNaN(d) ? null : d;
    }

    PL.fmt = {
        relative: function (ts) {
            if (!ts) return '';
            const then = parse(ts);
            if (!then) return '';
            const now = new Date();
            const startOf = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
            const days = Math.round((startOf(now) - startOf(then)) / 86400000); // calendar days, not 24-hour blocks
            if (days <= 0) return 'Today';
            if (days === 1) return 'Yesterday';
            if (days < 14) return days + ' days ago';
            return then.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
        },

        minutes: function (seconds) {
            const m = Math.round(seconds / 60);
            return m < 60 ? m + ' min' : Math.floor(m / 60) + ' h ' + (m % 60) + ' min';
        },

        name: function (p) {
            return (p.first_name + ' ' + p.last_name).trim();
        },

        percent: function (n) {
            return n === null || n === undefined ? '–' : Math.round(n) + '%';
        },
    };
})(window);
