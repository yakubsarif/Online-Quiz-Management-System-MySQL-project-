<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect to login if not logged in
require_login();

$user = get_current_user_data();
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$score = isset($_GET['score']) ? (int)$_GET['score'] : 0;
$total = isset($_GET['total']) ? (int)$_GET['total'] : 0;
$percentage = isset($_GET['percentage']) ? (float)$_GET['percentage'] : 0;

if (!$quiz_id || !$score || !$total) {
    redirect('quizzes.php');
}

// Get quiz details
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    redirect('quizzes.php');
}

// Get user's answers and correct answers
$stmt = $pdo->prepare("
    SELECT a.*, q.question, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option
    FROM answers a
    JOIN questions q ON a.question_id = q.id
    WHERE a.user_id = ? AND a.quiz_id = ?
    ORDER BY a.question_id
");
$stmt->execute([$user['id'], $quiz_id]);
$answers = $stmt->fetchAll();

// Determine performance message
$performance_message = '';
$performance_class = '';

if ($percentage >= 90) {
    $performance_message = 'Excellent! Outstanding performance!';
    $performance_class = 'alert-success';
} elseif ($percentage >= 80) {
    $performance_message = 'Great job! Well done!';
    $performance_class = 'alert-success';
} elseif ($percentage >= 70) {
    $performance_message = 'Good work! Keep it up!';
    $performance_class = 'alert-info';
} elseif ($percentage >= 60) {
    $performance_message = 'Not bad! Room for improvement.';
    $performance_class = 'alert-info';
} else {
    $performance_message = 'Keep practicing! You can do better!';
    $performance_class = 'alert-danger';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - Quiz System</title>
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
                <h2><?php echo htmlspecialchars($quiz['title']); ?> - Results</h2>
            </div>

            <div class="score-display">
                <h2><?php echo $score; ?>/<?php echo $total; ?></h2>
                <p><?php echo round($percentage, 1); ?>%</p>
                <p><?php echo $performance_message; ?></p>
            </div>

            <div class="card">
                <h3>Performance Summary</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                        <h4>Current Score</h4>
                        <p style="font-size: 1.5rem; font-weight: bold; color: #3498db;"><?php echo round($percentage, 1); ?>%</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Question Review</h3>
                <?php foreach ($answers as $index => $answer): ?>
                <div class="question" style="border-left: 4px solid <?php echo $answer['is_correct'] ? '#27ae60' : '#e74c3c'; ?>;">
                    <h4>Question <?php echo $index + 1; ?></h4>
                    <p><?php echo htmlspecialchars($answer['question']); ?></p>
                    
                    <div style="margin: 1rem 0;">
                        <p><strong>Your Answer:</strong> 
                            <span style="color: <?php echo $answer['is_correct'] ? '#27ae60' : '#e74c3c'; ?>;">
                                <?php echo $answer['selected_option']; ?>. 
                                <?php 
                                $option_text = '';
                                switch($answer['selected_option']) {
                                    case 'A': $option_text = $answer['option_a']; break;
                                    case 'B': $option_text = $answer['option_b']; break;
                                    case 'C': $option_text = $answer['option_c']; break;
                                    case 'D': $option_text = $answer['option_d']; break;
                                }
                                echo htmlspecialchars($option_text);
                                ?>
                            </span>
                        </p>
                        
                        <?php if (!$answer['is_correct']): ?>
                        <p><strong>Correct Answer:</strong> 
                            <span style="color: #27ae60;">
                                <?php echo $answer['correct_option']; ?>. 
                                <?php 
                                $correct_text = '';
                                switch($answer['correct_option']) {
                                    case 'A': $correct_text = $answer['option_a']; break;
                                    case 'B': $correct_text = $answer['option_b']; break;
                                    case 'C': $correct_text = $answer['option_c']; break;
                                    case 'D': $correct_text = $answer['option_d']; break;
                                }
                                echo htmlspecialchars($correct_text);
                                ?>
                            </span>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card" style="text-align: center;">
                <span class="btn btn-secondary" style="cursor: default;">Quiz Completed</span>
                <a href="quizzes.php" class="btn btn-secondary">Back to Quizzes</a>
                <a href="my_scores.php" class="btn btn-success">View All Scores</a>
                <a href="leaderboard.php" class="btn btn-danger">View Leaderboard</a>
            </div>
        </div>
    </main>
</body>
</html> 