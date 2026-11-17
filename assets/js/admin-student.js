/**
 * Admin: one student's full progress charts plus the message thread (admins can write).
 * The student comes from data-student-id on #admin-student, set by the PHP page.
 */
(function (global, $) {
    'use strict';

    const PL = global.PL;

    $(function () {
        if (PL.api.placeholder) $('#admin-sample').prop('hidden', false);
        const studentId = Number($('#admin-student').attr('data-student-id'));
        const feedback = PL.feedbackPanel.create({ title: 'Messages' });
        const view = PL.progressView.create($('#progress-view'), { extra: feedback.$el });

        view.load(studentId).then(function (data) {
            if (!data) return; // the view is already showing its own error
            const name = PL.fmt.name(data.student);
            $('#student-name').text(name);
            $('#student-crumb').text(name);
            $('#student-email').text(data.student.email);
            document.title = name + ' · Play & Learn Admin';
            feedback.load(studentId, data.student.first_name);
        });
    });
})(window, window.jQuery);
