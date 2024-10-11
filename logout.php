<?php
session_start();
session_destroy();
$_SESSION = [];  // Clear all session variables

// Debugging: Check if session variables are empty
if (empty($_SESSION)) {
    echo "Session destroyed successfully.";
} else {
    echo "Failed to destroy session.";
}

// Redirect after the session is destroyed
header('Location: index.php');
exit();
?>
