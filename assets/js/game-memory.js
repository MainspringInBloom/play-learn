/**
 * Game: Memory Match  (game_type "memory")
 *
 * A grid of face-down cards. Each pair is a term and its meaning, so the two
 * halves look different. Flip two cards: if they belong together they stay
 * face up (matched); if not they flip back after a moment.
 *
 * data.pairs: [{ a: "int", b: "A whole number" }, ...]   (a = term, b = meaning)
 *
 * A card's text is only put in the page while it is face up, so it is not read
 * out or visible in the page while hidden.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;

    const FLIP_BACK_MS = 900;

    PL.games.memory = {
        mount: function ($board, data, ui) {
            const cards = [];
            data.pairs.forEach(function (pair, i) {
                cards.push({ pairId: i, kind: 'a', text: pair.a });
                cards.push({ pairId: i, kind: 'b', text: pair.b });
            });
            const deck = PL.util.shuffle(cards);

            const $grid = el('div', 'pl-memory').attr({ role: 'group', 'aria-label': 'Memory cards' });
            deck.forEach(function (card, index) {
                const $card = el('button', 'pl-memory-card', '?').attr({
                    type: 'button',
                    'aria-label': hiddenLabel(index, deck.length),
                    'data-pair-id': card.pairId,
                    'data-kind': card.kind,
                    'data-index': index,
                });
                $card.data('text', card.text); // kept in jQuery's store, not in the markup
                $grid.append($card);
            });
            $board.append($grid);

            let flipped = []; // up to two face-up, unmatched cards
            let locked = false; // true while a wrong pair is showing

            $grid.on('click', '.pl-memory-card', function () {
                const $card = $(this);
                if (locked || $card.hasClass('is-flipped') || $card.hasClass('is-matched')) {
                    return;
                }
                faceUp($card);
                flipped.push($card);
                if (flipped.length === 2) {
                    compare();
                }
            });

            function compare() {
                const $first = flipped[0];
                const $second = flipped[1];
                const $both = $first.add($second);

                if ($first.attr('data-pair-id') === $second.attr('data-pair-id')) {
                    $both.removeClass('is-flipped').addClass('is-matched').prop('disabled', true);
                    flipped = [];
                    ui.match();
                    return;
                }

                locked = true;
                $both.addClass('is-wrong');
                ui.miss();
                setTimeout(function () {
                    $both.removeClass('is-wrong');
                    faceDown($first);
                    faceDown($second);
                    flipped = [];
                    locked = false;
                }, FLIP_BACK_MS);
            }

            function faceUp($card) {
                const text = $card.data('text');
                $card.addClass('is-flipped').attr({ 'aria-pressed': 'true', 'aria-label': text }).text(text);
            }

            function faceDown($card) {
                const index = parseInt($card.attr('data-index'), 10);
                $card
                    .removeClass('is-flipped')
                    .attr({ 'aria-pressed': 'false', 'aria-label': hiddenLabel(index, deck.length) })
                    .text('?');
            }

            function hiddenLabel(index, total) {
                return 'Hidden card ' + (index + 1) + ' of ' + total;
            }
        },
    };
})(window, window.jQuery);
