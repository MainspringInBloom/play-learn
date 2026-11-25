/**
 * Admin: add and remove student and parent accounts.
 *  - Two tabs, Students and Parents, each a table with a Remove button (asks first, inline).
 *  - An add form that creates the account (the server hashes the password).
 * Removing a student also removes their scores and progress; removing a parent also removes
 * the messages they sent. Linking parents to students is on admin/links.php.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;

    const WORDS = { student: ['student', 'Students'], parent: ['parent', 'Parents'] };
    let role = 'student';
    let users = [];

    function setStatus(text, kind, extraNode) {
        const $s = $('#users-status');
        $s.empty().removeClass('is-error is-ok').attr('role', kind === 'error' ? 'alert' : 'status');
        if (!text) return;
        $s.addClass(kind === 'error' ? 'is-error' : 'is-ok').text(text);
        if (extraNode) $s.append(' ', extraNode);
    }

    function actionsCell(u, $tr) {
        const $td = $('<td class="text-end pl-actions-cell"></td>');
        const label = PL.fmt.name(u);
        const $remove = el('button', 'btn btn-sm btn-outline-danger', 'Remove').attr({ type: 'button', 'aria-label': 'Remove ' + label });
        $remove.on('click', function () { confirmCell($td, u, $tr, $remove); });
        return $td.append($remove);
    }

    function confirmCell($td, u, $tr, $remove) {
        const label = PL.fmt.name(u);
        const warning = u.role === 'student'
            ? 'This also deletes their scores, progress and messages.'
            : 'This also removes their links to students and the messages they sent.';
        const $yes = el('button', 'btn btn-sm btn-danger', 'Yes, remove').attr('type', 'button');
        const $no = el('button', 'btn btn-sm btn-outline-secondary', 'Cancel').attr('type', 'button');
        const $box = el('div', 'pl-confirm').attr('role', 'group').attr('aria-label', 'Confirm removing ' + label);
        $box.append(el('p', 'mb-2', 'Remove ' + label + '? ' + warning), $yes, ' ', $no);
        $remove.detach(); // detach (not empty) keeps the Remove button's click handler for Cancel
        $td.empty().append($box);
        $no.trigger('focus').on('click', function () { $td.empty().append($remove); $remove.trigger('focus'); });
        $yes.on('click', function () {
            $yes.prop('disabled', true).text('Removing…');
            PL.api.deleteUser(u.user_id).then(function () {
                users = users.filter((x) => x.user_id !== u.user_id);
                $tr.remove();
                setStatus('Removed ' + label + '.', 'ok');
                renderEmpty();
            }).catch(function (err) {
                $yes.prop('disabled', false).text('Yes, remove');
                setStatus("Couldn't remove " + label + ': ' + (err.message || 'please try again.'), 'error');
            });
        });
    }

    function row(u) {
        const $tr = $('<tr></tr>').attr('data-user-id', u.user_id);
        const linked = u.links.length ? u.links.map((l) => l.name).join(', ') : '—';
        $tr.append(
            $('<td data-label="Name"></td>').text(PL.fmt.name(u)),
            $('<td data-label="Email"></td>').text(u.email),
            $('<td data-label="' + (role === 'parent' ? 'Children' : 'Parents') + '"></td>').text(linked),
            $('<td data-label="Added"></td>').text(u.created_at ? PL.fmt.relative(u.created_at) : ''),
            actionsCell(u, $tr)
        );
        return $tr;
    }

    function renderEmpty() {
        const none = users.length === 0;
        $('#users-table').prop('hidden', none);
        $('#users-none').prop('hidden', !none).text('No ' + WORDS[role][1].toLowerCase() + ' yet. Use the form above to add one.');
    }

    function renderTable() {
        $('#users-head-links').text(role === 'parent' ? 'Children' : 'Parents');
        const $body = $('#users-body').empty();
        users.forEach((u) => $body.append(row(u)));
        renderEmpty();
    }

    function load() {
        const $state = $('#users-state').prop('hidden', false).empty().append(el('p', 'pl-state', 'Loading…'));
        $('#users-table, #users-none').prop('hidden', true);
        $('#users-body').empty(); // never leave the other tab's rows in the page while loading
        const requested = role;
        PL.api.getUsers(requested).then(function (res) {
            if (requested !== role) return;
            users = res.users;
            $state.prop('hidden', true).empty();
            renderTable();
        }).catch(function (err) {
            if (requested !== role) return;
            const $s = el('p', 'pl-state', "We couldn't load the " + WORDS[role][1].toLowerCase() + '. ' + (err.message || '') + ' ');
            $s.append(el('button', 'btn btn-link p-0 align-baseline', 'Try again').attr('type', 'button').on('click', load));
            $state.empty().append($s);
        });
    }

    function switchTab(next) {
        role = next;
        $('#tab-student, #tab-parent').each(function () {
            const active = this.id === 'tab-' + role;
            $(this).toggleClass('active', active).attr('aria-selected', String(active));
        });
        $('#u-role').val(role);
        $('#users-title').text(WORDS[role][1]);
        setStatus('');
        load();
    }

    function validate(v) {
        if (!v.first_name || v.first_name.length > 50) return 'First name is required (50 characters max).';
        if (!v.last_name || v.last_name.length > 50) return 'Last name is required (50 characters max).';
        if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v.email) || v.email.length > 100) return 'Enter a valid email address.';
        if (v.password.length < 8) return 'Password must be at least 8 characters.';
        return null;
    }

    function onAdd(e) {
        e.preventDefault();
        const v = {
            role: $('#u-role').val(),
            first_name: $('#u-first').val().trim(),
            last_name: $('#u-last').val().trim(),
            email: $('#u-email').val().trim(),
            password: $('#u-password').val(),
        };
        const problem = validate(v);
        if (problem) { setStatus(problem, 'error'); return; }
        const $btn = $('#u-submit').prop('disabled', true).text('Adding…');
        PL.api.createUser(v).then(function (res) {
            $btn.prop('disabled', false).text('Add account');
            const name = PL.fmt.name(res.user);
            const $link = el('a', '', 'Link them on the Family links page').attr('href', '/admin/links.php');
            setStatus('Added ' + name + ' as a ' + res.user.role + '.', 'ok', $link);
            $('#add-form')[0].reset();
            $('#u-role').val(role);
            $('#u-first').trigger('focus');
            if (res.user.role === role) {
                users.push(res.user);
                renderTable();
            }
        }).catch(function (err) {
            $btn.prop('disabled', false).text('Add account');
            setStatus("Couldn't add the account: " + (err.message || 'please try again.'), 'error');
        });
    }

    $(function () {
        $('#tab-student').on('click', () => switchTab('student'));
        $('#tab-parent').on('click', () => switchTab('parent'));
        $('#u-role').on('change', function () { switchTab($(this).val()); });
        $('#add-form').on('submit', onAdd);
        switchTab('student');
    });
})(window, window.jQuery);
