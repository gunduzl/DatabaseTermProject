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

if (!$user || $user['role'] !== 'head_of_secretary') {
    die('User not found or you do not have the necessary permissions.');
}

// Get all faculties
$facultiesQuery = $pdo->query("SELECT id, name FROM faculties");
$faculties = $facultiesQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Head of Secretary Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        form > div {
            margin-bottom: 10px;
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
        .highlight {
            background-color: #ffcccc;
        }
        .container {
            display: flex;
            justify-content: space-between;
        }
        .half-width {
            width: 48%;
        }
    </style>
    <script>
        function logResponse(response) {
            console.log(response);
            return response;
        }

        function loadDepartments(facultyId, targetDepartmentSelectId, targetCourseSelectId) {
            if (facultyId) {
                fetch('get_departments.php?faculty_id=' + facultyId)
                    .then(response => response.json())
                    .then(logResponse)
                    .then(data => {
                        let departmentSelect = document.getElementById(targetDepartmentSelectId);
                        departmentSelect.innerHTML = '<option value="">No Department (Faculty-level courses)</option>';
                        data.forEach(department => {
                            departmentSelect.innerHTML += `<option value="${department.id}">${department.name}</option>`;
                        });
                        // Clear the course dropdown
                        document.getElementById(targetCourseSelectId).innerHTML = '<option value="">Select Course</option>';
                    }).catch(error => console.error('Error fetching departments:', error));
            }
        }

        function loadCourses(departmentId, targetCourseSelectId) {
            if (departmentId !== '') {
                fetch('get_course.php?department_id=' + departmentId)
                    .then(response => response.json())
                    .then(logResponse)
                    .then(data => {
                        let courseSelect = document.getElementById(targetCourseSelectId);
                        courseSelect.innerHTML = '<option value="">Select Course</option>';
                        data.forEach(course => {
                            courseSelect.innerHTML += `<option value="${course.id}">${course.name}</option>`;
                        });
                    }).catch(error => console.error('Error fetching courses:', error));

                loadActiveExams(departmentId); // Load active exams for the selected department
            } else {
                // Fetch faculty-level courses if no department is selected
                fetch('get_course.php?faculty_level=true')
                    .then(response => response.json())
                    .then(logResponse)
                    .then(data => {
                        let courseSelect = document.getElementById(targetCourseSelectId);
                        courseSelect.innerHTML = '<option value="">Select Course</option>';
                        data.forEach(course => {
                            courseSelect.innerHTML += `<option value="${course.id}">${course.name}</option>`;
                        });
                    }).catch(error => console.error('Error fetching faculty-level courses:', error));

                loadActiveExams('faculty'); // Load active exams for the faculty level
            }
        }

        function loadActiveExams(scope) {
            let url = 'get_active_exams.php?scope=' + scope;
            fetch(url)
                .then(response => response.json())
                .then(logResponse)
                .then(data => {
                    let examsTable = document.getElementById('active_exams');
                    examsTable.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(exam => {
                            examsTable.innerHTML += `
                                <tr>
                                    <td>${exam.name}</td>
                                    <td>${exam.exam_date}</td>
                                    <td>${exam.start_time}</td>
                                    <td>${exam.end_time}</td>
                                    <td>${exam.num_assistants}</td>
                                    <td>${exam.num_classes}</td>
                                </tr>
                            `;
                        });
                    } else {
                        examsTable.innerHTML = '<tr><td colspan="6">No active exams.</td></tr>';
                    }
                }).catch(error => console.error('Error fetching active exams:', error));
        }

        function validateForm(formId) {
            let form = document.getElementById(formId);
            let inputs = form.querySelectorAll('input, select');
            for (let input of inputs) {
                // all inputs are required except department
                if (input.value === '' && input.name !== 'department') {
                    alert('All fields are required.');
                    return false;
                }
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadActiveExams('faculty');
        });
    </script>
</head>
<body>
<h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>

<div class="container">
    <div class="half-width">
        <!-- Course Insertion Form -->
        <h2>Insert Course</h2>
        <form id="courseForm" action="insert_course.php" method="POST" onsubmit="return validateForm('courseForm')">
            <div>
                <label for="faculty">Select Faculty:</label>
                <select id="faculty" name="faculty" onchange="loadDepartments(this.value, 'department', 'course')">
                    <option value="">Select Faculty</option>
                    <?php foreach ($faculties as $faculty): ?>
                        <option value="<?php echo $faculty['id']; ?>"><?php echo htmlspecialchars($faculty['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="department">Select Department (optional):</label>
                <select id="department" name="department">
                    <option value="">No Department (Faculty-level courses)</option>
                </select>
            </div>
            <div>
                <label for="course_name">Course Name:</label>
                <input type="text" id="course_name" name="course_name" required>
            </div>
            <div>
                <label for="course_day">Course Day:</label>
                <select id="course_day" name="course_day" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>
            <div>
                <label for="start_time">Start Time:</label>
                <input type="time" id="start_time" name="start_time" required>
            </div>
            <div>
                <label for="end_time">End Time:</label>
                <input type="time" id="end_time" name="end_time" required>
            </div>
            <div>
                <input type="submit" value="Insert Course">
            </div>
        </form>

        <!-- Exam Insertion Form -->
        <h2>Insert Exam</h2>
        <form id="examForm" action="insert_exam.php" method="POST" onsubmit="return validateForm('examForm')">
            <div>
                <label for="faculty_exam">Select Faculty:</label>
                <select id="faculty_exam" name="faculty" onchange="loadDepartments(this.value, 'department_exam', 'course_exam')">
                    <option value="">Select Faculty</option>
                    <?php foreach ($faculties as $faculty): ?>
                        <option value="<?php echo $faculty['id']; ?>"><?php echo htmlspecialchars($faculty['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="department_exam">Select Department (optional):</label>
                <select id="department_exam" name="department" onchange="loadCourses(this.value, 'course_exam')">
                    <option value="">No Department (Faculty-level courses)</option>
                </select>
            </div>
            <div>
                <label for="course_exam">Select Course:</label>
                <select id="course_exam" name="course_id">
                    <option value="">Select Course</option>
                </select>
            </div>

            <!-- Exam Information Inputs -->
            <div>
                <label for="exam_name">Exam Name:</label>
                <input type="text" id="exam_name" name="exam_name" required>
            </div>
            <div>
                <label for="exam_date">Exam Date:</label>
                <input type="date" id="exam_date" name="exam_date" required>
            </div>
            <div>
                <label for="start_time">Start Time:</label>
                <input type="time" id="start_time" name="start_time" required>
            </div>
            <div>
                <label for="end_time">End Time:</label>
                <input type="time" id="end_time" name="end_time" required>
            </div>
            <div>
                <label for="num_assistants">Number of Assistants:</label>
                <input type="number" id="num_assistants" name="num_assistants" min="1" required>
            </div>
            <div>
                <label for="num_classes">Number of Classes:</label>
                <input type="number" id="num_classes" name="num_classes" min="1" required>
            </div>
            <div>
                <input type="submit" value="Add Exam">
            </div>
        </form>

        <!-- List Assistant Scores -->
        <h2>Assistant Scores</h2>
        <table>
            <tr>
                <th>Assistant Name</th>
                <th>Score</th>
            </tr>
            <?php
            $stmt = $pdo->query("SELECT name, score FROM assistants ORDER BY score ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr><td>{$row['name']}</td><td>{$row['score']}</td></tr>";
            }
            ?>
        </table>
    </div>

    <div class="half-width">
        <h2>Active Exams</h2>
        <table>
            <thead>
            <tr>
                <th>Exam Name</th>
                <th>Exam Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Number of Assistants</th>
                <th>Number of Classes</th>
            </tr>
            </thead>
            <tbody id="active_exams">
            <tr>
                <td colspan="6">No active exams.</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
