<?php
session_start(); // Make sure session_start() is the first thing in the script

// Check if the admin is logged in, if not, redirect to the login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit(); // Make sure to call exit() after header to stop script execution
}

// Include necessary files after session checks
include '../includes/header.php';
include '../config/config.php';



// Initialize variables for success and error messages
$success_message = "";
$error_message = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize inputs
    $company_name = htmlspecialchars($_POST['company_name']);
    $social_media_link = htmlspecialchars($_POST['social_media_link']);
    
    // Handle file upload for the logo
    $logo = $_FILES['logo']['name'];
    $logo_tmp = $_FILES['logo']['tmp_name'];
    
    // Set the upload directory to an absolute path
    $upload_dir = "admin/uploads/";
    
    // Check if the uploads directory exists, create it if not
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Set the full path of the file to be uploaded
    $upload_file = $upload_dir . basename($logo);
    
    if (!empty($logo)) {
        // Move the uploaded file to the upload directory
        if (move_uploaded_file($logo_tmp, $upload_file)) {
            // Insert the new sponsor details into the database
            $stmt = $pdo->prepare("INSERT INTO sponsors (company_name, logo, social_media_link) 
                                   VALUES (:company_name, :logo, :social_media_link)");
            $stmt->bindParam(':company_name', $company_name);
            $stmt->bindParam(':logo', $upload_file);
            $stmt->bindParam(':social_media_link', $social_media_link);
        } else {
            $error_message = "Error uploading the logo.";
        }
    } else {
        // If no logo is uploaded, insert sponsor without a logo
        $stmt = $pdo->prepare("INSERT INTO sponsors (company_name, social_media_link) 
                               VALUES (:company_name, :social_media_link)");
        $stmt->bindParam(':company_name', $company_name);
        $stmt->bindParam(':social_media_link', $social_media_link);
    }
    
    // Execute the SQL query
    if (empty($error_message) && $stmt->execute()) {
        $success_message = "New sponsor added successfully!";
    } else {
        $error_message = $error_message ?: "Error adding sponsor.";
    }
}
?>

<!-- The HTML form remains the same, only changes in PHP logic -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Sponsor</title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Custom Input Field Styles from Uiverse.io */
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
        
        .input:active {
            transform: scale(0.95);
        }
        
        .input:focus {
            border: 2px solid grey;
        }
        
        /* Additional Styling for Row Alignment */
        .input-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        
        .input-row .form-control {
            max-width: 220px;
        }

        /* Hide messages after a few seconds */
        .fade-out {
            opacity: 1;
            transition: opacity 3s linear;
        }
        .fade-out-hidden {
            opacity: 0;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Add New Sponsor</h1>
    
    <!-- Display Success and Error Messages -->
    <?php if (!empty($success_message)) : ?>
        <div id="successMessage" class="alert alert-success fade-out">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)) : ?>
        <div id="errorMessage" class="alert alert-danger fade-out">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Form Start -->
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- Company Name Input (Left Side) -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="company_name" class="form-label">Company Name:</label>
                    <input type="text" name="company_name" id="company_name" class="form-control input" required>
                </div>
            </div>

            <!-- Social Media Link Input (Right Side) -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="social_media_link" class="form-label">Social Media Link (optional):</label>
                    <input type="text" name="social_media_link" id="social_media_link" class="form-control input">
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sponsor Logo Input (Full Width) -->
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="logo" class="form-label">Sponsor Logo (optional):</label>
                    <input type="file" name="logo" id="logo" class="form-control input">
                </div>
            </div>
        </div>

        <!-- Submit Button (Full Width) -->
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Add Sponsor</button>
            </div>
        </div>
    </form>
</div>

 
<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Auto-hide success/error messages after 3 seconds
    setTimeout(function() {
        const successMessage = document.getElementById("successMessage");
        const errorMessage = document.getElementById("errorMessage");
        if (successMessage) {
            successMessage.classList.add("fade-out-hidden");
        }
        if (errorMessage) {
            errorMessage.classList.add("fade-out-hidden");
        }
    }, 3000);
</script>


<script>
    document.getElementById('logo').addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            const preview = document.createElement('img');
            preview.src = URL.createObjectURL(file);
            preview.style.maxWidth = '200px';
            preview.style.marginTop = '10px';
            
            // Remove any previous preview
            const existingPreview = document.getElementById('logoPreview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            // Append the preview image after the file input
            preview.id = 'logoPreview';
            event.target.parentElement.appendChild(preview);
        }
    });
</script>

</body>
</html>

<?php
// Close the PDO connection (optional, but good practice)
$pdo = null;
?>
