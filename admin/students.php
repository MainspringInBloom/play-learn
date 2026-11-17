<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/viewer.php';
require_role('admin');

$page_title = 'Students';
$active_nav = 'students';

require __DIR__ . '/../includes/admin_header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">
<link rel="stylesheet" href="/assets/css/progress.css">
<link rel="stylesheet" href="/assets/css/people.css">

<div id="admin-app">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Students</h1>
            <p class="pl-muted mb-0">Everyone currently enrolled, with their progress at a glance.</p>
        </div>
        <a class="btn btn-pl-primary" href="/admin/users.php">Add or remove students</a>
    </div>

    <span class="pl-sample-badge" id="admin-sample" hidden>Sample data &middot; API not connected</span>

    <div class="pl-tools" id="students-tools" hidden>
        <div>
            <label class="form-label" for="student-search">Search</label>
            <input class="form-control" type="search" id="student-search" placeholder="Name or email" autocomplete="off">
        </div>
        <div>
            <label class="form-label" for="student-sort">Sort by</label>
            <select class="form-select" id="student-sort">
                <option value="name">Name</option>
                <option value="progress">Lessons completed</option>
                <option value="quiz">Quiz average</option>
                <option value="active">Last active</option>
            </select>
        </div>
        <p class="pl-muted mb-2" id="students-count" role="status"></p>
    </div>

    <div id="students-state"><p class="pl-state">Loading&hellip;</p></div>
    <p class="pl-state" id="students-none" hidden>No students match that search.</p>

    <table class="table pl-table pl-stack align-middle" id="students-table" hidden>
        <thead>
            <tr>
                <th scope="col">Student</th>
                <th scope="col">Lessons</th>
                <th scope="col">Quiz average</th>
                <th scope="col">Games</th>
                <th scope="col">Last active</th>
                <th scope="col">Parents</th>
                <th scope="col"><span class="visually-hidden">Actions</span></th>
            </tr>
        </thead>
        <tbody id="students-body"></tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-sample-progress.js"></script>
<script src="/assets/js/pl-sample-people.js"></script>
<?php render_viewer_script(); ?>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/pl-format.js"></script>
<script src="/assets/js/admin-students.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
