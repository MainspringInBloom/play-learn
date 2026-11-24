<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/viewer.php';
require_role('admin');

$page_title = 'Users';
$active_nav = 'users';

require __DIR__ . '/../includes/admin_header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">
<link rel="stylesheet" href="/assets/css/progress.css">
<link rel="stylesheet" href="/assets/css/people.css">

<div id="admin-app">
    <h1 class="h3 mb-1">Users</h1>
    <p class="pl-muted">Add or remove student and parent accounts. To connect a parent to a student, use
        <a href="/admin/links.php">Family links</a>.</p>

    <span class="pl-sample-badge" id="admin-sample" hidden>Sample data &middot; API not connected</span>

    <section class="pl-panel mb-4" aria-labelledby="add-title">
        <h2 id="add-title">Add an account</h2>
        <form id="add-form" class="mt-2" novalidate>
            <div class="pl-form-grid">
                <div>
                    <label class="form-label" for="u-role">Role</label>
                    <select class="form-select" id="u-role" name="role">
                        <option value="student">Student</option>
                        <option value="parent">Parent</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="u-first">First name</label>
                    <input class="form-control" id="u-first" name="first_name" maxlength="50" autocomplete="off" required>
                </div>
                <div>
                    <label class="form-label" for="u-last">Last name</label>
                    <input class="form-control" id="u-last" name="last_name" maxlength="50" autocomplete="off" required>
                </div>
                <div>
                    <label class="form-label" for="u-email">Email</label>
                    <input class="form-control" type="email" id="u-email" name="email" maxlength="100" autocomplete="off" required>
                </div>
                <div>
                    <label class="form-label" for="u-password">Starting password</label>
                    <input class="form-control" type="password" id="u-password" name="password" autocomplete="new-password" aria-describedby="pw-help" required>
                    <div class="form-text" id="pw-help">At least 8 characters. Share it with them; they can't change it yet.</div>
                </div>
            </div>
            <button class="btn btn-pl-primary mt-3" type="submit" id="u-submit">Add account</button>
        </form>
    </section>

    <p class="pl-status" id="users-status" role="status"></p>

    <div class="pl-tabs" role="tablist" aria-label="Account type">
        <button class="pl-tab active" type="button" role="tab" id="tab-student" aria-selected="true" aria-controls="users-panel">Students</button>
        <button class="pl-tab" type="button" role="tab" id="tab-parent" aria-selected="false" aria-controls="users-panel">Parents</button>
    </div>

    <div id="users-panel" role="tabpanel" aria-labelledby="users-title">
        <h2 class="visually-hidden" id="users-title">Students</h2>
        <div id="users-state"><p class="pl-state">Loading&hellip;</p></div>
        <p class="pl-state" id="users-none" hidden></p>
        <table class="table pl-table pl-stack align-middle" id="users-table" hidden>
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col" id="users-head-links">Parents</th>
                    <th scope="col">Added</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody id="users-body"></tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-sample-people.js"></script>
<?php render_viewer_script(); ?>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/pl-format.js"></script>
<script src="/assets/js/admin-users.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
