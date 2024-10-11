<?php
session_start(); // Ensure session is started
include __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Assuming this function correctly checks admin login credentials
    $admin = loginAdmin($pdo, $email, $password);

    if ($admin) {
        // If login is successful, set session variable
        $_SESSION['admin_id'] = $admin['id'];
        header('Location:admin_dashboard.php ');
        exit();
    } else {
        // Handle invalid credentials
        $error = "Invalid admin credentials.";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Admin Login</h1>
    <form method="post" action="admin_login.php">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Login">
    </form>
    <?php if (isset($error)) echo '<p>' . $error . '</p>'; ?>
</body>
</html>
