<?php
// Include config.php for database connection
include '../config/config.php';
include '../includes/header.php';

// Initialize variables for success and error messages
$success_message = "";
$error_message = "";

// Pagination configuration
$limit = 10; // Limit the number of rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Count total sponsors for pagination
$total_stmt = $pdo->query("SELECT COUNT(*) as total FROM sponsors");
$total_sponsors = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_sponsors / $limit);

// Fetch sponsors with pagination
$stmt = $pdo->prepare("SELECT * FROM sponsors LIMIT :start, :limit");
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sponsors</title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Add any custom styles if needed */
    </style>
</head>
<body>
<div class="container mt-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Sponsors</li>
        </ol>
    </nav>

    <h1 class="mb-4">Manage Sponsors</h1>

    <!-- Table of Sponsors -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Company Name</th>
                <th>Logo</th>
                <th>Social Media Link</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sponsors) > 0) : ?>
                <?php foreach ($sponsors as $sponsor) : ?>
                    <tr>
                        <td><?php echo $sponsor['id']; ?></td>
                        <td><?php echo htmlspecialchars($sponsor['company_name']); ?></td>
                        <td>
                            <?php if ($sponsor['logo']) : ?>
                                <img src="<?php echo $sponsor['logo']; ?>" alt="Logo" style="height: 50px;">
                            <?php else : ?>
                                No logo
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($sponsor['social_media_link']) : ?>
                                <a href="<?php echo htmlspecialchars($sponsor['social_media_link']); ?>" target="_blank">
                                    View Link
                                </a>
                            <?php else : ?>
                                No Link
                            <?php endif; ?>
                        </td>
                        <td>
    <button type="button" class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo $sponsor['id']; ?>)">Edit</button>
</td><td>
    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $sponsor['id']; ?>)">Delete</button>
</td>

                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6" class="text-center">No sponsors found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination Links -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1) : ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages) : ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>



<!-- Edit Sponsor Modal -->
<div class="modal fade" id="editSponsorModal" tabindex="-1" aria-labelledby="editSponsorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSponsorModalLabel">Edit Sponsor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="editSponsorForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<!-- Sponsor ID here -->">

    <div class="mb-3">
        <label for="edit_company_name" class="form-label">Company Name:</label>
        <input type="text" id="edit_company_name" name="company_name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="edit_social_media_link" class="form-label">Social Media Link:</label>
        <input type="text" id="edit_social_media_link" name="social_media_link" class="form-control">
    </div>

    <div class="mb-3">
        <label for="edit_logo" class="form-label">Sponsor Logo:</label>
        <input type="file" id="edit_logo" name="logo" class="form-control">
        <img id="logoPreviewModal" src="" alt="Logo Preview" style="max-width: 200px; margin-top: 10px; display: none;">
    </div>

    <!-- Success/Error Message -->
    <div id="edit_message" class="alert d-none"></div>
</form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="submitEditSponsor">Update Sponsor</button>
            </div>
        </div>
    </div>
</div>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this sponsor?</p>
                <input type="hidden" id="deleteSponsorId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-danger" onclick="deleteSponsor()">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEditModal(id) {
    document.querySelector('#editSponsorForm input[name="id"]').value = id;

    // Make an AJAX request to fetch sponsor data
    fetch('get_sponsor_data.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            // Set current sponsor data in the modal form fields
            document.getElementById('edit_company_name').value = data.company_name;
            document.getElementById('edit_social_media_link').value = data.social_media_link;
            
            // If there's a logo, display it
            const logoPreviewModal = document.getElementById('logoPreviewModal');
            if (data.logo) {
                logoPreviewModal.src = data.logo;  // Path is already stored in the database
                logoPreviewModal.style.display = 'block'; // Show the preview
            } else {
                logoPreviewModal.style.display = 'none'; // Hide the preview if no logo is set
            }

            // Show the modal
            const editSponsorModal = new bootstrap.Modal(document.getElementById('editSponsorModal'));
            editSponsorModal.show();
        })
        .catch(error => {
            console.error('Error fetching sponsor data:', error);
        });
}

document.getElementById('edit_logo').addEventListener('change', function(event) {
    const [file] = event.target.files;
    const logoPreviewModal = document.getElementById('logoPreviewModal');

    if (file) {
        const objectURL = URL.createObjectURL(file);
        logoPreviewModal.src = objectURL;
        logoPreviewModal.style.display = 'block'; // Show the preview
    } else {
        logoPreviewModal.style.display = 'none'; // Hide the preview if no file is selected
    }
});

document.getElementById('submitEditSponsor').addEventListener('click', function(event) {
    event.preventDefault();

    const form = document.getElementById('editSponsorForm');
    const formData = new FormData(form); // Collect form data, including the file

    fetch('update_sponsor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('edit_message');
        if (data.success) {
            messageDiv.classList.remove('d-none', 'alert-danger');
            messageDiv.classList.add('alert-success');
            messageDiv.innerHTML = 'Sponsor updated successfully!';
        } else {
            messageDiv.classList.remove('d-none', 'alert-success');
            messageDiv.classList.add('alert-danger');
            messageDiv.innerHTML = 'Error updating sponsor.';
        }
    })
    .catch(error => {
        console.error('Error updating sponsor:', error);
    });
});

</script>



<script>
    function confirmDelete(id) {
    // Set the sponsor ID to the hidden input in the modal
    document.getElementById('deleteSponsorId').value = id;
    // Show the modal
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    deleteModal.show();
}

function deleteSponsor() {
    // Get the sponsor ID from the hidden input
    const sponsorId = document.getElementById('deleteSponsorId').value;

    // Make an AJAX request to delete the sponsor
    fetch('delete_sponsor.php?id=' + sponsorId, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Sponsor successfully deleted, reload the page to update the list
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting sponsor:', error);
        alert('An error occurred while trying to delete the sponsor.');
    });
}

</script>
</body>
</html>

<?php
// Close the PDO connection
$pdo = null;
?>
