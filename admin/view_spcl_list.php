<?php
session_start(); // Start session

// Check if the admin is logged in; if not, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Include config.php for database connection
include '../config/config.php';
include '../includes/header.php';

// Initialize messages
$success_message = "";
$error_message = "";

// Get collection_id from URL parameter
$collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;

// Fetch member names from the members table for the datalist
try {
    $members_stmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM members");
    $all_members = $members_stmt->fetchAll();
} catch (Exception $e) {
    $error_message = "Error fetching members: " . $e->getMessage();
}

// Handle form submission to add a new member
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_member'])) {
    $member_name = htmlspecialchars($_POST['member_name']);
    $promised_amount = floatval($_POST['promised_amount']);
    $member_id = null;

    if (empty($member_name) || $promised_amount <= 0) {
        $error_message = "Member name and promised amount are required.";
    } else {
        try {
            // Check if the member already exists in the 'members' table
            $stmt = $pdo->prepare("SELECT id FROM members WHERE CONCAT(first_name, ' ', last_name) = :member_name");
            $stmt->bindParam(':member_name', $member_name);
            $stmt->execute();
            $existing_member = $stmt->fetch();

            if ($existing_member) {
                $member_id = $existing_member['id'];
            }

            // Insert new member into 'society_members' table
            $stmt = $pdo->prepare("INSERT INTO society_members (member_name, promised_amount, collect_id, member_id) VALUES (:member_name, :promised_amount, :collect_id, :member_id)");
            $stmt->bindParam(':member_name', $member_name);
            $stmt->bindParam(':promised_amount', $promised_amount);
            $stmt->bindParam(':collect_id', $collection_id);
            $stmt->bindParam(':member_id', $member_id);
            $stmt->execute();

            $success_message = "Member added successfully!";
        } catch (Exception $e) {
            $error_message = "Error adding member: " . $e->getMessage();
        }
    }
}

// Fetch collection details
try {
    $stmt = $pdo->prepare("SELECT collection_name FROM collect_money_society WHERE id = :id");
    $stmt->bindParam(':id', $collection_id);
    $stmt->execute();
    $collection = $stmt->fetch();
} catch (Exception $e) {
    $error_message = "Error fetching collection: " . $e->getMessage();
}

// Fetch society members for the collection
try {
    $stmt = $pdo->prepare("SELECT * FROM society_members WHERE collect_id = :collect_id");
    $stmt->bindParam(':collect_id', $collection_id);
    $stmt->execute();
    $members = $stmt->fetchAll();
} catch (Exception $e) {
    $error_message = "Error fetching members: " . $e->getMessage();
}




?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Member List</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Member List for Collection: <?php echo htmlspecialchars($collection['collection_name']); ?></h1>

    <!-- Display Success and Error Messages -->
    <?php if (!empty($success_message)) : ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)) : ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
<!-- Add Member Form -->
<form method="POST" class="mb-4">
        <h3>Add New Member</h3>
        <div class="mb-3">
            <label for="member_name" class="form-label">Member Name:</label>
            <input list="member_names" name="member_name" id="member_name" class="form-control" required>
            <datalist id="member_names">
                <?php foreach ($all_members as $member) : ?>
                    <option value="<?php echo htmlspecialchars($member['full_name']); ?>">
                <?php endforeach; ?>
            </datalist>
        </div>
        <div class="mb-3">
            <label for="promised_amount" class="form-label">Promised Amount:</label>
            <input type="number" name="promised_amount" id="promised_amount" class="form-control" step="0.01" required>
        </div>
        <button type="submit" name="add_member" class="btn btn-primary">Add Member</button>
    </form>
    <!-- Members Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>ID</th>
                <th>Member Name</th>
                <th>Promised Amount</th>
                <th>Status</th>
                <th>Paid Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $slno = 1; 
            foreach ($members as $member) : ?>
                <tr>
                    <td><?php echo $slno++; ?></td>
                    <td><?php echo htmlspecialchars($member['id']); ?></td>
                    <!-- <td><?php echo htmlspecialchars($member['collect_id']); ?></td> -->
                    <td><?php echo htmlspecialchars($member['member_name']); ?></td>
                    <td><?php echo htmlspecialchars($member['promised_amount']); ?></td>
                    <td><?php echo htmlspecialchars($member['status']); ?></td>
                    <td><?php echo htmlspecialchars($member['paid_amount']); ?></td>
                    <td>
                    <a href="delete_spcl_member.php?id=<?php echo $member['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this member?');">Delete</a>
                        <button 
                            class="btn btn-warning btn-sm edit-btn" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editModal" 
                            data-id="<?php echo $member['id']; ?>"
                            data-collectid="<?php echo $member['collect_id']; ?>"
                            data-name="<?php echo htmlspecialchars($member['member_name']); ?>"
                            data-promise="<?php echo htmlspecialchars($member['promised_amount']); ?>"
                            data-status="<?php echo htmlspecialchars($member['status']); ?>"
                            data-paid="<?php echo htmlspecialchars($member['paid_amount']); ?>"
                        >
                            Edit
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm" action="process_update_member.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Member Contribution</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="member_id" id="memberId">
                        <input type="hidden" name="collect_id" id="collectId">
                        <div class="mb-3">
                            <label for="memberName" class="form-label">Member Name:</label>
                            <input type="text" id="memberName" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status:</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="paidAmount" class="form-label">Paid Amount:</label>
                            <input type="number" name="paid_amount" id="paidAmount" class="form-control" step="0.01" required>
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

    <!-- Bootstrap and JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <!-- Custom Script to Handle Modal Data Population -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const editButtons = document.querySelectorAll(".edit-btn");
            const memberIdInput = document.getElementById("memberId");
            const collectIdInput = document.getElementById("collectId");
            const memberNameInput = document.getElementById("memberName");
            const statusSelect = document.getElementById("status");
            const paidAmountInput = document.getElementById("paidAmount");

            // Attach a click event listener to each edit button
            editButtons.forEach(button => {
                button.addEventListener("click", function() {
                    const memberId = this.getAttribute("data-id");
                    const collectId = this.getAttribute("data-collectid");
                    const memberName = this.getAttribute("data-name");
                    const promisedAmount = this.getAttribute("data-promise");
                    const status = this.getAttribute("data-status");
                    const paidAmount = this.getAttribute("data-paid");

                    memberIdInput.value = memberId;
                    collectIdInput.value = collectId;
                    memberNameInput.value = memberName;
                    statusSelect.value = status;
                    paidAmountInput.value = status === "paid" ? promisedAmount : paidAmount;

                    statusSelect.addEventListener("change", function() {
                        paidAmountInput.value = this.value === "paid" ? promisedAmount : "";
                    });
                });
            });
        });
    </script>
</div>
</body>
</html>

