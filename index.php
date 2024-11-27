<?php
session_start();
include 'config/config.php'; // Database connection
include 'includes/function.php'; // Helper functions

$signup_error = ''; // Variable for sign-up error message
$success = ''; // Variable for success message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Login form handling
        $email = $_POST['email'];
        $password = $_POST['password'];

        $user = loginUser($pdo, $email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header('Location: user/userdashboard.php');
            exit();
        } else {
            $error = "Invalid email or password.";
            echo "<script>
            window.addEventListener('load', function() {
                var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
                myModal.show();
            });
            </script>";
        }
    } elseif (isset($_POST['signup'])) {
        // Sign-up form handling
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['signup_email'];
        $password = password_hash($_POST['signup_password'], PASSWORD_DEFAULT); // Hash password

        // Check if user already exists
        $userExists = checkUserExists($pdo, $email);

        if ($userExists) {
            $signup_error = "User already exists with this email.";
        } else {
            // Insert the new user
            $userCreated = createUser($pdo, $first_name, $last_name, $email, $password);

            if ($userCreated) {
                // Success: Redirect to a new modal
                $success = "User registered successfully.";
                echo "<script>
                    window.addEventListener('load', function() {
                        var myModal = new bootstrap.Modal(document.getElementById('successModal'));
                        myModal.show();
                    });
                    </script>";
            } else {
                $signup_error = "Error occurred during registration.";
            }
        }
    }
}


// Fetch images from the database
$sql = "SELECT id, title, image_path FROM photogalleryone ORDER BY created_at DESC";
$stmt = $pdo->query($sql);

// Fetch all images as an associative array
$images = $stmt->fetchAll(PDO::FETCH_ASSOC); 

// Fetch images from the database
$sql2 = "SELECT id, title, image_path FROM photogallerytwo ORDER BY created_at DESC";
$stmt = $pdo->query($sql2);

// Fetch all images as an associative array
$images2 = $stmt->fetchAll(PDO::FETCH_ASSOC);  


// Fetch images from the database
$sql3 = "SELECT id, title, image_path FROM photogallerythree ORDER BY created_at DESC";
$stmt = $pdo->query($sql3);

// Fetch all images as an associative array
$images3 = $stmt->fetchAll(PDO::FETCH_ASSOC);  

// Fetch images from the database
$sql4 = "SELECT id, company_name, logo,social_media_link FROM sponsors ";
$stmt = $pdo->query($sql4);

// Fetch all images as an associative array
$sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erattil Brothers Arts and Sports Club</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

  <!-- style -->
  <link rel="stylesheet" href="style.css">
    
</head>
  <style>
    /* Navbar collapsed (hidden) */
.navbar-collapsed {
    top: -79px; /* Height of the navbar */
}
  </style>
<body>
    <div class="logo-container" id="logoContainer">
        <img src="logo/ebasc logo.png" alt="Erattil Brothers Logo" class="logo">
    </div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top">
    <div class="container-fluid">
      <!-- Left Side: Logo -->
      <a class="navbar-brand" href="#">
        <img src="logo/ebasc logo.png" alt="ebasc">
       <span class="nav_ebasc">EBASC</span> 
      </a>

      <!-- Navbar Toggler for mobile -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <!-- Right Side: Navigation Links -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link active text-white" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="#">Features</a>
          </li>
         
        
          <li class="nav-item">
            <!-- Login Link triggers the modal -->
            <a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#authModal">Login</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>


<!-- Authentication Modal (Login/Sign-up) -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Login Form -->
                    <form id="loginForm" method="post" action="index.php">
                    <img src="logo/ebasc logo.png" alt="ebasc logo" style='height:100px;width:100px;'>
                    <h3 class='cap-log'>Log in to unlock your journey!</h3>
                        <div class="custom-group">
                            <input required="" type="email" name="email" class="custom-input" id="loginEmail">
                            <span class="custom-highlight"></span>
                            <span class="custom-bar"></span>
                            <label class="custom-label" for="loginEmail">Email address</label>
                        </div>
                        <div class="custom-group">
                            <input required="" type="password" name="password" class="custom-input" id="loginPassword">
                            <span class="custom-highlight"></span>
                            <span class="custom-bar"></span>
                            <label class="custom-label" for="loginPassword">Password</label>
                        </div>
                        <div class="mb-3 text-end">
                            <a href="#" class="small">Forgotten password?</a>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>

                    <?php if (isset($error)) echo '<p>' . $error . '</p>'; ?>

                    <!-- Sign-up Form (Initially hidden) -->
                    <form id="signUpForm" method="post" action="index.php" style="display:none;">
                        <img src="logo/ebasc logo.png" alt="ebasc logo" style='height:100px;width:100px;'>
                        <h3 class='cap-log'>Join us today and start something amazing!</h3>
                        <div class="custom-group">
                            <input required="" type="text" name="first_name" class="custom-input" id="signUpFirstName">
                            <span class="custom-highlight"></span>
                            <span class="custom-bar"></span>
                            <label class="custom-label" for="signUpFirstName">First Name</label>
                        </div>
                        <div class="custom-group">
                            <input required="" type="text" name="last_name" class="custom-input" id="signUpLastName">
                            <span class="custom-highlight"></span>
                            <span class="custom-bar"></span>
                            <label class="custom-label" for="signUpLastName">Last Name</label>
                        </div>
                        <div class="custom-group">
                            <input required="" type="email" name="signup_email" class="custom-input" id="signUpEmail">
                            <span class="custom-highlight"></span>
                            <span class="custom-bar"></span>
                            <label class="custom-label" for="signUpEmail">Email address</label>
                        </div>
                        <div class="custom-group">
                            <input required="" type="password" name="signup_password" class="custom-input" id="signUpPassword">
                            <span class="custom-highlight"></span>
                            <span class="custom-bar"></span>
                            <label class="custom-label" for="signUpPassword">Password</label>
                        </div>
                        <br>
                        <button type="submit" name="signup" class="btn btn-success w-100">Sign Up</button>
                    </form>

                    <?php if ($signup_error) echo '<p class="text-danger">' . $signup_error . '</p>'; ?>
                </div>
                <div class="modal-footer">
                    <p class="w-100 text-center">
                        <span id="formSwitchText">Don't have an account? </span>
                        <a href="#" id="formSwitchLink" class="btn btn-log w-100">Sign up</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal (To show success message after sign-up) -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success" id="successModalLabel">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p style='color:black'><?php echo $success; ?></p>
                    <button class="btn btn-primary" id="backToLogin">Back to Login</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success" id="successModalLabel">error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p style='color:black'><?php echo $error; ?></p>
                    <button class="btn btn-primary" id="backToLogin_from">try again..</button>
                </div>
            </div>
        </div>
    </div>








    <div class="container hero_section content">
       
       
        <h1 class="wow animate__fadeInUp" data-wow-duration="1s">ERATTIL BROTHERS ARTS AND SPORTS CLUB</h1>
        <p class="wow animate__fadeInUp" data-wow-duration="1.3s"> 
            Unleash your talents in our club, where we blend art, sports, and charity.<br>
            Whether you paint or play, your passion can help us support local causes and<br>
            bring about meaningful change.
        </p>
        <div   class="social_icon wow animate__fadeInUp" data-wow-duration="1s">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 106 106" fill="none">
                <g filter="url(#filter0_i_129_71)">
                  <circle cx="53" cy="53" r="53" fill="url(#paint0_linear_129_71)"/>
                </g>
                <circle cx="53" cy="53" r="52.5" stroke="black"/>
                <path d="M62.5717 41.3041H68V34.3081C65.3718 34.0998 62.731 33.997 60.0885 34.0001C52.2348 34.0001 46.8643 37.6521 46.8643 44.34V50.104H38V57.936H46.8643V78H57.4899V57.936H66.3253L67.6535 50.104H57.4899V45.1101C57.4899 42.8001 58.2984 41.3041 62.5717 41.3041Z" fill="#1250AD"/>
                <defs>
                  <filter id="filter0_i_129_71" x="0" y="-7" width="125.4" height="113" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                    <feMorphology radius="8" operator="dilate" in="SourceAlpha" result="effect1_innerShadow_129_71"/>
                    <feOffset dx="30" dy="-7"/>
                    <feGaussianBlur stdDeviation="13.7"/>
                    <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                    <feBlend mode="normal" in2="shape" result="effect1_innerShadow_129_71"/>
                  </filter>
                  <linearGradient id="paint0_linear_129_71" x1="97.267" y1="42.4602" x2="22.5852" y2="81.9091" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#F5BB28"/>
                    <stop offset="1" stop-color="white"/>
                  </linearGradient>
                </defs>
              </svg>


              <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 106 106" fill="none">
                <g filter="url(#filter0_i_129_81)">
                  <circle cx="53" cy="53" r="53" fill="url(#paint0_linear_129_81)"/>
                </g>
                <circle cx="53" cy="53" r="52.5" stroke="black"/>
                <path d="M61.8706 57.2185C61.4888 57.0337 59.0072 55.9251 58.6254 55.7403C58.2436 55.5556 57.8618 55.5556 57.48 55.9251C57.0982 56.2946 56.3347 57.4032 55.9529 57.7728C55.762 58.1423 55.3802 58.1423 54.9984 57.9576C53.6621 57.4032 52.3259 56.6642 51.1805 55.7403C50.226 54.8165 49.2716 53.7079 48.508 52.5993C48.3171 52.2297 48.508 51.8602 48.6989 51.6754C48.8898 51.4906 49.0807 51.1211 49.4625 50.9363C49.6533 50.7516 49.8442 50.382 49.8442 50.1973C50.0351 50.0125 50.0351 49.6429 49.8442 49.4582C49.6533 49.2734 48.6989 47.0562 48.3171 46.1323C48.1262 44.839 47.7444 44.839 47.3626 44.839H46.4081C46.0264 44.839 45.4537 45.2085 45.2628 45.3933C44.1174 46.5019 43.5447 47.7953 43.5447 49.2734C43.7356 50.9363 44.3083 52.5993 45.4537 54.0774C47.5535 57.0337 50.226 59.4357 53.4712 60.9139C54.4257 61.2834 55.1893 61.6529 56.1438 61.8377C57.0982 62.2072 58.0527 62.2072 59.1981 62.0225C60.5343 61.8377 61.6797 60.9139 62.4433 59.8052C62.8251 59.0662 62.8251 58.3271 62.6342 57.588L61.8706 57.2185ZM66.643 40.4045C59.1981 33.1985 47.1717 33.1985 39.7268 40.4045C33.6182 46.3171 32.4728 55.3708 36.6725 62.5768L34 72L44.1174 69.4132C46.9808 70.8914 50.0351 71.6305 53.0894 71.6305C63.5886 71.6305 71.988 63.5006 71.988 53.3383C72.1789 48.5343 70.0791 43.9151 66.643 40.4045ZM61.4888 66.2722C59.0072 67.7503 56.1438 68.6742 53.0894 68.6742C50.226 68.6742 47.5535 67.9351 45.0719 66.6417L44.4992 66.2722L38.5815 67.7503L40.1086 62.2072L39.7268 61.6529C35.1454 54.2622 37.4361 45.0237 44.881 40.4045C52.3259 35.7853 61.8706 38.1873 66.4521 45.2085C71.0335 52.4145 68.9337 61.8377 61.4888 66.2722Z" fill="#38D754"/>
                <defs>
                  <filter id="filter0_i_129_81" x="0" y="-7" width="125.4" height="113" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                    <feMorphology radius="8" operator="dilate" in="SourceAlpha" result="effect1_innerShadow_129_81"/>
                    <feOffset dx="30" dy="-7"/>
                    <feGaussianBlur stdDeviation="13.7"/>
                    <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                    <feBlend mode="normal" in2="shape" result="effect1_innerShadow_129_81"/>
                  </filter>
                  <linearGradient id="paint0_linear_129_81" x1="97.267" y1="42.4602" x2="22.5852" y2="81.9091" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#F5BB28"/>
                    <stop offset="1" stop-color="white"/>
                  </linearGradient>
                </defs>
              </svg>


              <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 106 106" fill="none">
                <g filter="url(#filter0_i_129_82)">
                  <circle cx="53" cy="53" r="53" fill="url(#paint0_linear_129_82)"/>
                </g>
                <circle cx="53" cy="53" r="52.5" stroke="black"/>
                <path d="M61.311 45.536C60.9194 45.536 60.5366 45.6486 60.211 45.8596C59.8854 46.0706 59.6316 46.3704 59.4817 46.7212C59.3319 47.0721 59.2926 47.4581 59.369 47.8306C59.4454 48.203 59.634 48.5451 59.9109 48.8136C60.1878 49.0822 60.5406 49.265 60.9247 49.3391C61.3088 49.4132 61.7069 49.3752 62.0687 49.2298C62.4305 49.0845 62.7397 48.8384 62.9573 48.5227C63.1749 48.207 63.291 47.8357 63.291 47.456C63.291 46.9468 63.0824 46.4584 62.7111 46.0984C62.3397 45.7383 61.8361 45.536 61.311 45.536ZM68.901 49.408C68.8689 48.0805 68.6125 46.767 68.142 45.52C67.7225 44.453 67.0695 43.4868 66.228 42.688C65.411 41.8679 64.4123 41.2387 63.3075 40.848C62.0249 40.3779 60.6689 40.1235 59.298 40.096C57.549 40 56.988 40 52.5 40C48.012 40 47.451 40 45.702 40.096C44.3311 40.1235 42.9751 40.3779 41.6925 40.848C40.5898 41.2426 39.5919 41.8713 38.772 42.688C37.9263 43.4803 37.2774 44.4487 36.8745 45.52C36.3897 46.7637 36.1274 48.0786 36.099 49.408C36 51.104 36 51.648 36 56C36 60.352 36 60.896 36.099 62.592C36.1274 63.9214 36.3897 65.2362 36.8745 66.48C37.2774 67.5513 37.9263 68.5197 38.772 69.312C39.5919 70.1287 40.5898 70.7574 41.6925 71.152C42.9751 71.6221 44.3311 71.8765 45.702 71.904C47.451 72 48.012 72 52.5 72C56.988 72 57.549 72 59.298 71.904C60.6689 71.8765 62.0249 71.6221 63.3075 71.152C64.4123 70.7613 65.411 70.1321 66.228 69.312C67.0732 68.5161 67.7268 67.5491 68.142 66.48C68.6125 65.233 68.8689 63.9195 68.901 62.592C68.901 60.896 69 60.352 69 56C69 51.648 69 51.104 68.901 49.408ZM65.931 62.4C65.919 63.4156 65.7293 64.4219 65.37 65.376C65.1065 66.0723 64.6834 66.7015 64.1325 67.216C63.5973 67.7448 62.9498 68.1543 62.235 68.416C61.2511 68.7644 60.2134 68.9484 59.166 68.96C57.516 69.04 56.9055 69.056 52.566 69.056C48.2265 69.056 47.616 69.056 45.966 68.96C44.8785 68.9797 43.7956 68.8174 42.765 68.48C42.0815 68.2049 41.4637 67.7965 40.95 67.28C40.4023 66.766 39.9845 66.1363 39.729 65.44C39.3262 64.4722 39.1027 63.4431 39.069 62.4C39.069 60.8 38.97 60.208 38.97 56C38.97 51.792 38.97 51.2 39.069 49.6C39.0764 48.5617 39.2719 47.5327 39.6465 46.56C39.937 45.8847 40.3828 45.2827 40.95 44.8C41.4513 44.2499 42.0708 43.813 42.765 43.52C43.7708 43.1681 44.8308 42.9841 45.9 42.976C47.55 42.976 48.1605 42.88 52.5 42.88C56.8395 42.88 57.45 42.88 59.1 42.976C60.1474 42.9876 61.1851 43.1716 62.169 43.52C62.9188 43.7898 63.5918 44.2286 64.1325 44.8C64.6732 45.2915 65.0957 45.8924 65.37 46.56C65.7367 47.5343 65.9265 48.5628 65.931 49.6C66.0135 51.2 66.03 51.792 66.03 56C66.03 60.208 66.0135 60.8 65.931 62.4ZM52.5 47.792C50.8266 47.7952 49.1917 48.2792 47.8019 49.1831C46.4121 50.0869 45.3297 51.3699 44.6916 52.87C44.0535 54.3701 43.8882 56.0199 44.2167 57.6111C44.5451 59.2022 45.3526 60.6633 46.537 61.8096C47.7215 62.9559 49.2297 63.736 50.8712 64.0514C52.5127 64.3668 54.2138 64.2034 55.7595 63.5817C57.3052 62.9599 58.6262 61.9079 59.5556 60.5585C60.485 59.209 60.981 57.6227 60.981 56C60.9832 54.9202 60.7652 53.8506 60.3395 52.8527C59.9139 51.8549 59.289 50.9485 58.5008 50.1857C57.7126 49.4229 56.7767 48.8187 55.7469 48.4079C54.717 47.9971 53.6136 47.7878 52.5 47.792ZM52.5 61.328C51.4133 61.328 50.351 61.0155 49.4474 60.4301C48.5439 59.8446 47.8396 59.0125 47.4237 58.0389C47.0079 57.0654 46.8991 55.9941 47.1111 54.9606C47.3231 53.927 47.8464 52.9777 48.6148 52.2325C49.3832 51.4874 50.3622 50.98 51.4281 50.7744C52.4939 50.5688 53.5987 50.6743 54.6027 51.0776C55.6066 51.4808 56.4648 52.1637 57.0685 53.0399C57.6723 53.9161 57.9945 54.9462 57.9945 56C57.9945 56.6997 57.8524 57.3925 57.5763 58.0389C57.3001 58.6854 56.8954 59.2727 56.3852 59.7675C55.875 60.2622 55.2693 60.6547 54.6027 60.9224C53.936 61.1902 53.2215 61.328 52.5 61.328Z" fill="url(#paint1_linear_129_82)"/>
                <defs>
                  <filter id="filter0_i_129_82" x="0" y="-7" width="125.4" height="113" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                    <feMorphology radius="8" operator="dilate" in="SourceAlpha" result="effect1_innerShadow_129_82"/>
                    <feOffset dx="30" dy="-7"/>
                    <feGaussianBlur stdDeviation="13.7"/>
                    <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                    <feBlend mode="normal" in2="shape" result="effect1_innerShadow_129_82"/>
                  </filter>
                  <linearGradient id="paint0_linear_129_82" x1="97.267" y1="42.4602" x2="22.5852" y2="81.9091" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#F5BB28"/>
                    <stop offset="1" stop-color="white"/>
                  </linearGradient>
                  <linearGradient id="paint1_linear_129_82" x1="66.25" y1="42" x2="41.6723" y2="70.0858" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D5019E"/>
                    <stop offset="0.407345" stop-color="#F34D04"/>
                    <stop offset="1" stop-color="#F08E06"/>
                  </linearGradient>
                </defs>
              </svg>



              <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 106 106" fill="none">
                <g filter="url(#filter0_i_129_83)">
                  <circle cx="53" cy="53" r="53" fill="url(#paint0_linear_129_83)"/>
                </g>
                <circle cx="53" cy="53" r="52.5" stroke="black"/>
                <mask id="mask0_129_83" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="22" y="34" width="48" height="46">
                  <path d="M68.9134 34.7709L22.0547 34.0049L22.1445 79.219L69.0032 79.985L68.9134 34.7709Z" fill="white"/>
                </mask>
                <g mask="url(#mask0_129_83)">
                  <path d="M62.5547 43.8836L67.3974 43.9637L56.8388 55.4456L69.3141 71.5139L59.5687 71.3527L51.9182 61.606L43.202 71.082L38.3564 71.0018L49.6498 58.7206L37.6826 43.4722L47.6753 43.6375L54.5909 52.5451L62.5547 43.8836ZM60.9002 68.5805L63.5836 68.6249L46.2221 46.2609L43.3426 46.2132L60.9002 68.5805Z" fill="black"/>
                </g>
                <defs>
                  <filter id="filter0_i_129_83" x="0" y="-7" width="125.4" height="113" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                    <feMorphology radius="8" operator="dilate" in="SourceAlpha" result="effect1_innerShadow_129_83"/>
                    <feOffset dx="30" dy="-7"/>
                    <feGaussianBlur stdDeviation="13.7"/>
                    <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                    <feBlend mode="normal" in2="shape" result="effect1_innerShadow_129_83"/>
                  </filter>
                  <linearGradient id="paint0_linear_129_83" x1="97.267" y1="42.4602" x2="22.5852" y2="81.9091" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#F5BB28"/>
                    <stop offset="1" stop-color="white"/>
                  </linearGradient>
                </defs>
              </svg>
        </div>
    </div>

<br><br><br>
    <section class="container sponsors-section">
        <h2  class="wow animate__fadeInUp" data-wow-duration="1s">OUR TRUSTED SPONSORS</h2>
        <div class="sponsors-container" id="sponsorsContainer">
          


            <?php
            // Generate dynamic HTML for each image
            foreach ($sponsors as $image) {
                echo '<img src="admin/' . htmlspecialchars($image['logo']) . '" alt="' . htmlspecialchars($image['company_name']) . '" class="sponsor-logo">';
            }

            // Duplicate images for continuous effect
            foreach ($sponsors as $image) {
                echo '<img src="admin/' . htmlspecialchars($image['logo']) . '" alt="' . htmlspecialchars($image['company_name']) . '" " class="sponsor-logo">';
            }
            ?>
           
        </div>
    </section>




    <br><br><br>
    <section class="container feild-section">
        <h2  class="wow animate__fadeInUp" data-wow-duration="1s">Creating Champions: On the Field and in the Community</h2>
        <p  class="wow animate__fadeInUp" data-wow-duration="1.3s">Join Our Club to Excel in Sports, Explore the Arts, and Empower Lives Through Charity</p>

        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card wow animate__fadeInUp" data-wow-duration="1s">
                    <img src="images/ecl.jpg" class="card-img-top" alt="Erattil Football League">
                    <div class="card-body">
                        <h5 class="card-title">EPL</h5>
                        <p class="card-text">ERATTIL Premier League is a top-tier football league showcasing intense local talent and competition</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card wow animate__fadeInUp" data-wow-duration="1s">
                    <img src="images/epl.jpg" class="card-img-top" alt="Erattil Cricket League">
                    <div class="card-body">
                        <h5 class="card-title">ECL</h5>
                        <p class="card-text">The Erattil Cricket League showcases local talent, thrilling matches, and vibrant community engagement.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card wow animate__fadeInUp" data-wow-duration="1s">
                    <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Card Image">
                    <div class="card-body">
                        <h5 class="card-title">Card Title 3</h5>
                        <p class="card-text">Card Subtitle 3</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card wow animate__fadeInUp" data-wow-duration="1s">
                    <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Card Image">
                    <div class="card-body">
                        <h5 class="card-title">Card Title 4</h5>
                        <p class="card-text">Card Subtitle 4</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card wow animate__fadeInUp" data-wow-duration="1s">
                    <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Card Image">
                    <div class="card-body">
                        <h5 class="card-title">Card Title 5</h5>
                        <p class="card-text">Card Subtitle 5</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card wow animate__fadeInUp" data-wow-duration="1s">
                    <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Card Image">
                    <div class="card-body">
                        <h5 class="card-title">Card Title 6</h5>
                        <p class="card-text">Card Subtitle 6</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<br><br><br>


    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/caroo1.jpg" class="d-block w-100" alt="Erattil brothers">
                <div class="carousel-caption d-none d-md-block">
                    <h5>The Heart of Companionship</h5>
                    <p>Navigating the Journeys That Bind Us Together</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/carosel1.jpg" class="d-block w-100" alt="Erattil brothers">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Beyond Ordinary Bonds</h5>
                    <p>Weaving Moments of Joy and Trust</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/carooll.jpg" class="d-block w-100" alt="Erattil brothers">
                <div class="carousel-caption d-none d-md-block">
                    <h5>The Magic of Allies</h5>
                    <p>Piecing Together the Elements of Lasting Relationships</p>
                </div>
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only"></span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only"></span>
        </a>
    </div>
    

  

    <section class="container about-section">
        <h2  class="wow animate__fadeInUp" data-wow-duration="1s">About Us: Where Passion Meets Purpose</h2>
        <p  class="wow animate__fadeInUp" data-wow-duration="1.2s">Dedicated to Fostering Creativity, Athleticism, and Community Impact Through Art, Sports, and Charity</p>

       
        <div class="row">
            <div class="col-md-6">
                <img src="logo/ebasc logo.png" alt="ebasc Image" class="about-us-image wow animate__fadeIn" data-wow-duration="3s">
            </div>
            <div class="col-md-6 about-us-text">
                <h1  class="wow animate__fadeInUp" data-wow-duration="1s">EBASC</h1>
                <p  class="wow animate__fadeInUp" data-wow-duration="1.3s">Erattil Brothers Arts and Sports Club (EBASC) was established on September 7, 2013, with the vision of fostering unity, talent, and community spirit. Officially inaugurated by the esteemed Varkala Kahar, EBASC began its journey as a platform to promote arts, sports, and cultural activities in the region. Over the years, the club has become a vibrant hub for individuals of all ages to explore their potential and connect with others, upholding its founding principles of passion, teamwork, and excellence.</p>
                <p  class="wow animate__fadeInUp" data-wow-duration="1.5s">
                EBASC enhances the community by offering local youth opportunities in sports and arts, promoting fitness and creativity. It serves as a hub for cultural events, workshops, and social initiatives, building strong bonds among members. Through outreach and local development efforts, the club fosters community welfare and pride.</p>
            </div>
        </div>


        </section>



        <section class="container photo-section">
            <h2>Photo Gallery: Capturing Our Journey</h2>
            <p>Explore Moments of Creativity, Athleticism, and Compassion in Action</p>

            <div class="scrolling-logos">
        <div class="scrolling-logos-wrapper">
            <?php
            // Generate dynamic HTML for each image
            foreach ($images as $image) {
                echo '<img src="admin/uploads_photo_g/' . htmlspecialchars($image['image_path']) . '" alt="' . htmlspecialchars($image['title']) . '" style="object-fit:cover;">';
            }

            // Duplicate images for continuous effect
            foreach ($images as $image) {
                echo '<img src="admin/uploads_photo_g/' . htmlspecialchars($image['image_path']) . '" alt="' . htmlspecialchars($image['title']) . '" " style="object-fit:cover;">';
            }
            ?>
        </div>


            <div class="scrolling-logos2">
                <div class="scrolling-logos-wrapper2">
                <?php
            // Generate dynamic HTML for each image
            foreach ($images2 as $image) {
                echo '<img src="admin/uploads_photo_g/' . htmlspecialchars($image['image_path']) . '" alt="' . htmlspecialchars($image['title']) . '" style="object-fit:cover;">';
            }

            // Duplicate images for continuous effect
            foreach ($images2 as $image) {
                echo '<img src="admin/uploads_photo_g/' . htmlspecialchars($image['image_path']) . '" alt="' . htmlspecialchars($image['title']) . '" " style="object-fit:cover;">';
            }
            ?>
                </div>
            </div>


            <div class="scrolling-logos3">
                <div class="scrolling-logos-wrapper3">
                <?php
            // Generate dynamic HTML for each image
            foreach ($images3 as $image) {
                echo '<img src="admin/uploads_photo_g/' . htmlspecialchars($image['image_path']) . '" alt="' . htmlspecialchars($image['title']) . '" style="object-fit:cover;">';
            }

            // Duplicate images for continuous effect
            foreach ($images3 as $image) {
                echo '<img src="admin/uploads_photo_g/' . htmlspecialchars($image['image_path']) . '" alt="' . htmlspecialchars($image['title']) . '" " style="object-fit:cover;">';
            }
            ?></div>
            </div>
       </section>



       <section class="container contact-section">
        <h2>Contact Us: Let's Connect</h2>
        <p>We're Here to Answer Your Questions and Welcome You to Our Community</p>
        <div class="row">
            <div class="col-md-6">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3942.300186059696!2d76.81105627482579!3d8.851619491740731!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b05e744d836cc1d%3A0x8f186d2deb4194a6!2sEBASC%20CLUB%20Erattil!5e0!3m2!1sen!2sin!4v1725087508601!5m2!1sen!2sin" width="500" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>            </div>
            <div class="col-md-6 about-us-text">
                <h1>EBASC</h1>
                <p>For any inquiries or to learn more about our club, feel free to reach out to us via email at  or give us a call at . We're here to help and look forward to connecting with you!</p>
                <div class="addressws">
                    <p><a href="mailto:-erattilbrothersartsandsports@gmail.com" style="text-decoration: none;color:white;"><i class="bi bi-envelope-at-fill"></i> erattilbrothersartsandsports@gmail.com</a></p>
                    <p><a href="tel:-9207303397" style="text-decoration: none;color:white;"><i class="bi bi-telephone"></i> +91 9207303397 (club president)</a></p>
                    <p><a href="tel:-9746735615" style="text-decoration: none;color:white;"><i class="bi bi-telephone"></i> +91 9746735615 (club secretory)</a></p>

                </div>
            </div>
        </div>
    
      </section>


      <div class="container-fluid footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 text-center text-md-left">
                    <img src="logo/ebasc logo.png" alt="ebasc Logo" class="footer-logo">
                </div>
                <div class="col-md-4 text-center">
                    <h2 class="footer-heading">ERATTIL BROTHERS</h2>
                    <p class="footer-subheading">arts and sports club</p>
                    <div class="footer-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-right">
                    <p class="footer-copy">&copy; 2024 EBASC. All Rights Reserved.</p>
                    <p class="footer-copy">Developed by . bilal muhammad</p>

                </div>
            </div>
        </div>
    </div>

















    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <script>
    new WOW().init();
    </script>

    <script>
        window.addEventListener('scroll', function() {
            const logoContainer = document.getElementById('logoContainer');
            const scrollPosition = window.scrollY;
            const maxScroll = 200; // Adjust this value based on how fast you want the opacity to change

            // Calculate the new opacity based on scroll position
            let opacity = 1 - (scrollPosition / maxScroll);

            // Ensure opacity stays within bounds (0 to 1)
            if (opacity < 0) opacity = 0.4;
            if (opacity > 1) opacity = 1;

            // Apply the new opacity to the logo
            logoContainer.style.opacity = opacity;
        });
    </script>


<!-- ---------------------- -->

<!-- sponors logo -->

<!-- ---------------------- -->
<script>
    const container = document.getElementById('sponsorsContainer');
    const logos = Array.from(container.children);

    // Clone logos to make the scroll seamless
    logos.forEach(logo => {
        const clone = logo.cloneNode(true);
        container.appendChild(clone);
    });

    function animateLogos() {
        let offset = 0;
        const step = 1; // Change this value to control the scroll speed
        const containerWidth = container.offsetWidth;

        function stepScroll() {
            offset -= step;
            container.style.transform = `translateX(${offset}px)`;

            // Reset when the entire width is scrolled
            if (Math.abs(offset) >= containerWidth / 2) {
                offset = 0;
            }

            requestAnimationFrame(stepScroll);
        }

        requestAnimationFrame(stepScroll);
    }

    animateLogos();
</script>
<!-- -------------end---------------- -->

<script>
        // Script to switch between Login and Sign-up forms
        const loginForm = document.getElementById('loginForm');
        const signUpForm = document.getElementById('signUpForm');
        const formSwitchLink = document.getElementById('formSwitchLink');
        const formSwitchText = document.getElementById('formSwitchText');
        const authModalLabel = document.getElementById('authModalLabel');
        const backToLogin = document.getElementById('backToLogin');

        let isLogin = true;

        formSwitchLink.addEventListener('click', function (e) {
            e.preventDefault();
            if (isLogin) {
                // Switch to Sign-up form
                loginForm.style.display = 'none';
                signUpForm.style.display = 'block';
                formSwitchText.textContent = 'Already have an account? ';
                formSwitchLink.textContent = 'Login';
                authModalLabel.textContent = 'Sign Up';
            } else {
                // Switch to Login form
                signUpForm.style.display = 'none';
                loginForm.style.display = 'block';
                formSwitchText.textContent = "Don't have an account? ";
                formSwitchLink.textContent = 'Sign up';
                authModalLabel.textContent = 'Login';
            }
            isLogin = !isLogin; // Toggle form state
        });

        // Handle back to login after successful signup
        if (backToLogin) {
            backToLogin.addEventListener('click', function () {
                var authModal = new bootstrap.Modal(document.getElementById('authModal'));
                authModal.show(); // Show the login modal again
                var successModal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
                successModal.hide(); // Hide the success modal
                 });
        }


       
    </script>


<script>
    // Handle back to login after error modal is shown
    var backToLoginBtn = document.getElementById('backToLogin_from');
    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', function () {
            var authModal = new bootstrap.Modal(document.getElementById('authModal'));
            authModal.show(); // Show the login modal again
            var errorModal = bootstrap.Modal.getInstance(document.getElementById('errorModal'));
            errorModal.hide(); // Hide the error modal
        });
    }
</script>


<!-- navbar animation -->
 <script>
    // JavaScript to manage the navbar collapse on scroll behavior
document.addEventListener("DOMContentLoaded", function() {
    var navbar = document.querySelector('.navbar-custom');
    var lastScrollTop = 0;

    window.addEventListener('scroll', function() {
        var currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (currentScrollTop > lastScrollTop) {
            // User is scrolling down - hide the navbar
            navbar.classList.add('navbar-collapsed');
        } else {
            // User is scrolling up - show the navbar
            navbar.classList.remove('navbar-collapsed');
        }

        lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop; // Ensure lastScrollTop is never negative
    });
});

 </script>
 <!-- Bootstrap JS Bundle -->
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
