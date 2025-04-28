<?php
// db.php
$host = 'localhost';
$db   = 'ru_project';
$user = 'root';
$pass = ''; // Adjust if you have a DB password.

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database Connection failed: " . $e->getMessage();
    exit;
}
?>
