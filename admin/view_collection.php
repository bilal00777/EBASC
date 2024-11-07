<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
// if (!isset($_SESSION['admin_id'])) {
//     http_response_code(403);
//     echo "Unauthorized";
//     exit();
// }

// Include database configuration
include '../config/config.php';
include '../includes/header.php';

// Get collection_id from URL
if (!isset($_GET['id'])) {
    echo "Collection ID not specified.";
    exit();
}
$collection_id = (int) $_GET['id'];

// Fetch collection details
$collection_query = "SELECT heading, amount, created_at FROM collections WHERE id = :collection_id";
$collection_stmt = $pdo->prepare($collection_query);
$collection_stmt->bindParam(':collection_id', $collection_id, PDO::PARAM_INT);
$collection_stmt->execute();
$collection = $collection_stmt->fetch(PDO::FETCH_ASSOC);

if (!$collection) {
    echo "Collection not found.";
    exit();
}




// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $member_id = (int) $_POST['member_id'];
//     $collection_id = (int) $_POST['collection_id'];
//     $status = $_POST['status'];
//     $paid_amount = (float) $_POST['paid_amount'];

//     $update_query = "UPDATE collection_members SET status = :status, paid_amount = :paid_amount WHERE member_id = :member_id AND collection_id = :collection_id";
//     $stmt = $pdo->prepare($update_query);
//     $stmt->bindParam(':status', $status, PDO::PARAM_STR);
//     $stmt->bindParam(':paid_amount', $paid_amount, PDO::PARAM_STR);
//     $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
//     $stmt->bindParam(':collection_id', $collection_id, PDO::PARAM_INT);

//     if ($stmt->execute()) {
//         echo json_encode(['status' => 'success']);
//     } else {
//         http_response_code(500);
//         echo json_encode(['status' => 'error', 'message' => 'Failed to update member']);
//     }
    
// }
// Fetch members related to this collection_id
$members_query = "
    SELECT members.id, collection_members.collection_id, collection_members.member_name, collection_members.status, collection_members.paid_amount
    FROM collection_members
    INNER JOIN members ON collection_members.member_id = members.id
    WHERE collection_members.collection_id = :collection_id
";
$members_stmt = $pdo->prepare($members_query);
$members_stmt->bindParam(':collection_id', $collection_id, PDO::PARAM_INT);
$members_stmt->execute();
$members = $members_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Collection Members</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
<div class="container mt-5">
    <h1 class="mb-4">Members List for Collection: <?php echo htmlspecialchars($collection['heading']); ?></h1>
    <p><strong>Collection Amount:</strong> <?php echo htmlspecialchars($collection['amount']); ?></p>
    <p><strong>Created At:</strong> <?php echo htmlspecialchars($collection['created_at']); ?></p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <!-- <th>Collection ID</th> -->
                <th>Member ID</th>
                <th>Member Name</th>
                <th>Status</th>
                <th>Paid Amount</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($members)) : ?>
                <?php foreach ($members as $member) : ?>
                    <tr>
                        <!-- <td><?php echo htmlspecialchars($member['collection_id']); ?></td> -->
                        <td><?php echo htmlspecialchars($member['id']); ?></td>
                        <td><?php echo htmlspecialchars($member['member_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['status']); ?></td>
                        <td><?php echo htmlspecialchars($member['paid_amount']); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" 
                                    data-member-id="<?php echo $member['id']; ?>" 
                                    data-collection-id="<?php echo $member['collection_id']; ?>" 
                                    data-member-name="<?php echo htmlspecialchars($member['member_name']); ?>" 
                                    data-status="<?php echo htmlspecialchars($member['status']); ?>" 
                                    data-paid-amount="<?php echo htmlspecialchars($member['paid_amount']); ?>" 
                                    data-collection-amount="<?php echo htmlspecialchars($collection['amount']); ?>">
                                Edit
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6" class="text-center">No members found for this collection.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="collection_list.php" class="btn btn-secondary">Back to Collection List</a>
</div>

<!-- Bootstrap Modal for Editing Member -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editMemberForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemberModalLabel">Edit Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="collectionId" name="collection_id">
                    <input type="hidden" id="memberId" name="member_id">
                    <div class="mb-3">
                        <label class="form-label">Member ID</label>
                        <input type="text" class="form-control" id="displayMemberId" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Member Name</label>
                        <input type="text" class="form-control" id="displayMemberName" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="paidAmount" class="form-label">Paid Amount</label>
                        <input type="number" class="form-control" id="paidAmount" name="paid_amount" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Open modal and populate data
        $('.edit-btn').on('click', function () {
            let memberId = $(this).data('member-id');
            let collectionId = $(this).data('collection-id');
            let memberName = $(this).data('member-name');
            let status = $(this).data('status');
            let paidAmount = $(this).data('paid-amount');
            let collectionAmount = $(this).data('collection-amount');

            $('#memberId').val(memberId);
            $('#collectionId').val(collectionId);
            $('#displayMemberId').val(memberId);
            $('#displayMemberName').val(memberName);
            $('#status').val(status);
            $('#paidAmount').val(paidAmount);

            $('#editMemberModal').modal('show');
        });

        // Automatically set paid amount if status is changed to "paid"
        $('#status').on('change', function () {
            if ($(this).val() === 'paid') {
                $('#paidAmount').val('<?php echo $collection['amount']; ?>');
            }
        });

        $('#editMemberForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: 'update_member_collection.php',  // Same page to handle the POST request
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            try {
                const res = JSON.parse(response);  // Parse the JSON response
                if (res.status === 'success') {
                    location.reload();  // Reload the page to see updated data
                } else {
                    alert(res.message || "Failed to update member.");  // Show error message if available
                }
            } catch (e) {
                alert("Unexpected response format.");  // Handle any JSON parsing errors
            }
        },
        error: function (xhr, status, error) {
            alert("An error occurred: " + error);
        }
    });
});

    });
</script>
</body>
</html>
