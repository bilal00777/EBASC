<?php
// Include config.php for database connection
include '../config/config.php';
include '../includes/header.php';

// Initialize variables for success and error messages
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize inputs
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $phone_number = htmlspecialchars($_POST['phone_number']);
    $address = htmlspecialchars($_POST['address']);
    $role = htmlspecialchars($_POST['role']);
    $category = htmlspecialchars($_POST['category']);
    
    // Handle file upload for the photo
    $photo = $_FILES['photo']['name'];
    $photo_tmp = $_FILES['photo']['tmp_name'];

    // Set the upload directory to an absolute path
    $upload_dir =  "admin/upload_member/";
    
    // Check if the uploads directory exists, create it if not
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Set the full path of the file to be uploaded
    $upload_file = $upload_dir . basename($photo);

    // Validate form data
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone_number) || empty($address) || empty($role) || empty($category)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (!empty($photo) && !move_uploaded_file($photo_tmp, $upload_file)) {
        $error_message = "Error uploading the photo.";
    } else {
        // Insert the new member details into the database
        $stmt = $pdo->prepare("INSERT INTO members (first_name, last_name, email, phone_number, photo, address, role, category) 
                               VALUES (:first_name, :last_name, :email, :phone_number, :photo, :address, :role, :category)");
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':photo', $upload_file); // Save the full file path
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':category', $category);
        
        if ($stmt->execute()) {
            $success_message = "New member added successfully!";
        } else {
            $error_message = "Error adding member.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .input {
            width: 100%;
            height: 45px;
            padding: 12px;
            border-radius: 12px;
            border: 1.5px solid lightgrey;
            outline: none;
            transition: all 0.3s cubic-bezier(0.19, 1, 0.22, 1);
            box-shadow: 0px 0px 20px -18px;
        }
        .input:hover {
            border: 2px solid lightgrey;
            box-shadow: 0px 0px 20px -17px;
        }
        .input:focus {
            border: 2px solid grey;
        }
        /* Photo preview styling */
        #photoPreview {
            max-width: 200px;
            margin-top: 10px;
            display: none;
        }
        .fade-out {
            transition: opacity 2s ease-out;
            opacity: 1;
        }
        .fade-out.hide {
            opacity: 0;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Add New Member</h1>

    <!-- Display Success and Error Messages -->
    <?php if (!empty($success_message)) : ?>
        <div id="successMessage" class="alert alert-success fade-out">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)) : ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Member Form -->
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- First Name -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name:</label>
                    <input type="text" name="first_name" id="first_name" class="form-control input" required>
                </div>
            </div>

            <!-- Last Name -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" class="form-control input" required>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Email -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" id="email" class="form-control input" required>
                </div>
            </div>

            <!-- Phone Number -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number:</label>
                    <input type="text" name="phone_number" id="phone_number" class="form-control input" required>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Address -->
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="address" class="form-label">Address:</label>
                    <textarea name="address" id="address" class="form-control input" rows="3" required></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Photo Upload -->
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="photo" class="form-label">Upload Photo:</label>
                    <input type="file" name="photo" id="photo" class="form-control input">
                    <img id="photoPreview" alt="Photo Preview">
                </div>
            </div>
        </div>

        <div class="row">
        <!-- Role Dropdown -->
        <div class="col-md-6">
        <div class="mb-3">
            <label for="role" class="form-label">Role:</label>
            <select name="role" id="role" class="form-control input" required>
                <option value="member" selected>Member</option>
                <option value="president">President</option>
                <option value="vice president">Vice President</option>
                <option value="secretary">Secretary</option>
                <option value="joint secretary">Joint Secretary</option>
                <option value="treasurer">Treasurer</option>
                <option value="PRO">PRO</option>
                <option value="CEO">CEO</option>
            </select>
        </div>
    </div>

        <!-- Category Dropdown -->
        <div class="col-md-6">
        <div class="mb-3">
            <label for="category" class="form-label">Category:</label>
            <select name="category" id="category" class="form-control input" required>
                <option value="above 18 and in the country" selected>Above 18 and In the Country</option>
                <option value="above 18 and out of the country">Above 18 and Out of the Country</option>
                <option value="below 18 and in the country">Below 18 and In the Country</option>
                <option value="below 18 and out of the country">Below 18 and Out of the Country</option>
            </select>
        </div>
    </div>
    </div>


        <!-- Submit Button -->
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Add Member</button>
            </div>
        </div>
    </form>
</div>

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript for Photo Preview and Success Message Auto-Hide -->
<script>
    // Photo preview functionality
    document.getElementById('photo').addEventListener('change', function(event) {
        const [file] = event.target.files;
        const photoPreview = document.getElementById('photoPreview');

        if (file) {
            photoPreview.src = URL.createObjectURL(file);
            photoPreview.style.display = 'block';
        } else {
            photoPreview.style.display = 'none';
        }
    });

    // Auto-hide success message after 2 seconds
    window.onload = function() {
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(function() {
                successMessage.classList.add('hide');
            }, 2000); // Hide after 2 seconds
        }
    };
</script>
</body>
</html>
