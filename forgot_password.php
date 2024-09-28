<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['reset_user_id'] = $user['id']; // Store the user ID in session
        header('Location: reset_password.php');
        exit();
    } else {
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
<h2>Forgot Password</h2>
<form method="POST" action="">
    <label>Username:</label>
    <input type="text" name="username" required><br>
    <button type="submit">Submit</button>
</form>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
</body>
</html>
