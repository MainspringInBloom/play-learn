/**
 * Small helpers shared by the quiz and game scripts.
 * Load after jQuery and before quiz.js / game-*.js.
 */
(function (global, $) {
    'use strict';

    const PL = (global.PL = global.PL || {});
    PL.games = PL.games || {}; // game engines register themselves here

    /**
     * Builds an element. Text is always inserted with .text(), never as HTML,
     * so quiz and game content (which comes from the database) cannot inject markup.
     */
    function el(tag, className, text) {
        const $element = $(document.createElement(tag));
        if (className) {
            $element.addClass(className);
        }
        if (text !== undefined && text !== null) {
            $element.text(text);
        }
        return $element;
    }

    /** Fisher-Yates shuffle; returns a new array. */
    function shuffle(list) {
        const copy = list.slice();
        for (let i = copy.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [copy[i], copy[j]] = [copy[j], copy[i]];
        }
        return copy;
    }

    /** Like shuffle, but retries so the result is not left in the original order. */
    function shuffleDifferent(list) {
        if (list.length < 2) {
            return list.slice();
        }
        let result = shuffle(list);
        for (let tries = 0; tries < 10 && result.every((item, i) => item === list[i]); tries++) {
            result = shuffle(list);
        }
        return result;
    }

    /** 75 -> "1:15" */
    function formatDuration(totalSeconds) {
        const seconds = Math.max(0, Math.round(totalSeconds));
        const minutes = Math.floor(seconds / 60);
        const rest = seconds % 60;
        return minutes + ':' + (rest < 10 ? '0' : '') + rest;
    }

    PL.util = { el: el, shuffle: shuffle, shuffleDifferent: shuffleDifferent, formatDuration: formatDuration };
})(window, window.jQuery);
