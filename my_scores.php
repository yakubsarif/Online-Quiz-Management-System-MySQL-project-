<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect to login if not logged in
require_login();

$user = get_current_user_data();

// Get user's results with quiz details
$stmt = $pdo->prepare("
    SELECT r.*, q.title as quiz_title, q.description as quiz_description
    FROM results r 
    JOIN quizzes q ON r.quiz_id = q.id 
    WHERE r.user_id = ? 
    ORDER BY r.attempt_date DESC
");
$stmt->execute([$user['id']]);
$results = $stmt->fetchAll();

// Calculate overall statistics (best and average only)
$best_score = 0;
$average_score = 0;
$has_results = count($results) > 0;

if ($has_results) {
    $sum_percentages = 0;
    foreach ($results as $result) {
        if ($result['percentage'] > $best_score) {
            $best_score = $result['percentage'];
        }
        $sum_percentages += $result['percentage'];
    }
    $average_score = $sum_percentages / count($results);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Scores - Quiz System</title>
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
                <h2>My Quiz Scores</h2>
                <p>Track your performance across all quizzes and see your progress over time.</p>
            </div>

            <?php if ($has_results): ?>
            <div class="card">
                <h3>Overall Statistics</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 5px;">
                        <h4>Best Score</h4>
                        <p style="font-size: 2rem; font-weight: bold; color: #27ae60;"><?php echo round($best_score, 1); ?>%</p>
                    </div>
                    <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 5px;">
                        <h4>Average Score</h4>
                        <p style="font-size: 2rem; font-weight: bold; color: #f39c12;"><?php echo round($average_score, 1); ?>%</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Recent Attempts</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['quiz_title']); ?></td>
                            <td><?php echo $result['score']; ?>/<?php echo $result['total_questions']; ?></td>
                            <td>
                                <span style="color: <?php echo $result['percentage'] >= 80 ? '#27ae60' : ($result['percentage'] >= 60 ? '#f39c12' : '#e74c3c'); ?>; font-weight: bold;">
                                    <?php echo round($result['percentage'], 1); ?>%
                                </span>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($result['attempt_date'])); ?></td>
                            <td>
                                <span class="btn btn-secondary btn-sm" style="cursor: default;">Completed</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php else: ?>   
            <div class="card">
                <div class="alert alert-info">
                    <h3>No Quiz Attempts Yet</h3>
                    <p>You haven't taken any quizzes yet. Start by taking your first quiz!</p>
                    <a href="quizzes.php" class="btn btn-primary">Take Your First Quiz</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="card" style="text-align: center;">
                <a href="quizzes.php" class="btn btn-primary">Take a Quiz</a>
                <a href="leaderboard.php" class="btn btn-success">View Leaderboard</a>
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </main>
</body>
</html> 