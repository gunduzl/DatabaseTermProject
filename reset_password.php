<?php
session_start();
require 'db.php';

// Ensure the user is coming from the forgot password process
if (!isset($_SESSION['reset_user_id'])) {
    header('Location: forgot_password.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        $user_id = $_SESSION['reset_user_id'];

        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute(['password' => $new_password, 'id' => $user_id]);

        // Clear the reset session data
        unset($_SESSION['reset_user_id']);

        $message = "Password has been reset successfully. You can now <a href='login.php'>login</a> with your new password.";
    } else {
        $error = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
<h2>Reset Password</h2>
<form method="POST" action="">
    <label>New Password:</label>
    <input type="password" name="new_password" required><br>
    <label>Confirm Password:</label>
    <input type="password" name="confirm_password" required><br>
    <button type="submit">Reset Password</button>
</form>
<?php if (isset($message)) echo "<p>$message</p>"; ?>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
</body>
</html>
