// Play and Learn — dashboard page
// For now this loads fake/placeholder data so the page works standalone.
// Once Teammate 2 builds an endpoint (e.g. api/get_dashboard.php returning
// JSON from users/courses/lesson_progress/quiz_attempts), swap FAKE_DATA
// below for a real $.getJSON('/api/get_dashboard.php') call.

const FAKE_DATA = {
  user: { first_name: 'Alex' },
  course: { course_id: 1, title: 'Python 101' },
  current_lesson: { title: 'Loops & Conditionals', index: 4, total: 10 },
  progress_pct: 40,
  stats: { lessons_completed: 3, quiz_average: 86, games_played: 5 },
  streak_days: 4
};

function renderDashboard(data) {
  $('#userInitial').text(data.user.first_name.charAt(0).toUpperCase());
  $('#userFirstName').text(data.user.first_name);
  $('#welcomeName').text(data.user.first_name);

  $('#courseCard').attr('data-course-id', data.course.course_id);
  $('#courseTitle').text(data.course.title);
  $('#currentLessonLabel').text(
    'Lesson ' + data.current_lesson.index + ' of ' + data.current_lesson.total + ' — ' + data.current_lesson.title
  );
  $('#courseProgressPct').text(data.progress_pct + '%');
  $('#courseProgressFill').css('width', data.progress_pct + '%');

  $('#statLessonsCompleted').text(data.stats.lessons_completed);
  $('#statQuizAverage').text(data.stats.quiz_average + '%');
  $('#statGamesPlayed').text(data.stats.games_played);

  $('#streakCount').text(data.streak_days + '-day streak');
}

$(function () {
  // TODO (swap once backend is ready):
  // $.getJSON('/api/get_dashboard.php').done(renderDashboard);
  renderDashboard(FAKE_DATA);
});
