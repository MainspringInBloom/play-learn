/**
 * Admin: every enrolled student with their progress at a glance (search + sort), each
 * linking to admin/student.php for the full charts and messages.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;
    const el = PL.util.el;

    let students = [];

    const sorters = {
        name: (a, b) => PL.fmt.name(a).localeCompare(PL.fmt.name(b)),
        progress: (a, b) => (b.lessons_completed / (b.total_lessons || 1)) - (a.lessons_completed / (a.total_lessons || 1)),
        quiz: (a, b) => (b.quiz_average === null ? -1 : b.quiz_average) - (a.quiz_average === null ? -1 : a.quiz_average),
        active: (a, b) => (b.last_active_at ? new Date(String(b.last_active_at).replace(' ', 'T')).getTime() : 0) -
                          (a.last_active_at ? new Date(String(a.last_active_at).replace(' ', 'T')).getTime() : 0),
    };

    function row(s) {
        const pct = s.total_lessons > 0 ? Math.round((s.lessons_completed / s.total_lessons) * 100) : 0;
        const $tr = $('<tr></tr>').attr('data-student-id', s.student_id);
        const $name = $('<td data-label="Student"></td>').append(
            el('a', 'fw-semibold', PL.fmt.name(s)).attr('href', '/admin/student.php?student_id=' + s.student_id),
            el('div', 'pl-muted small', s.email)
        );
        const $lessons = $('<td data-label="Lessons"></td>').append(
            el('span', '', s.lessons_completed + '/' + s.total_lessons),
            el('span', 'pl-child-bar pl-inline-bar').append(el('span', 'pl-child-fill').css('width', pct + '%'))
        );
        const parents = s.parents.length ? s.parents.map((p) => p.name).join(', ') : '—';
        $tr.append(
            $name,
            $lessons,
            $('<td data-label="Quiz average"></td>').text(PL.fmt.percent(s.quiz_average)),
            $('<td data-label="Games"></td>').text(String(s.games_played)),
            $('<td data-label="Last active"></td>').text(s.last_active_at ? PL.fmt.relative(s.last_active_at) : 'Never'),
            $('<td data-label="Parents"></td>').text(parents),
            $('<td class="text-end"></td>').append(el('a', 'btn btn-sm btn-outline-secondary', 'View').attr({
                href: '/admin/student.php?student_id=' + s.student_id,
                'aria-label': 'View ' + PL.fmt.name(s),
            }))
        );
        return $tr;
    }

    function render() {
        const q = $('#student-search').val().trim().toLowerCase();
        const sortBy = $('#student-sort').val();
        const shown = students.filter((s) => (PL.fmt.name(s) + ' ' + s.email).toLowerCase().includes(q)).sort(sorters[sortBy]);
        const $body = $('#students-body').empty();
        shown.forEach((s) => $body.append(row(s)));
        $('#students-count').text(shown.length === students.length
            ? students.length + (students.length === 1 ? ' student' : ' students')
            : shown.length + ' of ' + students.length + ' students');
        $('#students-table').prop('hidden', shown.length === 0);
        $('#students-none').prop('hidden', shown.length !== 0 || students.length === 0);
    }

    function load() {
        const $state = $('#students-state').prop('hidden', false).empty().append(el('p', 'pl-state', 'Loading…'));
        PL.api.getStudentsOverview().then(function (res) {
            students = res.students;
            if (!students.length) {
                $state.empty().append(el('p', 'pl-empty-banner', 'No students are enrolled yet. Add one on the Users page.'));
                $('#students-tools').prop('hidden', true);
                return;
            }
            $state.prop('hidden', true).empty();
            $('#students-tools').prop('hidden', false);
            render();
        }).catch(function (err) {
            const $s = el('p', 'pl-state', "We couldn't load the students. " + (err.message || '') + ' ');
            $s.append(el('button', 'btn btn-link p-0 align-baseline', 'Try again').attr('type', 'button').on('click', load));
            $state.empty().append($s);
        });
    }

    $(function () {
        $('#student-search').on('input', render);
        $('#student-sort').on('change', render);
        load();
    });
})(window, window.jQuery);
