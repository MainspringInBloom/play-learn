/**
 * Two-way messages panel for parents and admins: the thread about one student plus a box
 * to write a new message.
 *
 *   const panel = PL.feedbackPanel.create({ title: 'Messages' });
 *   panel.$el      the <section> to place on the page (or pass to progressView as `extra`)
 *   panel.load(studentId, firstName)   load that student's thread and point the box at them
 *
 * Message text goes in with .text(), never as HTML. Needs pl-api.js, pl-util.js, pl-format.js.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;
    const MAX = 2000;

    function create(options) {
        options = options || {};
        const uid = 'fb' + Math.random().toString(36).slice(2, 7);

        const $el = el('section', 'pl-panel pl-feedback-panel').attr('aria-labelledby', uid + '-title');
        const $title = el('h2', '', options.title || 'Messages').attr('id', uid + '-title');
        const $sub = el('p', 'pl-panel-sub', 'Notes to and from this student’s teachers and parents.');
        const $list = el('div', 'pl-thread').attr({ id: 'feedback-thread', 'aria-live': 'polite' });

        const $form = $('<form class="pl-composer" novalidate></form>');
        const $label = el('label', 'form-label', 'Write a message').attr('for', uid + '-body');
        const $body = $('<textarea class="form-control" rows="3"></textarea>').attr({ id: uid + '-body', maxlength: MAX, name: 'body' });
        const $count = el('span', 'pl-count', '0/' + MAX).attr('aria-hidden', 'true');
        const $send = el('button', 'btn btn-pl-primary', 'Send message').attr({ type: 'submit', id: 'feedback-send' }).prop('disabled', true);
        const $status = el('p', 'pl-composer-status').attr({ id: 'feedback-status', role: 'status', 'aria-live': 'polite' });
        $form.append($label, $body, el('div', 'pl-composer-row').append($count, $send), $status);

        $el.append($title, $sub, $list, $form);

        let studentId = null;
        let current = 0;

        function setStatus(text, isError) {
            $status.text(text).toggleClass('is-error', !!isError);
            if (isError) $status.attr('role', 'alert'); else $status.attr('role', 'status');
        }

        function card(m) {
            const roleLabel = m.sender_role === 'parent' ? 'Parent' : 'Teacher';
            const $c = el('article', 'pl-message' + (m.is_mine ? ' is-mine' : ''));
            $c.append(
                el('header', 'pl-message-head').append(
                    el('strong', '', m.is_mine ? 'You' : m.sender_name),
                    el('span', 'pl-chip', roleLabel),
                    el('time', 'pl-message-when', PL.fmt.relative(m.created_at))
                ),
                el('p', 'pl-message-body', m.body)
            );
            return $c;
        }

        function load(id, firstName) {
            const mine = ++current;
            studentId = id;
            setStatus('');
            $body.val('');
            $count.text('0/' + MAX);
            $send.prop('disabled', true);
            $label.text(firstName ? 'Write a message to ' + firstName : 'Write a message');
            $list.empty().append(el('p', 'pl-state', 'Loading…'));

            return PL.api.getFeedback(id).then(function (res) {
                if (mine !== current) return;
                $list.empty();
                if (!res.messages.length) {
                    $list.append(el('p', 'pl-state', 'No messages yet. Be the first to write one.'));
                    return;
                }
                res.messages.forEach((m) => $list.append(card(m)));
            }).catch(function (err) {
                if (mine !== current) return;
                const $s = el('p', 'pl-state', "We couldn't load the messages. " + (err.message || '') + ' ');
                $s.append(el('button', 'btn btn-link p-0 align-baseline', 'Try again').attr('type', 'button').on('click', function () { load(id, firstName); }));
                $list.empty().append($s);
            });
        }

        $body.on('input', function () {
            const len = $body.val().length;
            $count.text(len + '/' + MAX);
            $send.prop('disabled', $body.val().trim().length === 0);
        });

        $form.on('submit', function (e) {
            e.preventDefault();
            const text = $body.val().trim();
            if (!text || studentId === null) return;
            const sentTo = studentId;
            $send.prop('disabled', true).text('Sending…');
            setStatus('');
            PL.api.sendFeedback({ studentId: sentTo, body: text }).then(function (res) {
                $send.text('Send message');
                if (sentTo !== studentId) return; // switched child while sending; the message still went to the right student
                $list.children('.pl-state').remove();
                $list.prepend(card(res.message));
                $body.val('');
                $count.text('0/' + MAX);
                $send.prop('disabled', true);
                setStatus('Message sent.');
            }).catch(function (err) {
                $send.text('Send message').prop('disabled', false);
                setStatus("Couldn't send: " + (err.message || 'please try again.'), true); // text stays in the box
            });
        });

        return { $el: $el, load: load };
    }

    PL.feedbackPanel = { create: create };
})(window, window.jQuery);
