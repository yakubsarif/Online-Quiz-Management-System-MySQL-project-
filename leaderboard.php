<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect to login if not logged in
require_login();

$user = get_current_user_data();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Quiz System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <h1>Quiz System</h1>
                <ul class="nav-links">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="quizzes.php">Available Quizzes</a></li>
                    <li><a href="my_scores.php">My Scores</a></li>
                    <li><a href="leaderboard.php">Leaderboard</a></li>
                    <?php if (is_admin()): ?>
                        <li><a href="admin/">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="card">
                <h2>Overall Leaderboard</h2>
                <p>See how you rank against other users across all quizzes!</p>
            </div>

            <div class="card">
                <h3>Top Performers</h3>
                <?php
                // Get overall top performers (users with highest average scores)
                $stmt = $pdo->prepare("
                    SELECT u.name, 
                           COUNT(r.id) as total_attempts,
                           AVG(r.percentage) as avg_score,
                           MAX(r.percentage) as best_score
                    FROM users u
                    LEFT JOIN results r ON u.id = r.user_id
                    WHERE u.is_admin = FALSE
                    GROUP BY u.id, u.name
                    HAVING COUNT(r.id) > 0
                    ORDER BY AVG(r.percentage) DESC
                    LIMIT 10
                ");
                $stmt->execute();
                $overall_leaders = $stmt->fetchAll(); //sql query thke 2d array.
                ?>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>User</th>
                            <th>Average Score</th>
                            <th>Best Score</th>
                            <th>Total Attempts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        //This checks if the variable exists and is not null/false &&  if the array has at least one element
                        if ($overall_leaders && count($overall_leaders) > 0) {
                            foreach ($overall_leaders as $index => $leader) {
                                $rank = $index + 1; // array index starts from 0 so we add 1 to get the correct rank.
                        ?>
                        
                        <tr style="<?php echo $leader['name'] == $user['name'] ? 'background: #e8f5e8;' : ''; ?>">
                            <td><?php echo $rank; ?></td>
                            <td><?php echo htmlspecialchars($leader['name']); ?></td>
                            <td><strong><?php echo round($leader['avg_score'], 1); ?>%</strong></td>
                            <td><?php echo round($leader['best_score'], 1); ?>%</td>
                            <td><?php echo $leader['total_attempts']; ?></td>
                        </tr>
                        <?php 
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="5" class="alert alert-info">
                                <p>No overall scores recorded yet.</p>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="text-align: center;">
                <a href="quizzes.php" class="btn btn-primary">Take a Quiz</a>
                <a href="my_scores.php" class="btn btn-secondary">My Scores</a>
                <a href="index.php" class="btn btn-success">Back to Dashboard</a>
            </div>
        </div>
    </main>
</body>
</html> 