<?php
require 'db.php';

if (isset($_GET['department_id']) || isset($_GET['faculty_level'])) {
    $department_id = isset($_GET['department_id']) ? filter_var($_GET['department_id'], FILTER_SANITIZE_NUMBER_INT) : null;

    try {
        if ($department_id !== null) {
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE department_id = :department_id");
            $stmt->execute(['department_id' => $department_id]);
        } else {
            $stmt = $pdo->query("SELECT * FROM courses WHERE department_id IS NULL");
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
