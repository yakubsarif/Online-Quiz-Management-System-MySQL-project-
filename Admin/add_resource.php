<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../index.php');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $url = trim($_POST['resource_url']);
    
    if (empty($title) || empty($description) || empty($url)) {
        $message = 'All fields are required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO resources (title, description, resource_url) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description, $url]);
            $message = 'Resource added successfully!';
            
            // Clear form data
            $title = $description = $url = '';
        } catch (PDOException $e) {
            $message = 'Error adding resource: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resource - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Add Learning Resource</h1>
            <nav>
                <a href="index.php">Admin Dashboard</a>
                <a href="manage_resources.php">Manage Resources</a>
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

            <form method="POST" class="form">
                <div class="form-group">
                    <label for="title">Resource Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="resource_url">Resource URL *</label>
                    <input type="url" id="resource_url" name="resource_url" value="<?php echo isset($url) ? htmlspecialchars($url) : ''; ?>" required>
                    <small>Enter YouTube link, PDF URL, or any other resource link</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Resource</button>
                    <a href="manage_resources.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
