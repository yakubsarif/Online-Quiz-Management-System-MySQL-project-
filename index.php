<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$user = get_current_user_data();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz System - Dashboard</title>
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
                    <li><a href="resources.php">Resources</a></li>
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
                <h2>Welcome, <?php echo $user['name']; ?>!</h2>
                <p>Ready to test your knowledge? Choose a quiz from the available options below.</p>
            </div>

            <div class="card">
                <h2>Quick Actions</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="quizzes.php" class="btn btn-primary">Take a Quiz</a>
                    <a href="my_scores.php" class="btn btn-secondary">View My Scores</a>
                    <a href="leaderboard.php" class="btn btn-success">Leaderboard</a>
                    <a href="resources.php" class="btn btn-info">Learning Resources</a>
                    <?php if (is_admin()): ?>
                        <a href="admin/" class="btn btn-danger">Admin Panel</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            // Get recent quizzes (excluding hard quizzes)
            $stmt = $pdo->prepare("
                SELECT q.*, COUNT(qu.id) as question_count 
                FROM quizzes q 
                LEFT JOIN questions qu ON q.id = qu.quiz_id 
                WHERE q.difficulty != 'hard'
                GROUP BY q.id 
                ORDER BY q.created_at DESC 
                LIMIT 3
            ");
            $stmt->execute();
            $recent_quizzes = $stmt->fetchAll();


            ?>

            <?php if ($recent_quizzes): ?>
            <div class="card">
                <h2>Recent Quizzes</h2>
                <div class="quiz-list">
                    <?php foreach ($recent_quizzes as $quiz): ?>
                    <div class="quiz-card">
                        <h3><?php echo $quiz['title']; ?></h3>
                        <p><?php echo $quiz['description']; ?></p>
                        <p><strong>Questions:</strong> <?php echo $quiz['question_count']; ?></p>
                        <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">Take Quiz</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php
            // Get user's recent scores
            $stmt = $pdo->prepare("
                SELECT r.*, q.title as quiz_title 
                FROM results r 
                JOIN quizzes q ON r.quiz_id = q.id 
                WHERE r.user_id = ? 
                ORDER BY r.attempt_date DESC 
                LIMIT 5
            ");
            $stmt->execute([$user['id']]);
            $recent_scores = $stmt->fetchAll();
            ?>

            <?php if ($recent_scores): ?>
            <div class="card">
                <h2>Your Recent Scores</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_scores as $score): ?>
                        <tr>
                            <td><?php echo $score['quiz_title']; ?></td>
                            <td><?php echo $score['score']; ?>/<?php echo $score['total_questions']; ?></td>
                            <td><?php echo $score['percentage']; ?>%</td>
                            <td><?php echo date('M j, Y', strtotime($score['attempt_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="my_scores.php" class="btn btn-secondary">View All Scores</a>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html> 