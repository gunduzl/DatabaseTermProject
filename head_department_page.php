<?php
session_start();
require 'db.php';

// Ensure the user ID is set in the session
if (!isset($_SESSION['user_id'])) {
    die('User ID not set in session.');
}

$user_id = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'head_of_department') {
    die('User not found or you do not have the necessary permissions.');
}

// Get the department ID of the head of the department
$department_id = $user['department_id'];

// Get the exam schedule for the department
$examScheduleQuery = $pdo->prepare("
    SELECT e.exam_date, e.start_time, e.end_time, c.name as course_name, e.name as exam_name, e.num_assistants, e.num_classes 
    FROM exams e
    JOIN courses c ON e.course_id = c.id
    WHERE c.department_id = ?
    ORDER BY e.exam_date ASC, e.start_time ASC
");
$examScheduleQuery->execute([$department_id]);
$examSchedule = $examScheduleQuery->fetchAll(PDO::FETCH_ASSOC);

// Get the assistant workloads
$assistantWorkloadsQuery = $pdo->prepare("
    SELECT a.name, a.score, (a.score / (SELECT SUM(score) FROM assistants WHERE department_id = ?)) * 100 as percentage
    FROM assistants a
    WHERE a.department_id = ?
    ORDER BY a.score DESC
");
$assistantWorkloadsQuery->execute([$department_id, $department_id]);
$assistantWorkloads = $assistantWorkloadsQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Head of Department Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>

<h2>Exam Schedule</h2>
<table>
    <tr>
        <th>Exam Name</th>
        <th>Course Name</th>
        <th>Exam Date</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Number of Assistants</th>
        <th>Number of Classes</th>
    </tr>
    <?php if (count($examSchedule) > 0): ?>
        <?php foreach ($examSchedule as $exam): ?>
            <tr>
                <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                <td><?php echo htmlspecialchars($exam['course_name']); ?></td>
                <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
                <td><?php echo htmlspecialchars($exam['start_time']); ?></td>
                <td><?php echo htmlspecialchars($exam['end_time']); ?></td>
                <td><?php echo htmlspecialchars($exam['num_assistants']); ?></td>
                <td><?php echo htmlspecialchars($exam['num_classes']); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7">No exams scheduled.</td>
        </tr>
    <?php endif; ?>
</table>

<h2>Assistant Workloads</h2>
<table>
    <tr>
        <th>Assistant Name</th>
        <th>Score</th>
        <th>Percentage</th>
    </tr>
    <?php if (count($assistantWorkloads) > 0): ?>
        <?php foreach ($assistantWorkloads as $workload): ?>
            <tr>
                <td><?php echo htmlspecialchars($workload['name']); ?></td>
                <td><?php echo htmlspecialchars($workload['score']); ?></td>
                <td><?php echo htmlspecialchars(number_format($workload['percentage'], 2)) . '%'; ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="3">No assistants found.</td>
        </tr>
    <?php endif; ?>
</table>
</body>
</html>
