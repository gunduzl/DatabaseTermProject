<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty_id = $_POST['faculty'];
    $department_id = $_POST['department'] ? $_POST['department'] : null;
    $course_name = $_POST['course_name'];
    $course_day = $_POST['course_day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    try {
        // Insert the course
        $stmt = $pdo->prepare("INSERT INTO courses (name, department_id) VALUES (:course_name, :department_id)");
        $stmt->execute(['course_name' => $course_name, 'department_id' => $department_id]);
        $course_id = $pdo->lastInsertId();

        // Insert the course schedule
        $scheduleStmt = $pdo->prepare("INSERT INTO course_schedules (course_id, day_of_week, start_time, end_time) VALUES (:course_id, :course_day, :start_time, :end_time)");
        $scheduleStmt->execute(['course_id' => $course_id, 'course_day' => $course_day, 'start_time' => $start_time, 'end_time' => $end_time]);

        header('Location: head_secretary_page.php');
        exit();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>
