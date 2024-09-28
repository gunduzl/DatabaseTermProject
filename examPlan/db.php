<?php
$servername = "localhost";
$user = "root";
$pass = "mysql";
$db = "examPlan";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $db :" . $e->getMessage());
}
?>
