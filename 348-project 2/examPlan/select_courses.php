<?php
session_start();
require 'db.php';

// Ensure user is logged in and is an assistant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'assistant') {
    header('Location: login.php');
    exit();
}

// Check if courses are selected
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course'])) {
    $assistant_id = $_SESSION['user_id'];
    $course_id = $_POST['course'];

    try {
        // Insert new selected course
        $stmt = $pdo->prepare("INSERT INTO assistant_courses (assistant_id, course_id) VALUES (:assistant_id, :course_id)");
        $stmt->execute(['assistant_id' => $assistant_id, 'course_id' => $course_id]);

        // Redirect back to assistant page
        header('Location: assistant_page.php');
        exit();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    header('Location: assistant_page.php');
    exit();
}
?>
