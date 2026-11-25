/**
 * Admin: manage which students are linked to which parent (the parent_students table).
 * Pick a parent on the left; on the right see their children, unlink one, or link another
 * student. Students with no parent yet are listed at the bottom.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;

    let parents = [];
    let students = [];
    let selected = null; // user_id of the chosen parent

    function setStatus(text, isError) {
        $('#links-status').text(text || '').toggleClass('is-error', !!isError).toggleClass('is-ok', !!text && !isError)
            .attr('role', isError ? 'alert' : 'status');
    }

    function current() { return parents.find((p) => p.user_id === selected) || null; }

    function renderParents() {
        const $list = $('#parent-list').empty();
        parents.forEach(function (p) {
            const n = p.links.length;
            const $b = el('button', 'pl-pick-parent' + (p.user_id === selected ? ' is-selected' : ''))
                .attr({ type: 'button', 'aria-pressed': String(p.user_id === selected), 'data-parent-id': p.user_id });
            $b.append(el('span', 'fw-semibold', PL.fmt.name(p)), el('span', 'pl-muted small', n + (n === 1 ? ' child' : ' children')));
            $b.on('click', function () { selected = p.user_id; setStatus(''); render(); });
            $list.append($b);
        });
    }

    function renderDetail() {
        const p = current();
        const $d = $('#parent-detail').empty();
        if (!p) { $d.append(el('p', 'pl-state', 'Choose a parent to see and change their children.')); return; }

        $d.append(el('h2', 'h5 mb-1', PL.fmt.name(p)), el('p', 'pl-muted small mb-3', p.email));

        const $kids = el('ul', 'pl-links').attr('aria-label', 'Children of ' + PL.fmt.name(p));
        if (!p.links.length) $kids.append(el('li', 'pl-muted', 'No children linked yet.'));
        p.links.forEach(function (kid) {
            const $rm = el('button', 'btn btn-sm btn-outline-danger', 'Unlink').attr({ type: 'button', 'aria-label': 'Unlink ' + kid.name + ' from ' + PL.fmt.name(p) });
            $rm.on('click', function () {
                $rm.prop('disabled', true);
                PL.api.removeLink(p.user_id, kid.user_id).then(function () {
                    p.links = p.links.filter((l) => l.user_id !== kid.user_id);
                    setStatus('Unlinked ' + kid.name + ' from ' + PL.fmt.name(p) + '.');
                    render();
                }).catch(function (err) {
                    $rm.prop('disabled', false);
                    setStatus("Couldn't unlink: " + (err.message || 'please try again.'), true);
                });
            });
            $kids.append(el('li', '').append(el('span', '', kid.name), $rm));
        });
        $d.append($kids);

        const linkedIds = p.links.map((l) => l.user_id);
        const free = students.filter((s) => linkedIds.indexOf(s.user_id) < 0)
            .sort((a, b) => PL.fmt.name(a).localeCompare(PL.fmt.name(b)));
        const $form = $('<form class="pl-link-add" novalidate></form>');
        if (!free.length) {
            $form.append(el('p', 'pl-muted mb-0', students.length ? 'Every student is already linked to this parent.' : 'There are no students yet. Add one on the Users page.'));
        } else {
            const $sel = $('<select class="form-select" id="link-student"></select>');
            $sel.append($('<option value="">Choose a student…</option>'));
            free.forEach((s) => $sel.append($('<option></option>').val(s.user_id).text(PL.fmt.name(s))));
            const $add = el('button', 'btn btn-pl-primary', 'Link student').attr({ type: 'submit', id: 'link-add' });
            $form.append(el('label', 'form-label', 'Link another student').attr('for', 'link-student'), el('div', 'd-flex gap-2').append($sel, $add));
            $form.on('submit', function (e) {
                e.preventDefault();
                const sid = Number($sel.val());
                if (!sid) { setStatus('Choose a student to link.', true); return; }
                const student = students.find((s) => s.user_id === sid);
                $add.prop('disabled', true);
                PL.api.addLink(p.user_id, sid).then(function () {
                    p.links.push({ user_id: sid, name: PL.fmt.name(student) });
                    setStatus('Linked ' + PL.fmt.name(student) + ' to ' + PL.fmt.name(p) + '.');
                    render();
                }).catch(function (err) {
                    $add.prop('disabled', false);
                    setStatus("Couldn't link: " + (err.message || 'please try again.'), true);
                });
            });
        }
        $d.append($form);
    }

    function renderOrphans() {
        const linked = {};
        parents.forEach((p) => p.links.forEach((l) => { linked[l.user_id] = true; }));
        const orphans = students.filter((s) => !linked[s.user_id]);
        const $o = $('#orphans').empty();
        if (!orphans.length) { $o.append(el('p', 'pl-muted mb-0', 'Every student has at least one parent.')); return; }
        $o.append(el('p', 'mb-2', 'Students with no parent linked yet:'));
        const $ul = el('ul', 'pl-chips');
        orphans.forEach((s) => $ul.append(el('li', 'pl-chip', PL.fmt.name(s))));
        $o.append($ul);
    }

    function render() {
        renderParents();
        renderDetail();
        renderOrphans();
    }

    function load() {
        const $state = $('#links-state').prop('hidden', false).empty().append(el('p', 'pl-state', 'Loading…'));
        $('#links-body').prop('hidden', true);
        Promise.all([PL.api.getUsers('parent'), PL.api.getUsers('student')]).then(function (res) {
            parents = res[0].users;
            students = res[1].users;
            if (!parents.length) {
                $state.empty().append(el('p', 'pl-empty-banner', 'There are no parents yet. Add one on the Users page, then link them here.'));
                return;
            }
            $state.prop('hidden', true).empty();
            $('#links-body').prop('hidden', false);
            if (!current()) selected = parents[0].user_id;
            render();
        }).catch(function (err) {
            const $s = el('p', 'pl-state', "We couldn't load the families. " + (err.message || '') + ' ');
            $s.append(el('button', 'btn btn-link p-0 align-baseline', 'Try again').attr('type', 'button').on('click', load));
            $state.empty().append($s);
        });
    }

    $(function () {
        load();
    });
})(window, window.jQuery);
