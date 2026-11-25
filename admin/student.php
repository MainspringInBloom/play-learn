<?php
require __DIR__ . '/../includes/auth.php';
require_role('admin');

$student_id = (int) ($_GET['student_id'] ?? 0);
if ($student_id < 1) {
    header('Location: /admin/students.php');
    exit;
}

$page_title = 'Student progress';
$active_nav = 'students';

require __DIR__ . '/../includes/admin_header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">
<link rel="stylesheet" href="/assets/css/progress.css">
<link rel="stylesheet" href="/assets/css/people.css">

<div id="admin-student" data-student-id="<?= $student_id ?>">
    <nav class="small mb-2"><a href="/admin/students.php">Students</a> / <span id="student-crumb">Student</span></nav>

    <div class="mb-3">
        <h1 class="h3 mb-0" id="student-name">Student</h1>
        <p class="pl-muted mb-0" id="student-email"></p>
    </div>


    <div id="progress-view"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/pl-format.js"></script>
<script src="/assets/js/pl-charts.js"></script>
<script src="/assets/js/progress-view.js"></script>
<script src="/assets/js/feedback-panel.js"></script>
<script src="/assets/js/admin-student.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
