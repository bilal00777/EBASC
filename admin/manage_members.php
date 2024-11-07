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


// Pagination setup
$limit = 10; // Limit the number of rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'desc' : 'asc'; // Set the order for sorting (default asc)

// Toggle sorting for SL No column
$order_next = ($order == 'asc') ? 'desc' : 'asc';

// Fetch total number of members
$total_stmt = $pdo->query("SELECT COUNT(*) as total FROM members");
$total_members = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_members / $limit);

// Fetch members from the database with sorting and pagination
$stmt = $pdo->prepare("SELECT * FROM members ORDER BY id $order LIMIT :start, :limit");
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Members</title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .table img {
            max-width: 50px;
            height: auto;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Members List</li>
        </ol>
    </nav>

    <h1 class="mb-4">Members List</h1>

    <!-- Table of Members -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>
                    <a href="?page=<?php echo $page; ?>&order=<?php echo $order_next; ?>">SL No</a>
                </th>
                <th>Member ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Username</th> <!-- New Column -->
                <!-- <th>Password</th> New Column (hashed or masked) -->
                <th>Role</th> <!-- New Column -->
                <th  style="width:20px;">catagory</th> <!-- New Column -->
                <th>Email</th>
                <th>Phone Number</th>
                <th>Photo</th>
                <th>Address</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($members) > 0) : ?>
                <?php 
                $slno = ($page - 1) * $limit + 1; // Initialize SL No based on page number
                foreach ($members as $member) : ?>
                    <tr>
                        <td><?php echo $slno++; ?></td>
                        <td><?php echo $member['id']; ?></td>
                        <td><?php echo htmlspecialchars($member['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['username']); ?></td> <!-- Username Column -->
                        <!-- <td><?php echo str_repeat('*', strlen($member['password'])); ?></td> Masked Password Column -->
                        <td><?php echo htmlspecialchars($member['role']); ?></td> <!-- Role Column -->
                        <td><?php echo htmlspecialchars($member['category']); ?></td> <!-- Role Column -->
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars($member['phone_number']); ?></td>
                        <td>
                            <?php if ($member['photo']) : ?>
                                <img src="<?php echo $member['photo']; ?>" alt="Photo">
                            <?php else : ?>
                                No Photo
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($member['address']); ?></td>
                        <td>
    <button type="button" class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo $member['id']; ?>)">Edit</button>
</td>

<td>
    <button type="button" class="btn btn-danger btn-sm" onclick="openDeleteModal(<?php echo $member['id']; ?>)">Delete</button>
</td>

                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="13" class="text-center">No members found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination Links -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1) : ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&order=<?php echo $order; ?>">Previous</a>
                </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&order=<?php echo $order; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages) : ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&order=<?php echo $order; ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>


<div>




<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editMemberModalLabel">Edit Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <!-- Alert div for success or error messages -->
        <!-- <div id="modalAlert" class="alert d-none"></div> -->

        <!-- Form for editing the member -->
        <form id="editMemberForm" enctype="multipart/form-data">
          <input type="hidden" id="member_id" name="id">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-control" id="role" name="role">
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
            <div class="col-md-6">
              <div class="mb-3">
                <label for="category" class="form-label">category</label>
                <select class="form-control" id="category" name="category">
                <option value="above 18 and in the country" selected>Above 18 and In the Country</option>
                <option value="above 18 and out of the country">Above 18 and Out of the Country</option>
                <option value="below 18 and in the country">Below 18 and In the Country</option>
                <option value="below 18 and out of the country">Below 18 and Out of the Country</option>
           
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="photo" class="form-label">Photo</label>
                <input type="file" class="form-control" id="photo" name="photo">
                <img id="photoPreview" src="" alt="Photo Preview" style="max-width: 100px; margin-top: 10px;">
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Alert Modal -->
<div id="alertModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="alertModalLabel">Notification</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-success" id="alertMessage">
        <!-- Dynamic alert message will appear here -->
      </div>
    </div>
  </div>
</div>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMemberModal" tabindex="-1" aria-labelledby="deleteMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteMemberModalLabel">Delete Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this member?</p>
        <input type="hidden" id="delete_member_id">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
      </div>
    </div>
  </div>
</div>



<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>



<script>
function openEditModal(memberId) {
    // Fetch member details via AJAX
    fetch('get_member_data.php?id=' + memberId)
        .then(response => response.json())
        .then(data => {
            // Populate the form with the fetched data
            document.getElementById('member_id').value = data.id;
            document.getElementById('first_name').value = data.first_name;
            document.getElementById('last_name').value = data.last_name;
            document.getElementById('username').value = data.username;
            document.getElementById('email').value = data.email;
            document.getElementById('phone_number').value = data.phone_number;
            document.getElementById('role').value = data.role;
            document.getElementById('category').value = data.category;
            document.getElementById('address').value = data.address;

            // Set the photo preview
            if (data.photo) {
                document.getElementById('photoPreview').src = data.photo;  // Direct path from the database
            } else {
                document.getElementById('photoPreview').src = '';  // Clear if no photo
            }

            // Show the modal
            var editMemberModal = new bootstrap.Modal(document.getElementById('editMemberModal'));
            editMemberModal.show();
        })
        .catch(error => {
            console.error('Error fetching member data:', error);
        });
}

// Handle image preview for new uploads
document.getElementById('photo').addEventListener('change', function(event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('photoPreview').src = URL.createObjectURL(file);
    } else {
        document.getElementById('photoPreview').src = ''; // Clear preview if no file
    }
});


document.getElementById('editMemberForm').addEventListener('submit', function(event) {
    event.preventDefault();  // Prevent the form from submitting the traditional way

    const formData = new FormData(this);  // Collect form data, including the file

    fetch('update_member.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Set alert message based on response
        const alertMessage = document.getElementById('alertMessage');
        const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
        
        if (data.success) {
            alertMessage.classList.remove('alert-danger');
            alertMessage.classList.add('alert-success');
            alertMessage.innerHTML = 'Member updated successfully!';
        } else {
            alertMessage.classList.remove('alert-success');
            alertMessage.classList.add('alert-danger');
            alertMessage.innerHTML = 'Error: ' + data.message;
        }
        
        // Show modal
        alertModal.show();
        
        // Close modal and optionally reload page after 2 seconds
        setTimeout(function() {
            alertModal.hide();
            if (data.success) {
                location.reload();
            }
        }, 3000);  // 2 seconds delay
    })
    .catch(error => {
        console.error('Error updating member:', error);

        const alertMessage = document.getElementById('alertMessage');
        alertMessage.classList.remove('alert-success');
        alertMessage.classList.add('alert-danger');
        alertMessage.innerHTML = 'Error: ' + error.message;

        const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
        alertModal.show();

        setTimeout(() => alertModal.hide(), 2000);  // Hide modal after 2 seconds
    });
});


</script>




<script>
    // Open delete confirmation modal and pass the member ID
function openDeleteModal(memberId) {
    document.getElementById('delete_member_id').value = memberId;  // Set the member ID in the hidden input
    const deleteMemberModal = new bootstrap.Modal(document.getElementById('deleteMemberModal'));
    deleteMemberModal.show();  // Show the modal
}

// Confirm delete and make the request to delete_member.php
function confirmDelete() {
    const memberId = document.getElementById('delete_member_id').value;

    // Make an AJAX request to delete the member
    fetch('delete_member.php?id=' + memberId, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Member deleted successfully!');
            location.reload();  // Reload the page to reflect the changes
        } else {
            alert('Error deleting member: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the member.');
    });
}

</script>
</body>
</html>
