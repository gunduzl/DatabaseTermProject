<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';


if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
<h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>
<?php
try {
    switch ($role) {
        case 'assistant':
            include 'assistant_page.php';
            break;
        case 'secretary':
            include 'secretary_page.php';
            break;
        case 'head_of_department':
            include 'head_department_page.php';
            break;
        case 'head_of_secretary':
            include 'head_secretary_page.php';
            break;
        case 'dean':
            include 'dean_page.php';
            break;
        default:
            echo "<p>Invalid role: " . htmlspecialchars($role) . "</p>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>
