<?php
// Entry point for TAI ETU web app
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TAI ETU</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>Welcome to TAI ETU</h1>
    <p>This is the baseline PHP index page.</p>
    <a href="pages/login.php">Login</a> | <a href="pages/signup.php">Sign Up</a>
</body>
</html>
