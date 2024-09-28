<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_id = $_POST['exam_id'];

    // Fetch exam details
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = :exam_id");
    $stmt->execute(['exam_id' => $exam_id]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch available assistants
    $stmt = $pdo->prepare("
        SELECT a.id, a.name, a.score
        FROM assistants a
        LEFT JOIN assistant_courses ac ON a.id = ac.assistant_id
        LEFT JOIN courses c ON ac.course_id = c.id
        WHERE a.department_id = (SELECT department_id FROM courses WHERE id = :course_id)
        AND NOT EXISTS (
            SELECT 1 FROM exams e
            JOIN exam_assignments ea ON e.id = ea.exam_id
            WHERE ea.assistant_id = a.id
            AND e.exam_date = :exam_date
            AND e.start_time = :exam_time
        )
        GROUP BY a.id
        ORDER BY a.score ASC
        LIMIT :num_assistants
    ");
    $stmt->bindValue(':course_id', $exam['course_id'], PDO::PARAM_INT);
    $stmt->bindValue(':exam_date', $exam['exam_date'], PDO::PARAM_STR);
    $stmt->bindValue(':exam_time', $exam['start_time'], PDO::PARAM_STR);
    $stmt->bindValue(':num_assistants', $exam['num_assistants'], PDO::PARAM_INT);
    $stmt->execute();
    $assistants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Assign assistants to the exam
    foreach ($assistants as $assistant) {
        $stmt = $pdo->prepare("INSERT INTO exam_assignments (exam_id, assistant_id) VALUES (:exam_id, :assistant_id)");
        $stmt->execute([
            'exam_id' => $exam_id,
            'assistant_id' => $assistant['id']
        ]);

        // Update assistant's score
        $stmt = $pdo->prepare("UPDATE assistants SET score = score + 1 WHERE id = :assistant_id");
        $stmt->execute(['assistant_id' => $assistant['id']]);
    }

    header('Location: index.php');
    exit();
}
?>

