<?php
// Function to handle admin login
function loginAdmin($pdo, $email, $password) {
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure password is verified correctly
    if ($admin && password_verify($password, $admin['password'])) {
        return $admin;
    }
    return false;
}


// Function to check if user exists by email
function checkUserExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC); // Return user if exists
}

// Function to create a new user
function createUser($pdo, $first_name, $last_name, $email, $password) {
    $stmt = $pdo->prepare("INSERT INTO members (first_name, last_name, email, password, username) VALUES (?, ?, ?, ?, ?)");
    $username = strtolower($first_name) . '_' . strtolower($last_name); // Create a username
    return $stmt->execute([$first_name, $last_name, $email, $password, $username]);
}

// Function to login user by verifying email and password
function loginUser($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}
?>
