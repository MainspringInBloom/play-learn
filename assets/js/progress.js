/**
 * Student Progress page (week 6, now built on the shared progress view).
 * Loads the student's own progress and messages. The messages panel here is an inbox with
 * "Mark as read"; parents and admins use the two-way panel in feedback-panel.js instead.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;

    function showState($panel, message, retry) {
        const $s = el('p', 'pl-state', message);
        if (retry) {
            $s.append(' ', el('button', 'btn btn-link p-0 align-baseline', 'Try again').attr('type', 'button').on('click', retry));
        }
        $panel.empty().append($s);
    }

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
                el('time', 'pl-message-when', PL.fmt.relative(m.created_at))
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
        PL.progressView.create($('#progress-view'), { extra: $('#feedback-panel') }).load();
        loadFeedback();
    });
})(window, window.jQuery);
