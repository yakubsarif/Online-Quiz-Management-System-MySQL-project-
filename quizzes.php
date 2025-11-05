<?php
date_default_timezone_set('Asia/Dhaka');
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect to login if not logged in
require_login();

$user = get_current_user_data();

// Set timezone to Bangladesh
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Dhaka');
}

// Debug: Show PHP and MySQL time for troubleshooting
if (isset($pdo)) {
    $stmt = $pdo->query("SELECT NOW() as mysql_time");
    $row = $stmt->fetch();
    echo '<div style="background:#ffe;border:1px solid #fc0;padding:8px;margin:8px 0;color:#333;font-family:monospace;">';
    echo 'PHP time: ' . date('Y-m-d H:i:s') . '<br>';
    echo 'MySQL time: ' . $row['mysql_time'] . '<br>';
    echo '</div>';
}

// Get all quizzes with attempt count for current user
$stmt = $pdo->prepare("
    SELECT q.*, 
           (SELECT COUNT(*) FROM results r WHERE r.quiz_id = q.id AND r.user_id = ?) as attempts
    FROM quizzes q 
    ORDER BY q.created_at DESC
");
$stmt->execute([$user['id']]);
$quizzes = $stmt->fetchAll();

// Helper: get top 5 users from overall leaderboard
function get_overall_top5($pdo) {
    $stmt = $pdo->prepare("
        SELECT u.id 
        FROM users u 
        LEFT JOIN results r ON u.id = r.user_id 
        WHERE u.is_admin = FALSE 
        GROUP BY u.id 
        HAVING COUNT(r.id) > 0
        ORDER BY AVG(r.percentage) DESC 
        LIMIT 5
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
$overall_top5 = get_overall_top5($pdo);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Quizzes - Quiz System</title>
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
                <h2>Available Quizzes</h2>
                <p>Choose a quiz to test your knowledge. Each quiz can only be taken once!</p>
            </div>

            <?php if ($quizzes): ?>
            <div class="quiz-list">
                <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz-card">
                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                    
                    <div style="margin: 1rem 0;">
                        <p><strong>Your Attempts:</strong> <?php echo $quiz['attempts']; ?></p>
                        <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($quiz['created_at'])); ?></p>
                        <p><strong>Difficulty:</strong> <?php echo ucfirst($quiz['difficulty']); ?></p>
                        <?php if ($quiz['start_time']): ?>
                            <?php $start_dt = new DateTime($quiz['start_time']); ?>
                            <?php $now_dt = new DateTime(); ?>
                            <?php if ($quiz['duration']): ?>
                                <?php $end_dt = (clone $start_dt)->modify('+' . $quiz['duration'] . ' minutes'); ?>
                                <?php if ($now_dt < $start_dt): ?>
                                    <p style="color:#e67e22;"><strong>Quiz will start at:</strong> <?php echo $start_dt->format('M j, Y H:i'); ?></p>
                                <?php elseif ($now_dt > $end_dt): ?>
                                    <p style="color:#e74c3c;"><strong>Quiz Ended</strong></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php
                    $is_hard = ($quiz['difficulty'] === 'hard');
                    $can_access = !$is_hard || in_array($user['id'], $overall_top5);
                    $quiz_available = true;
                    $has_attempted = $quiz['attempts'] > 0;
                    
                    if ($quiz['start_time'] && $quiz['duration']) {
                        $start = new DateTime($quiz['start_time']);
                        $end = (clone $start)->modify('+' . $quiz['duration'] . ' minutes');
                        $now = new DateTime();
                        $quiz_available = ($now >= $start && $now <= $end);
                    }
                    ?>
                    <?php if ($can_access && $quiz_available && !$has_attempted): ?>
                        <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">
                            Take Quiz
                        </a>
                    <?php elseif ($has_attempted): ?>
                        <button class="btn btn-secondary" disabled>Already Completed</button>
                        <div style="color:#27ae60;font-size:0.9em;">You have already taken this quiz.</div>
                    <?php elseif ($is_hard && !$can_access): ?>
                        <button class="btn btn-secondary" disabled>Top 5 Overall Only</button>
                        <div style="color:#e74c3c;font-size:0.9em;">Only top 5 users on the overall leaderboard can access this quiz.</div>
                    <?php elseif (!$quiz_available): ?>
                        <button class="btn btn-secondary" disabled>Not Available</button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="alert alert-info">
                    <p>No quizzes are available at the moment. Please check back later!</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html> 