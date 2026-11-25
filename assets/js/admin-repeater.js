/**
 * Add / remove rows in the admin editors (answer options on question_form.php, pairs on game_form.php).
 *
 * Markup contract:
 *   <div data-repeater data-min="2" data-max="6">
 *     <div data-repeater-rows> ...rows, each with class "js-row"... </div>
 *     <template data-repeater-template> a row with class "js-row" </template>
 *     <button type="button" data-repeater-add>Add</button>
 *   </div>
 * Inside a row: inputs whose name is written as data-name="options[{i}][text]" get their
 * real name set (and re-set after every add/remove) so PHP receives a clean 0-based list.
 * A radio with data-index gets value = the row's index. A button.js-remove deletes its row.
 * Plain JavaScript on purpose: no library needed.
 */
(function () {
    'use strict';

    function init(root) {
        const rows = root.querySelector('[data-repeater-rows]');
        const template = root.querySelector('[data-repeater-template]');
        const addButton = root.querySelector('[data-repeater-add]');
        const min = parseInt(root.getAttribute('data-min') || '0', 10);
        const max = parseInt(root.getAttribute('data-max') || '99', 10);

        function renumber() {
            const all = rows.querySelectorAll('.js-row');
            all.forEach(function (row, i) {
                row.querySelectorAll('[data-name]').forEach(function (input) {
                    input.name = input.getAttribute('data-name').replace('{i}', i);
                });
                row.querySelectorAll('[data-index]').forEach(function (input) {
                    input.value = String(i);
                });
                row.querySelectorAll('[data-row-number]').forEach(function (n) {
                    n.textContent = String(i + 1);
                });
            });
            addButton.disabled = all.length >= max;
            rows.querySelectorAll('.js-remove').forEach(function (b) {
                b.disabled = all.length <= min;
            });
            // Never leave a radio group with nothing chosen.
            const radios = rows.querySelectorAll('input[type=radio][data-index]');
            if (radios.length && !rows.querySelector('input[type=radio][data-index]:checked')) {
                radios[0].checked = true;
            }
        }

        addButton.addEventListener('click', function () {
            if (rows.querySelectorAll('.js-row').length >= max) {
                return;
            }
            rows.appendChild(template.content.firstElementChild.cloneNode(true));
            renumber();
            const last = rows.querySelector('.js-row:last-child input[type=text], .js-row:last-child textarea');
            if (last) { last.focus(); }
        });

        rows.addEventListener('click', function (event) {
            const button = event.target.closest('.js-remove');
            if (!button || button.disabled) {
                return;
            }
            button.closest('.js-row').remove();
            renumber();
        });

        renumber();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-repeater]').forEach(init);
    });
})();
