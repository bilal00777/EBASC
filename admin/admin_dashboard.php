<?php
session_start();
include '../includes/header.php';

// Ensure that admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // If no session for admin_id, redirect to admin login page
    header('Location: admin_login.php');
    exit();
}




// Admin is logged in, show the dashboard
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Welcome, Admin!</h1>
  
</body>
</html>
