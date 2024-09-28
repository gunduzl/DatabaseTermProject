<!DOCTYPE html>
<html>
<head>
    <title>Secretary Page</title>
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
        function loadDepartments(facultyId) {
            if (facultyId) {
                fetch('get_departments.php?faculty_id=' + facultyId)
                    .then(response => response.json())
                    .then(data => {
                        let departmentSelect = document.getElementById('department');
                        departmentSelect.innerHTML = '<option value="">Select Department</option>';
                        data.forEach(department => {
                            departmentSelect.innerHTML += `<option value="${department.id}">${department.name}</option>`;
                        });
                        // Clear the course dropdown
                        document.getElementById('course').innerHTML = '<option value="">Select Course</option>';
                        loadActiveExams('faculty'); // Load faculty-level exams by default
                    });
            }
        }

        function loadCourses(departmentId) {
            if (departmentId) {
                fetch('get_course.php?department_id=' + departmentId)
                    .then(response => response.json())
                    .then(data => {
                        let courseSelect = document.getElementById('course');
                        courseSelect.innerHTML = '<option value="">Select Course</option>';
                        data.forEach(course => {
                            courseSelect.innerHTML += `<option value="${course.id}">${course.name}</option>`;
                        });
                    });

                loadActiveExams(departmentId); // Load active exams for the selected department
            }
        }

        function loadActiveExams(scope) {
            let url = 'get_active_exams.php?scope=' + scope;
            fetch(url)
                .then(response => response.json())
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

        function validateForm() {
            let faculty = document.getElementById('faculty').value;
            let department = document.getElementById('department').value;
            let course = document.getElementById('course').value;
            let examName = document.getElementById('exam_name').value;
            let examDate = document.getElementById('exam_date').value;
            let startTime = document.getElementById('start_time').value;
            let endTime = document.getElementById('end_time').value;
            let numAssistants = document.getElementById('num_assistants').value;
            let numClasses = document.getElementById('num_classes').value;

            if (!faculty || !department || !course || !examName || !examDate || !startTime || !endTime || !numAssistants || !numClasses) {
                alert('All fields are required.');
                return false;
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', () => {
            let facultySelect = document.getElementById('faculty');
            let departmentSelect = document.getElementById('department');

            // Load faculty-level exams by default
            if (facultySelect.value) {
                loadDepartments(facultySelect.value);
            } else {
                loadActiveExams('faculty');
            }

            // Event listeners to update the exams table when department changes
            facultySelect.addEventListener('change', () => {
                loadDepartments(facultySelect.value);
            });

            departmentSelect.addEventListener('change', () => {
                loadCourses(departmentSelect.value);
            });
        });
    </script>
</head>
<body>
<h1>Welcome, Secretary!</h1>

<div class="container">
    <div class="half-width">
        <!-- Course Selection Dropdowns -->
        <form action="insert_exam.php" method="POST" onsubmit="return validateForm()">
            <div>
                <label for="faculty">Select Faculty:</label>
                <select id="faculty" name="faculty">
                    <option value="">Select Faculty</option>
                    <?php
                    require 'db.php';
                    $stmt = $pdo->query("SELECT * FROM faculties");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="department">Select Department:</label>
                <select id="department" name="department">
                    <option value="">Select Department</option>
                </select>
            </div>
            <div>
                <label for="course">Select Course:</label>
                <select id="course" name="course_id">
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
