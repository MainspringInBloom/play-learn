<?php
/** User rows in the shape the admin pages expect (shared by get_users and create_user). */

function pl_user_with_links(PDO $pdo, array $u): array
{
    $id = (int) $u['user_id'];
    if ($u['role'] === 'parent') {
        $sql = 'SELECT u.user_id, u.first_name, u.last_name FROM parent_students ps JOIN users u ON u.user_id = ps.student_id WHERE ps.parent_id = ?';
    } else {
        $sql = 'SELECT u.user_id, u.first_name, u.last_name FROM parent_students ps JOIN users u ON u.user_id = ps.parent_id WHERE ps.student_id = ?';
    }
    $stmt = $pdo->prepare($sql . ' ORDER BY u.first_name, u.last_name, u.user_id');
    $stmt->execute([$id]);
    $links = array_map(function ($r) {
        return ['user_id' => (int) $r['user_id'], 'name' => trim($r['first_name'] . ' ' . $r['last_name'])];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));

    return [
        'user_id'    => $id,
        'role'       => $u['role'],
        'first_name' => $u['first_name'],
        'last_name'  => $u['last_name'],
        'email'      => $u['email'],
        'created_at' => $u['created_at'] ?? null,
        'links'      => $links,
    ];
}
