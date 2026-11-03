/**
 * Game: Match the Output  (game_type "match_output")
 *
 * Two columns: code snippets on the left, outputs on the right (shuffled).
 * Pick a card, then pick a card from the other column. A correct pair locks
 * in; a wrong pair shakes and the picks clear. Picking in the other order
 * (output first) works too. Picking the selected card again un-selects it.
 *
 * data.pairs: [{ code: "for i in range(3):\n    print(i)", output: "0\n1\n2" }, ...]
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;

    PL.games.match_output = {
        mount: function ($board, data, ui) {
            const codeCards = data.pairs.map(function (pair, i) {
                return { pairId: i, text: pair.code };
            });
            const outputCards = PL.util.shuffleDifferent(
                data.pairs.map(function (pair, i) {
                    return { pairId: i, text: pair.output };
                })
            );

            const $grid = el('div', 'pl-match');
            $grid.append(column('Code snippet', 'code', codeCards), column('Output', 'output', outputCards));
            $board.append($grid);

            let selected = null; // { side, pairId, $card }
            let locked = false; // true while a wrong pair is being shown

            $grid.on('click', '.pl-pick', function () {
                if (locked) {
                    return;
                }
                const $card = $(this);
                const side = $card.attr('data-side');
                const pairId = $card.attr('data-pair-id');

                if (!selected) {
                    select($card, side, pairId);
                } else if (selected.$card[0] === $card[0]) {
                    deselect();
                } else if (selected.side === side) {
                    deselect();
                    select($card, side, pairId);
                } else {
                    attempt(selected, { side: side, pairId: pairId, $card: $card });
                }
            });

            function select($card, side, pairId) {
                selected = { side: side, pairId: pairId, $card: $card };
                $card.addClass('is-selected').attr('aria-pressed', 'true');
            }

            function deselect() {
                selected.$card.removeClass('is-selected').attr('aria-pressed', 'false');
                selected = null;
            }

            function attempt(first, second) {
                const $both = first.$card.add(second.$card);
                $both.removeClass('is-selected');

                if (first.pairId === second.pairId) {
                    $both.addClass('is-matched').attr('aria-pressed', 'false').prop('disabled', true);
                    selected = null;
                    ui.match();
                    return;
                }

                locked = true;
                $both.addClass('is-wrong');
                ui.miss();
                setTimeout(function () {
                    $both.removeClass('is-wrong').attr('aria-pressed', 'false');
                    selected = null;
                    locked = false;
                }, 600);
            }

            function column(label, side, cards) {
                const labelId = 'match-label-' + side;
                const $list = el('div', 'pl-card-list').attr({ role: 'group', 'aria-labelledby': labelId });
                cards.forEach(function (card) {
                    const $button = el('button', 'pl-pick').attr({
                        type: 'button',
                        'aria-pressed': 'false',
                        'data-side': side,
                        'data-pair-id': card.pairId,
                    });
                    $button.append(el('code', '', card.text));
                    $list.append($button);
                });
                return el('section', 'pl-match-col').append(el('h2', 'pl-col-label', label).attr('id', labelId), $list);
            }
        },
    };
})(window, window.jQuery);
