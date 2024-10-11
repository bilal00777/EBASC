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
        header('Location: admin_dashboard.php');
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
    <title>Admin Login - Karthika's Aesthetic Center</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h1 {
            color: #9D2570;
            font-size: 2.2em;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #9D2570;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #B7305E;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .remember-checkbox input[type="checkbox"] {
            margin-right: 10px;
        }

        .error {
            color: red;
            margin-top: 10px;
            font-size: 14px;
        }

        a {
            color: #9D2570;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .card-footer {
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="card">
        <h1>EBASC</h1>

        <form method="post" action="admin_login.php" id="loginForm">
            <input type="email" name="email" id="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <div class="remember-checkbox">
                <label><input type="checkbox" id="rememberMe"> Remember Password</label>
                <a href="#">Forgot Password?</a>
            </div>

            <input type="submit" value="Login">
            <?php if (isset($error)) echo '<p class="error">' . $error . '</p>'; ?>
        </form>

        <div class="card-footer">
            <a href="#">Welcome EBASC's admin !</a>
        </div>
    </div>

    <script>
        // Function to check if 'Remember Me' was selected and store email in localStorage
        document.getElementById('loginForm').addEventListener('submit', function () {
            var rememberMe = document.getElementById('rememberMe').checked;
            var email = document.getElementById('email').value;

            if (rememberMe) {
                // Save email to localStorage
                localStorage.setItem('rememberedEmail', email);
            } else {
                // Clear the email from localStorage if 'Remember Me' is unchecked
                localStorage.removeItem('rememberedEmail');
            }
        });

        // On page load, check if email exists in localStorage and pre-fill the email field
        window.onload = function () {
            var rememberedEmail = localStorage.getItem('rememberedEmail');
            if (rememberedEmail) {
                document.getElementById('email').value = rememberedEmail;
                document.getElementById('rememberMe').checked = true;
            }
        };
    </script>

</body>
</html>
