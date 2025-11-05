<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../index.php');
}

$user = get_current_user_data();

// Get statistics for admin dashboard
$stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE is_admin = FALSE");
$stmt->execute();
$user_count = $stmt->fetch()['total_users'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_quizzes FROM quizzes");
$stmt->execute();
$quiz_count = $stmt->fetch()['total_quizzes'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_questions FROM questions");
$stmt->execute();
$question_count = $stmt->fetch()['total_questions'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_attempts FROM results");
$stmt->execute();
$attempt_count = $stmt->fetch()['total_attempts'];

// Get recent quiz attempts
$stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name, q.title as quiz_title
    FROM results r
    JOIN users u ON r.user_id = u.id
    JOIN quizzes q ON r.quiz_id = q.id
    ORDER BY r.attempt_date DESC
    LIMIT 10
");
$stmt->execute();
$recent_attempts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Quiz System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <h1>Quiz System - Admin Panel</h1>
                <ul class="nav-links">
                    <li><a href="../index.php">Main Site</a></li>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="add_quiz.php">Add Quiz</a></li>
                    <li><a href="manage_quizzes.php">Manage Quizzes</a></li>
                    <li><a href="add_questions.php">Add Questions</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="card">
                <h2>Admin Dashboard</h2>
                <p>Welcome, <?php echo htmlspecialchars($user['name']); ?>! Manage your quiz system from here.</p>
            </div>

            <div class="card">
                <h3>System Statistics</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 5px;">
                        <h4>Total Users</h4>
                        <p style="font-size: 2rem; font-weight: bold; color: #3498db;"><?php echo $user_count; ?></p>
                    </div>
                    <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 5px;">
                        <h4>Total Quizzes</h4>
                        <p style="font-size: 2rem; font-weight: bold; color: #27ae60;"><?php echo $quiz_count; ?></p>
                    </div>
                    <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 5px;">
                        <h4>Total Questions</h4>
                        <p style="font-size: 2rem; font-weight: bold; color: #e74c3c;"><?php echo $question_count; ?></p>
                    </div>
                    <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 5px;">
                        <h4>Total Attempts</h4>
                        <p style="font-size: 2rem; font-weight: bold; color: #f39c12;"><?php echo $attempt_count; ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Quick Actions</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="add_quiz.php" class="btn btn-primary">Create New Quiz</a>
                    <a href="manage_quizzes.php" class="btn btn-secondary">Manage Quizzes</a>
                    <a href="add_questions.php" class="btn btn-success">Add Questions</a>
                    <a href="manage_resources.php" class="btn btn-info">Manage Resources</a>
                    <a href="../leaderboard.php" class="btn btn-danger">View Leaderboard</a>
                </div>
            </div>

            <?php if ($recent_attempts): ?>
            <div class="card">
                <h3>Recent Quiz Attempts</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_attempts as $attempt): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($attempt['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                            <td><?php echo $attempt['score']; ?>/<?php echo $attempt['total_questions']; ?></td>
                            <td>
                                <span style="color: <?php echo $attempt['percentage'] >= 80 ? '#27ae60' : ($attempt['percentage'] >= 60 ? '#f39c12' : '#e74c3c'); ?>; font-weight: bold;">
                                    <?php echo round($attempt['percentage'], 1); ?>%
                                </span>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($attempt['attempt_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html> 