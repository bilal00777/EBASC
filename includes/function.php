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

// Function to handle user login
function loginUser($pdo, $email, $password) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Function to check if a user already exists
function checkUserExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Function to create a new user
function createUser($pdo, $name, $email, $password) {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $email, $password]);
}
?>
