<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Include database configuration
include '../config/config.php';
include '../includes/header.php';

// Initialize filter variables
$search_keyword = '';
$from_date = '';
$end_date = '';

// Check for filter input
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search_keyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
}

// Prepare SQL query with filters
$query = "
    SELECT 
        collections.id, 
        collections.heading, 
        collections.amount, 
        collections.category_id,
        COUNT(collection_members.id) AS total_members,
        collections.created_at
    FROM collections
    LEFT JOIN collection_members ON collections.id = collection_members.collection_id
    WHERE 1=1
";

// Add search keyword filter
if (!empty($search_keyword)) {
    $query .= " AND collections.heading LIKE :search_keyword";
}

// Add date range filters
if (!empty($from_date)) {
    $query .= " AND collections.created_at >= :from_date";
}
if (!empty($end_date)) {
    $query .= " AND collections.created_at <= :end_date";
}

$query .= " GROUP BY collections.id";

$stmt = $pdo->prepare($query);

// Bind parameters for filters
if (!empty($search_keyword)) {
    $stmt->bindValue(':search_keyword', '%' . $search_keyword . '%', PDO::PARAM_STR);
}
if (!empty($from_date)) {
    $stmt->bindValue(':from_date', $from_date, PDO::PARAM_STR);
}
if (!empty($end_date)) {
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
}

$stmt->execute();
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Collections List</h1>

    <!-- Filter Form -->
    <form method="GET" class="mb-4">
        <div class="row">
            <!-- Search Keyword -->
            <div class="col-md-4">
                <label for="search_keyword" class="form-label">Search Keyword</label>
                <input type="text" name="search_keyword" id="search_keyword" class="form-control" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="Enter heading">
            </div>

            <!-- From Date -->
            <div class="col-md-3">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control" value="<?php echo htmlspecialchars($from_date); ?>">
            </div>

            <!-- End Date -->
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>

            <!-- Filter Button -->
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Collections Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>ID</th>
                <th>Heading</th>
                <th>Amount</th>
                <th>Category ID</th>
                <th>Created At</th>
                <th>Total Members</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
       
            <?php if (!empty($collections)) :
                 $slNo = 1; ?>
                
                <?php foreach ($collections as $collection) : ?>
                    <tr>
                        <td><?php echo $slNo++; ?></td> <!-- Incremented Sl No -->
                        <td><?php echo htmlspecialchars($collection['id']); ?></td>
                        <td><?php echo htmlspecialchars($collection['heading']); ?></td>
                        <td><?php echo htmlspecialchars($collection['amount']); ?></td>
                        <td><?php echo htmlspecialchars($collection['category_id']); ?></td>
                        <td><?php echo htmlspecialchars($collection['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($collection['total_members']); ?></td>
                        <td>
                            <a href="view_collection.php?id=<?php echo $collection['id']; ?>" class="btn btn-info btn-sm">View List</a>
                            <a href="edit_collection.php?id=<?php echo $collection['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_collection.php?id=<?php echo $collection['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this collection?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="text-center">No collections found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
