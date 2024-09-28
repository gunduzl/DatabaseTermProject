<?php
session_start();
require 'db.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['exam_id'])) {
    die('Exam ID is required.');
}

$exam_id = $_GET['exam_id'];

// Fetch exam details
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = :exam_id");
$stmt->execute(['exam_id' => $exam_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam) {
    die('Exam not found.');
}

// Fetch assigned assistants
$stmt = $pdo->prepare("
    SELECT a.name, a.score
    FROM exam_assignments ea
    JOIN assistants a ON ea.assistant_id = a.id
    WHERE ea.exam_id = :exam_id
");
$stmt->execute(['exam_id' => $exam_id]);
$assistants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background-color: #f2f2f2;
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .back-button {
            display: block;
            width: 100px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Exam Details</h1>

    <table>
        <tr>
            <th>Course ID</th>
            <td><?php echo htmlspecialchars($exam['course_id']); ?></td>
        </tr>
        <tr>
            <th>Exam Name</th>
            <td><?php echo htmlspecialchars($exam['name']); ?></td>
        </tr>
        <tr>
            <th>Exam Date</th>
            <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
        </tr>
        <tr>
            <th>Start Time</th>
            <td><?php echo htmlspecialchars($exam['start_time']); ?></td>
        </tr>
        <tr>
            <th>End Time</th>
            <td><?php echo htmlspecialchars($exam['end_time']); ?></td>
        </tr>
        <tr>
            <th>Number of Assistants</th>
            <td><?php echo htmlspecialchars($exam['num_assistants']); ?></td>
        </tr>
        <tr>
            <th>Number of Classes</th>
            <td><?php echo htmlspecialchars($exam['num_classes']); ?></td>
        </tr>
    </table>

    <h2>Assigned Assistants</h2>
    <ul>
        <?php foreach ($assistants as $assistant): ?>
            <li><?php echo htmlspecialchars($assistant['name']); ?> (Score: <?php echo htmlspecialchars($assistant['score']); ?>)</li>
        <?php endforeach; ?>
    </ul>

    <a href="secretary_page.php" class="back-button">Go Back</a>
</div>
</body>
</html>
