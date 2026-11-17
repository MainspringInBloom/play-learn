/**
 * Parent dashboard: pick one of your linked children, then see their progress and
 * exchange messages. Children come from PL.api.getChildren() (the server only returns
 * students linked to the logged-in parent). ?student_id=N in the URL selects a child.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;

    let progress = null;
    let feedback = null;
    let children = [];

    function showError($host, message, retry) {
        const $s = el('p', 'pl-state', message + ' ');
        $s.append(el('button', 'btn btn-link p-0 align-baseline', 'Try again').attr('type', 'button').on('click', retry));
        $host.empty().append($s);
    }

    function childCard(c) {
        const pct = c.total_lessons > 0 ? Math.round((c.lessons_completed / c.total_lessons) * 100) : 0;
        const $b = el('button', 'pl-child').attr({ type: 'button', 'data-student-id': c.student_id, 'aria-pressed': 'false' });
        $b.append(
            el('span', 'pl-child-name', PL.fmt.name(c)),
            el('span', 'pl-child-line', c.lessons_completed + ' of ' + c.total_lessons + ' lessons'),
            el('span', 'pl-child-bar').append(el('span', 'pl-child-fill').css('width', pct + '%')),
            el('span', 'pl-child-line', 'Quiz average ' + PL.fmt.percent(c.quiz_average)),
            el('span', 'pl-child-line pl-muted', c.last_active_at ? 'Last active ' + PL.fmt.relative(c.last_active_at).toLowerCase() : 'No activity yet')
        );
        $b.on('click', function () { select(c.student_id, true); });
        return $b;
    }

    function select(studentId, updateUrl) {
        const child = children.find((c) => c.student_id === studentId);
        if (!child) return;
        $('#children .pl-child').each(function () {
            $(this).attr('aria-pressed', String(Number($(this).attr('data-student-id')) === studentId));
        });
        $('#child-heading').text(PL.fmt.name(child) + "’s progress");
        $('#child-detail').prop('hidden', false);
        if (updateUrl && global.history && global.history.replaceState) {
            global.history.replaceState(null, '', '?student_id=' + studentId + (PL.api.demoMode ? '&demo=' + encodeURIComponent(PL.api.demoMode) : ''));
        }
        progress.load(studentId);
        feedback.load(studentId, child.first_name);
    }

    function loadChildren() {
        const $list = $('#children');
        $list.empty().append(el('p', 'pl-state', 'Loading…'));
        PL.api.getChildren().then(function (res) {
            children = res.children;
            $list.empty();
            if (!children.length) {
                $('#child-detail').prop('hidden', true);
                $list.append(el('p', 'pl-empty-banner', 'No students are linked to your account yet. Ask an admin to link your child to you.'));
                return;
            }
            children.forEach((c) => $list.append(childCard(c)));
            const wanted = Number(new URLSearchParams(global.location.search).get('student_id'));
            const start = children.find((c) => c.student_id === wanted) || children[0];
            select(start.student_id, false);
        }).catch(function (err) {
            $('#child-detail').prop('hidden', true);
            showError($list, "We couldn't load your children. " + (err.message || ''), loadChildren);
        });
    }

    $(function () {
        if (PL.api.placeholder) $('#parent-sample').prop('hidden', false);
        feedback = PL.feedbackPanel.create({ title: 'Messages' });
        progress = PL.progressView.create($('#progress-view'), { extra: feedback.$el });
        loadChildren();
    });
})(window, window.jQuery);
