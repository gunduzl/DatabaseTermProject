<?php
require 'db.php';

if (isset($_GET['scope'])) {
    $scope = $_GET['scope'];

    try {
        if ($scope === 'faculty') {
            $stmt = $pdo->query("
                SELECT e.name, e.exam_date, e.start_time, e.end_time, e.num_assistants, e.num_classes
                FROM exams e
                JOIN courses c ON e.course_id = c.id
                WHERE c.department_id IS NULL
            ");
        } else {
            $department_id = filter_var($scope, FILTER_SANITIZE_NUMBER_INT);
            $stmt = $pdo->prepare("
                SELECT e.name, e.exam_date, e.start_time, e.end_time, e.num_assistants, e.num_classes
                FROM exams e
                JOIN courses c ON e.course_id = c.id
                WHERE c.department_id = :department_id
            ");
            $stmt->execute(['department_id' => $department_id]);
        }

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
