<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
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
    <title>Learning Resources</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Learning Resources</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="quizzes.php">Quizzes</a>
                <a href="resources.php" class="active">Resources</a>
                <a href="my_scores.php">My Scores</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <div class="resources-grid">
                <?php if (empty($resources)): ?>
                    <p class="no-resources">No learning resources available yet.</p>
                <?php else: ?>
                    <?php foreach ($resources as $resource): ?>
                        <div class="resource-card">
                            <div class="resource-content">
                                <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                                <p><?php echo htmlspecialchars($resource['description']); ?></p>
                                <div class="resource-actions">
                                    <a href="<?php echo htmlspecialchars($resource['resource_url']); ?>" target="_blank" class="btn btn-primary">Open Resource</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>


</body>
</html>
