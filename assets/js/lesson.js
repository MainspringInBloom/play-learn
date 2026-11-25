/**
 * Lesson page: the "Mark lesson complete" button for lessons that have no quiz.
 * Markup: student/lesson.php. Talks to the server through PL.api.completeLesson.
 */
$(function () {
    'use strict';

    const $button = $('#complete-lesson');
    const $status = $('#complete-status');
    if (!$button.length) {
        return;
    }

    $button.on('click', function () {
        $button.prop('disabled', true);
        $status.removeClass('is-error').text('Saving…');
        window.PL.api
            .completeLesson(parseInt($button.attr('data-lesson-id'), 10))
            .then(function () {
                window.location.reload(); // the page re-renders in review mode with the next lesson open
            })
            .catch(function (error) {
                console.error(error);
                $button.prop('disabled', false);
                $status.addClass('is-error').text(error.message || "We couldn't save your progress. Try again.");
            });
    });
});
