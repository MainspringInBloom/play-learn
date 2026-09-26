// Play and Learn — login form handling
// Placeholder client-side logic. Teammate 2's backend (api/login.php) will
// return JSON: { success: true, role: "student" } or { success: false, message: "..." }

$(function () {
  $('#loginForm').on('submit', function (e) {
    e.preventDefault();

    const $btn = $('#loginBtn');
    const $error = $('#loginError');
    $error.addClass('d-none').text('');
    $btn.prop('disabled', true).text('Logging in...');

    const payload = {
      email: $('#email').val(),
      password: $('#password').val(),
      remember: $('#remember').is(':checked')
    };

    // TODO (Teammate 2): swap this for a real fetch/ajax call once
    // api/login.php exists. Expected response shape:
    // { success: true, role: "admin" | "student" | "parent" }
    $.ajax({
      url: '/api/login.php',
      method: 'POST',
      data: payload,
      dataType: 'json'
    })
      .done(function (res) {
        if (res.success) {
          if (res.role === 'admin') {
            window.location.href = 'admin/dashboard.html';
          } else if (res.role === 'parent') {
            window.location.href = 'parent/dashboard.html';
          } else {
            window.location.href = 'pages/dashboard.html';
          }
        } else {
          $error.removeClass('d-none').text(res.message || 'Incorrect email or password.');
          $btn.prop('disabled', false).text('Log In');
        }
      })
      .fail(function () {
        $error.removeClass('d-none').text('Could not reach the server. Is the backend running?');
        $btn.prop('disabled', false).text('Log In');
      });
  });
});
