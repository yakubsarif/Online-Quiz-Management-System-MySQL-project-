<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../index.php');
}

$message = '';

// Handle resource deletion
if (isset($_POST['delete']) && isset($_POST['resource_id'])) {
    $resource_id = $_POST['resource_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
        $stmt->execute([$resource_id]);
        $message = 'Resource deleted successfully!';
    } catch (PDOException $e) {
        $message = 'Error deleting resource: ' . $e->getMessage();
    }
}

// Get all resources
$stmt = $pdo->query("SELECT * FROM resources ORDER BY id DESC");
$resources = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manage Learning Resources</h1>
            <nav>
                <a href="index.php">Admin Dashboard</a>
                <a href="add_resource.php">Add New Resource</a>
                <a href="../index.php">Back to Site</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="actions">
                <a href="add_resource.php" class="btn btn-primary">Add New Resource</a>
            </div>

            <?php if (empty($resources)): ?>
                <p class="no-resources">No resources available.</p>
            <?php else: ?>
                <div class="resources-list">
                    <?php foreach ($resources as $resource): ?>
                        <div class="resource-item">
                            <div class="resource-info">
                                <div class="resource-icon">
                                    <?php if (strpos($resource['resource_url'], 'youtube.com') !== false || strpos($resource['resource_url'], 'youtu.be') !== false): ?>
                                        <span class="youtube-icon">Video</span>
                                    <?php elseif (strtolower(pathinfo($resource['resource_url'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                                        <span class="pdf-icon">PDF</span>
                                    <?php else: ?>
                                        <span class="link-icon">Link</span>
                                    <?php endif; ?>
                                </div>
                                <div class="resource-details">
                                    <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($resource['description']); ?></p>
                                    <div class="resource-meta">
                                        <small>URL: <a href="<?php echo htmlspecialchars($resource['resource_url']); ?>" target="_blank"><?php echo htmlspecialchars($resource['resource_url']); ?></a></small>
                                    </div>
                                </div>
                            </div>
                            <div class="resource-actions">
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this resource?')">
                                    <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
