/**
 * Sample data for the parent and admin pages (week 7).
 *
 * TEMPORARY, like the other pl-sample-*.js files: used only by the placeholder
 * methods in pl-api.js. Delete this file and its <script> tag once those methods
 * call the real endpoints. The "database" below lives in memory, so adding or
 * removing people in the admin pages works until the page is reloaded.
 *
 * Demo cast (matches the seed users in schema.sql):
 *   parents   3 Test Parent (children: Test Student, Aisha Bello),
 *             7 Diane Nguyen (Kevin), 8 Robert Gonzalez (Maria)
 *   students  2 Test Student, 4 Maria Gonzalez, 5 Kevin Nguyen (no activity yet), 6 Aisha Bello
 */
(function (global) {
    'use strict';

    const PL = (global.PL = global.PL || {});
    PL.sample = PL.sample || {};

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function daysAgo(n, hour) {
        const d = new Date();
        d.setDate(d.getDate() - n);
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(hour || 16) + ':00:00';
    }

    const users = [
        { user_id: 1, role: 'admin',   first_name: 'Test',   last_name: 'Admin',    email: 'admin@test.com',   created_at: daysAgo(40) },
        { user_id: 2, role: 'student', first_name: 'Test',   last_name: 'Student',  email: 'student@test.com', created_at: daysAgo(40) },
        { user_id: 3, role: 'parent',  first_name: 'Test',   last_name: 'Parent',   email: 'parent@test.com',  created_at: daysAgo(40) },
        { user_id: 4, role: 'student', first_name: 'Maria',  last_name: 'Gonzalez', email: 'maria.g@test.com', created_at: daysAgo(39) },
        { user_id: 5, role: 'student', first_name: 'Kevin',  last_name: 'Nguyen',   email: 'kevin.n@test.com', created_at: daysAgo(39) },
        { user_id: 6, role: 'student', first_name: 'Aisha',  last_name: 'Bello',    email: 'aisha.b@test.com', created_at: daysAgo(39) },
        { user_id: 7, role: 'parent',  first_name: 'Diane',  last_name: 'Nguyen',   email: 'diane.n@test.com', created_at: daysAgo(39) },
        { user_id: 8, role: 'parent',  first_name: 'Robert', last_name: 'Gonzalez', email: 'robert.g@test.com', created_at: daysAgo(39) },
    ];

    // parent_students rows: [parent_id, student_id]
    const links = [[3, 2], [3, 6], [7, 5], [8, 4]];

    // What each student has done. Everything the charts show is derived from this.
    const activity = {
        2: { lessons: 5, quizzes: 6, avg: 76.7, games: 4, seconds: 6420, last: 1 },
        4: { lessons: 3, quizzes: 3, avg: 61.7, games: 2, seconds: 3100, last: 4 },
        5: { lessons: 0, quizzes: 0, avg: null, games: 0, seconds: 0, last: null },
        6: { lessons: 9, quizzes: 9, avg: 88.9, games: 7, seconds: 11800, last: 0 },
    };

    const messages = [
        { message_id: 1, student_id: 2, sender_id: 1, sender_name: 'Test Admin', sender_role: 'admin',
          body: 'Great jump on the Loops quiz! Next, try the Conditionals game again to lock in if/else.', created_at: daysAgo(1, 9), read_at: null },
        { message_id: 2, student_id: 2, sender_id: 3, sender_name: 'Test Parent', sender_role: 'parent',
          body: 'Proud of the steady practice this week. Keep it up!', created_at: daysAgo(4, 18), read_at: daysAgo(4, 19) },
        { message_id: 3, student_id: 6, sender_id: 1, sender_name: 'Test Admin', sender_role: 'admin',
          body: 'Aisha is ahead of the class on Python 101. Ready for Java 101 whenever she wants.', created_at: daysAgo(2, 10), read_at: daysAgo(2, 12) },
    ];

    PL.sample.people = {
        users: users,
        links: links,
        activity: activity,
        messages: messages,
        totalLessons: 12,
        nextUserId: 9,
        nextMessageId: 4,
        daysAgo: daysAgo,
    };

    // -----------------------------------------------------------------------
    // A tiny pretend server. Each function below stands in for one endpoint and
    // applies the same permission and validation rules the real one must.
    // It throws Error(message) the way a real endpoint answers {ok:false, error}.
    // -----------------------------------------------------------------------

    const P = PL.sample.people;
    const clone = (v) => JSON.parse(JSON.stringify(v));
    const fullName = (u) => u.first_name + ' ' + u.last_name;

    function viewer() { return PL.viewer || { user_id: 2, role: 'student', first_name: 'Test' }; }
    function userById(id) { return P.users.find((u) => u.user_id === id) || null; }
    function studentById(id) { const u = userById(id); return u && u.role === 'student' ? u : null; }
    function parentsOf(studentId) { return P.links.filter((l) => l[1] === studentId).map((l) => userById(l[0])); }
    function childrenOf(parentId) { return P.links.filter((l) => l[0] === parentId).map((l) => userById(l[1])); }
    function ref(u) { return { user_id: u.user_id, name: fullName(u) }; }

    /** admin: any student; parent: linked children only; student: themselves. */
    function assertCanView(studentId) {
        const v = viewer();
        const ok = studentById(studentId) && (v.role === 'admin' ||
            (v.role === 'parent' && P.links.some((l) => l[0] === v.user_id && l[1] === studentId)) ||
            (v.role === 'student' && v.user_id === studentId));
        if (!ok) throw new Error('You do not have access to that student.');
    }
    function assertAdmin() {
        if (viewer().role !== 'admin') throw new Error('This action is for admin accounts only.');
    }

    function summary(u) {
        const a = P.activity[u.user_id] || { lessons: 0, quizzes: 0, avg: null, games: 0, seconds: 0, last: null };
        return {
            student_id: u.user_id, first_name: u.first_name, last_name: u.last_name, email: u.email,
            lessons_completed: a.lessons, total_lessons: P.totalLessons, quiz_average: a.avg,
            games_played: a.games, time_spent_seconds: a.seconds,
            last_active_at: a.last === null ? null : P.daysAgo(a.last, 15),
        };
    }

    PL.sample.server = {
        /** One student's full progress (the same shape for student, parent and admin). */
        progress: function (studentId) {
            const v = viewer();
            const id = studentId === undefined || studentId === null ? v.user_id : Number(studentId);
            assertCanView(id);
            const a = P.activity[id] || { lessons: 0, quizzes: 0, avg: null, games: 0, seconds: 0 };
            const data = clone(PL.sample.progress);
            const u = studentById(id);
            data.student = { student_id: id, first_name: u.first_name, last_name: u.last_name, email: u.email };
            data.totals = { lessons_completed: a.lessons, total_lessons: P.totalLessons, quizzes_taken: a.quizzes,
                            quiz_average: a.avg, games_played: a.games, time_spent_seconds: a.seconds };
            const shift = a.avg === null ? 0 : a.avg - 76.7;
            data.quiz_scores = data.quiz_scores.slice(0, Math.min(6, a.quizzes)).map((q) => {
                q.score = Math.max(0, Math.min(100, Math.round(q.score + shift)));
                return q;
            });
            data.daily_activity = data.daily_activity.slice(0, Math.min(6, a.quizzes));
            data.game_scores = data.game_scores.slice(0, Math.min(4, a.games));
            data.courses[0].completed_lessons = Math.min(a.lessons, 6);
            data.courses[1].completed_lessons = Math.max(0, a.lessons - 6);
            return data;
        },

        /** The logged-in parent's linked students, with a short summary each. */
        children: function () {
            const v = viewer();
            if (v.role !== 'parent') throw new Error('This action is for parent accounts only.');
            return { children: childrenOf(v.user_id).map(summary) };
        },

        /** Admin: every enrolled student with summary stats and their parents. */
        overview: function () {
            assertAdmin();
            return { students: P.users.filter((u) => u.role === 'student').map((u) => {
                const row = summary(u);
                row.parents = parentsOf(u.user_id).map(ref);
                return row;
            }) };
        },

        /** Admin: all students or all parents; each lists who they are linked to. */
        users: function (role) {
            assertAdmin();
            if (role !== 'student' && role !== 'parent') throw new Error("'role' must be student or parent.");
            return { users: P.users.filter((u) => u.role === role).map((u) => {
                const out = clone(u);
                out.links = (role === 'parent' ? childrenOf(u.user_id) : parentsOf(u.user_id)).map(ref);
                return out;
            }) };
        },

        createUser: function (input) {
            assertAdmin();
            const role = input.role;
            const first = String(input.first_name || '').trim();
            const last = String(input.last_name || '').trim();
            const email = String(input.email || '').trim();
            if (role !== 'student' && role !== 'parent') throw new Error('Choose student or parent.');
            if (!first || first.length > 50) throw new Error('First name is required (50 characters max).');
            if (!last || last.length > 50) throw new Error('Last name is required (50 characters max).');
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email) || email.length > 100) throw new Error('Enter a valid email address.');
            if (String(input.password || '').length < 8) throw new Error('Password must be at least 8 characters.');
            if (P.users.some((u) => u.email.toLowerCase() === email.toLowerCase())) throw new Error('That email is already in use.');
            const user = { user_id: P.nextUserId++, role: role, first_name: first, last_name: last, email: email, created_at: P.daysAgo(0, 12) };
            P.users.push(user);
            const out = clone(user);
            out.links = [];
            return { user: out };
        },

        /** Removes a student or parent, their links, their results and the messages they sent or got. */
        deleteUser: function (userId) {
            assertAdmin();
            const u = userById(Number(userId));
            if (!u) throw new Error('User not found.');
            if (u.role === 'admin') throw new Error('Admin accounts cannot be removed here.');
            P.users.splice(P.users.indexOf(u), 1);
            for (let i = P.links.length - 1; i >= 0; i--) {
                if (P.links[i][0] === u.user_id || P.links[i][1] === u.user_id) P.links.splice(i, 1);
            }
            for (let i = P.messages.length - 1; i >= 0; i--) {
                if (P.messages[i].student_id === u.user_id || P.messages[i].sender_id === u.user_id) P.messages.splice(i, 1);
            }
            delete P.activity[u.user_id];
            return { ok: true };
        },

        addLink: function (parentId, studentId) {
            assertAdmin();
            const parent = userById(Number(parentId));
            if (!parent || parent.role !== 'parent') throw new Error('Parent not found.');
            if (!studentById(Number(studentId))) throw new Error('Student not found.');
            if (P.links.some((l) => l[0] === parent.user_id && l[1] === Number(studentId))) throw new Error('Those two are already linked.');
            P.links.push([parent.user_id, Number(studentId)]);
            return { ok: true };
        },

        removeLink: function (parentId, studentId) {
            assertAdmin();
            const i = P.links.findIndex((l) => l[0] === Number(parentId) && l[1] === Number(studentId));
            if (i < 0) throw new Error('That link does not exist.');
            P.links.splice(i, 1);
            return { ok: true };
        },

        /** Messages about one student, newest first. is_mine marks the viewer's own. */
        feedback: function (studentId) {
            assertCanView(Number(studentId));
            const v = viewer();
            const rows = P.messages.filter((m) => m.student_id === Number(studentId))
                .sort((a, b) => b.message_id - a.message_id)
                .map((m) => { const out = clone(m); out.is_mine = m.sender_id === v.user_id; return out; });
            return { messages: rows };
        },

        send: function (studentId, body) {
            const v = viewer();
            if (v.role === 'student') throw new Error('Students cannot send messages.');
            assertCanView(Number(studentId)); // parents: linked children only; admins: anyone
            const text = String(body || '').trim();
            if (!text) throw new Error('Write a message first.');
            if (text.length > 2000) throw new Error('Messages can be up to 2000 characters.');
            const m = { message_id: P.nextMessageId++, student_id: Number(studentId), sender_id: v.user_id,
                        sender_name: v.first_name + (v.role === 'admin' ? ' Admin' : ' Parent'),
                        sender_role: v.role, body: text, created_at: P.daysAgo(0, new Date().getHours()), read_at: null };
            P.messages.push(m);
            const out = clone(m);
            out.is_mine = true;
            return { message: out };
        },
    };
})(window);
