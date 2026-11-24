<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require __DIR__ . '/../config/db.php';

    $stmt = $pdo->prepare(
        'SELECT l.lesson_id, l.title, l.sort_order
         FROM lessons l
         JOIN courses c ON l.course_id = c.course_id
         WHERE c.title = ?
         ORDER BY l.sort_order, l.lesson_id'
    );

    $stmt->execute(['Java 101']);
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'lessons' => $lessons
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to retrieve Java lessons'
    ]);
}
