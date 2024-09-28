<?php
require 'db.php';

if (isset($_GET['faculty_id'])) {
    $faculty_id = filter_var($_GET['faculty_id'], FILTER_SANITIZE_NUMBER_INT);
    try {
        $stmt = $pdo->prepare("SELECT * FROM departments WHERE faculty_id = :faculty_id");
        $stmt->execute(['faculty_id' => $faculty_id]);
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add a special option for faculty-level courses (no department)
        array_unshift($departments, ['id' => '', 'name' => 'No Department (Faculty-level courses)']);

        echo json_encode($departments);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

?>
