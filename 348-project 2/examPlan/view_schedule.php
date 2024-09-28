<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_id = filter_var($_POST['department_id'], FILTER_SANITIZE_NUMBER_INT);

    // Fetch exam schedule for the selected department
    try {
        $stmt = $pdo->prepare("SELECT * FROM exams WHERE course_id IN (SELECT id FROM courses WHERE department_id = :department_id) ORDER BY date, time");
        $stmt->execute(['department_id' => $department_id]);
        $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Schedule</title>
</head>
<body>
<h2>Exam Schedule for Department</h2>
<table border="1">
    <tr>
        <th>Course</th>
        <th>Exam Name</th>
        <th>Date</th>
        <th>Time</th>
    </tr>
    <?php if (!empty($exams)): ?>
        <?php foreach ($exams as $exam): ?>
            <tr>
                <td><?php echo htmlspecialchars($exam['course_id']); ?></td>
                <td><?php echo htmlspecialchars($exam['name']); ?></td>
                <td><?php echo htmlspecialchars($exam['date']); ?></td>
                <td><?php echo htmlspecialchars($exam['time']); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4">No exams found for the selected department.</td>
        </tr>
    <?php endif; ?>
</table>
</body>
</html>
