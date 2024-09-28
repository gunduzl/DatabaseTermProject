<?php
// Fetch departments
$stmt = $pdo->query("SELECT * FROM departments");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>View Exam Schedule</h3>
<form method="POST" action="view_schedule.php">
    <label>Department:</label>
    <select name="department_id">
        <?php foreach ($departments as $department): ?>
            <option value="<?php echo $department['id']; ?>"><?php echo $department['name']; ?></option>
        <?php endforeach; ?>
    </select><br>
    <button type="submit">View Schedule</button>
</form>
