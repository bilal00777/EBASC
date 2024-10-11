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
                            <a href="edit_sponsor.php?id=<?php echo $sponsor['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        </td>
                        <td>
                            <a href="delete_sponsor.php?id=<?php echo $sponsor['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this sponsor?');">Delete</a>
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

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the PDO connection
$pdo = null;
?>
