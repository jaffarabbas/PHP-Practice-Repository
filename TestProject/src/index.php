<?php
$host = 'db';
$db = 'testdb';
$user = 'root';
$pass = 'secret';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "<h1>✅ Connected to MySQL successfully!</h1>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
} catch (PDOException $e) {
    echo "<h1>❌ Connection failed: " . $e->getMessage() . "</h1>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p><?php echo "Hello, world!" . $host; ?></p>
</body>
</html>