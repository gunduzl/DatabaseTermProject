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

if (!$user) {
    die('User not found.');
}

// Get the department ID of the assistant
$department_id = $user['department_id'];

// Get all courses from the assistant's department
$allCoursesQuery = $pdo->prepare("SELECT id, name FROM courses WHERE department_id = ?");
$allCoursesQuery->execute([$department_id]);
$allCourses = $allCoursesQuery->fetchAll(PDO::FETCH_ASSOC);

// Get the course schedule
$courseScheduleQuery = $pdo->prepare("
    SELECT cs.day_of_week, cs.start_time, cs.end_time, c.name as course_name 
    FROM course_schedules cs 
    JOIN courses c ON cs.course_id = c.id 
    JOIN assistant_courses ac ON c.id = ac.course_id 
    WHERE ac.assistant_id = ?
");
$courseScheduleQuery->execute([$user_id]);
$courseSchedules = $courseScheduleQuery->fetchAll(PDO::FETCH_ASSOC);

// Determine the start date of the week
$startDate = isset($_GET['week']) ? new DateTime($_GET['week']) : new DateTime();
$startDate->modify('Monday this week');
$endDate = clone $startDate;
$endDate->modify('Sunday this week');

// Get the weekly plan including newly added exams
$scheduleQuery = $pdo->prepare("
    SELECT e.exam_date, e.start_time, e.end_time, c.name as course_name, e.name as exam_name 
    FROM exams e
    JOIN courses c ON e.course_id = c.id
    JOIN assistant_courses ac ON c.id = ac.course_id
    WHERE ac.assistant_id = ? AND e.exam_date BETWEEN ? AND ?
");
$scheduleQuery->execute([$user_id, $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
$schedule = $scheduleQuery->fetchAll(PDO::FETCH_ASSOC);

// Format the weekly schedule
$timeSlots = ["08:00-09:00", "09:00-10:00", "10:00-11:00", "11:00-12:00", "12:00-13:00", "13:00-14:00", "14:00-15:00", "15:00-16:00", "16:00-17:00", "17:00-18:00", "18:00-19:00", "19:00-20:00"];
$weekDays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

// Initialize the schedule array
$scheduleFormatted = [];
foreach ($timeSlots as $timeSlot) {
    $scheduleFormatted[$timeSlot] = array_fill_keys($weekDays, ['type' => '', 'name' => '']);
}

// Fill the schedule with courses
foreach ($courseSchedules as $item) {
    $dayOfWeek = $item['day_of_week'];
    $startTime = $item['start_time'];
    $endTime = $item['end_time'];
    $courseName = $item['course_name'];

    // Map the course schedule to the time slots
    foreach ($timeSlots as $timeSlot) {
        $slotStartTime = substr($timeSlot, 0, 5) . ":00";
        $slotEndTime = substr($timeSlot, 6, 5) . ":00";
        if ($startTime >= $slotStartTime && $startTime < $slotEndTime) {
            $scheduleFormatted[$timeSlot][$dayOfWeek] = ['type' => 'course', 'name' => $courseName];
        }
    }
}

// Fill the schedule with exams
foreach ($schedule as $item) {
    $dayOfWeek = date('l', strtotime($item['exam_date']));
    $startTime = $item['start_time'];
    $endTime = $item['end_time'];
    $eventName = $item['exam_name'] ?: $item['course_name'];

    // Map the exam schedule to the time slots
    foreach ($timeSlots as $timeSlot) {
        $slotStartTime = substr($timeSlot, 0, 5) . ":00";
        $slotEndTime = substr($timeSlot, 6, 5) . ":00";
        if ($startTime >= $slotStartTime && $startTime < $slotEndTime) {
            $scheduleFormatted[$timeSlot][$dayOfWeek] = ['type' => 'exam', 'name' => $eventName];
        }
    }
}

function getNextWeek($date) {
    $nextWeek = clone $date;
    $nextWeek->modify('+1 week');
    return $nextWeek->format('Y-m-d');
}

function getPreviousWeek($date) {
    $previousWeek = clone $date;
    $previousWeek->modify('-1 week');
    return $previousWeek->format('Y-m-d');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assistant Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .course {
            background-color: #ffff99;
        }
        .exam {
            background-color: #A6F5ED;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .button-container a, .button-container button {
            padding: 10px 20px;
            text-decoration: none;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .button-container a:hover, .button-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>

<h2>Select Courses</h2>
<form action="select_courses.php" method="post">
    <label for="course">Select Course:</label>
    <select id="course" name="course">
        <?php foreach ($allCourses as $course): ?>
            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Add Course</button>
</form>

<h2>Your Weekly Schedule</h2>
<div class="button-container">
    <a href="?week=<?php echo getPreviousWeek($startDate); ?>">Previous Week</a>
    <button onclick="window.location.reload();">Refresh Table</button>
    <a href="?week=<?php echo getNextWeek($startDate); ?>">Next Week</a>
</div>
<table>
    <tr>
        <th>Time Slot</th>
        <th>Monday</th>
        <th>Tuesday</th>
        <th>Wednesday</th>
        <th>Thursday</th>
        <th>Friday</th>
        <th>Saturday</th>
        <th>Sunday</th>
    </tr>
    <?php foreach ($timeSlots as $timeSlot): ?>
        <tr>
            <td><?php echo $timeSlot; ?></td>
            <?php foreach ($weekDays as $day): ?>
                <td class="<?php echo (!empty($scheduleFormatted[$timeSlot][$day]['type']) ? ($scheduleFormatted[$timeSlot][$day]['type'] == 'exam' ? 'exam' : 'course') : ''); ?>">
                    <?php echo htmlspecialchars($scheduleFormatted[$timeSlot][$day]['name'] ?? ''); ?>
                </td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
