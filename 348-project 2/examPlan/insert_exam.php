<?php
session_start();
require 'db.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $exam_name = $_POST['exam_name'];
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $num_assistants = $_POST['num_assistants'];
    $num_classes = $_POST['num_classes'];

    // Validate form inputs
    if (!$course_id || !$exam_name || !$exam_date || !$start_time || !$end_time || !$num_assistants || !$num_classes) {
        echo "<script>
            alert('All fields are required.');
            window.location.href = document.referrer;
        </script>";
        exit();
    }

    try {
        // Insert exam into the database
        $stmt = $pdo->prepare("INSERT INTO exams (course_id, name, exam_date, start_time, end_time, num_assistants, num_classes) VALUES (:course_id, :name, :exam_date, :start_time, :end_time, :num_assistants, :num_classes)");
        $stmt->execute([
            'course_id' => $course_id,
            'name' => $exam_name,
            'exam_date' => $exam_date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'num_assistants' => $num_assistants,
            'num_classes' => $num_classes
        ]);

        // Get the inserted exam ID
        $exam_id = $pdo->lastInsertId();

        // Get the department_id of the course
        $courseQuery = $pdo->prepare("SELECT department_id FROM courses WHERE id = :course_id");
        $courseQuery->execute(['course_id' => $course_id]);
        $course = $courseQuery->fetch(PDO::FETCH_ASSOC);

        // If the department_id is NULL, we should check all assistants
        $department_id_condition = is_null($course['department_id']) ? "1=1" : "u.department_id = :department_id";

        // Check for conflicts and get least-scored assistants without schedule conflicts
        $stmt = $pdo->prepare("
            SELECT a.id, a.name, a.score
            FROM assistants a
            JOIN users u ON a.user_id = u.id
            WHERE ($department_id_condition)
            AND a.id NOT IN (
                SELECT assistant_id
                FROM assistant_courses ac
                JOIN course_schedules cs ON ac.course_id = cs.course_id
                WHERE cs.day_of_week = DAYNAME(:exam_date)
                AND (
                    (cs.start_time <= :start_time AND cs.end_time > :start_time)
                    OR (cs.start_time < :end_time AND cs.end_time >= :end_time)
                    OR (cs.start_time >= :start_time AND cs.end_time <= :end_time)
                )
            )
            ORDER BY a.score ASC
            LIMIT :num_assistants
        ");
        if (!is_null($course['department_id'])) {
            $stmt->bindValue(':department_id', $course['department_id'], PDO::PARAM_INT);
        }
        $stmt->bindValue(':exam_date', $exam_date);
        $stmt->bindValue(':start_time', $start_time);
        $stmt->bindValue(':end_time', $end_time);
        $stmt->bindValue(':num_assistants', $num_assistants, PDO::PARAM_INT);
        $stmt->execute();

        $assigned_assistants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($assigned_assistants) < $num_assistants) {
            echo "<script>
                alert('Not enough available assistants to cover this exam.');
                window.location.href = document.referrer;
            </script>";
            exit();
        }

        foreach ($assigned_assistants as $assistant) {
            // Insert exam assignment
            $stmt = $pdo->prepare("INSERT INTO exam_assignments (exam_id, assistant_id) VALUES (:exam_id, :assistant_id)");
            $stmt->execute([
                'exam_id' => $exam_id,
                'assistant_id' => $assistant['id']
            ]);

            // Update assistant's score
            $new_score = $assistant['score'] + 1;
            $stmt = $pdo->prepare("UPDATE assistants SET score = :score WHERE id = :assistant_id");
            $stmt->execute([
                'score' => $new_score,
                'assistant_id' => $assistant['id']
            ]);
        }

        // Redirect to the new page with exam details
        header('Location: exam_details.php?exam_id=' . $exam_id);
        exit();
    } catch (PDOException $e) {
        echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.location.href = document.referrer;
        </script>";
    }
}
?>
