<?php
date_default_timezone_set('Asia/Dhaka');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect to login if not logged in
require_login();

$user = get_current_user_data();
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$quiz_id) {
    echo '<div style="background:#ffe;border:1px solid #fc0;padding:8px;margin:8px 0;color:#333;font-family:monospace;">Redirect: quiz_id is missing or zero.<br>quiz_id: ' . htmlspecialchars($quiz_id) . '</div>';
    exit;
}

// Get quiz details
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    echo '<div style="background:#ffe;border:1px solid #fc0;padding:8px;margin:8px 0;color:#333;font-family:monospace;">Redirect: quiz not found in database.<br>quiz_id: ' . htmlspecialchars($quiz_id) . '</div>';
    exit;
}

// Get questions for this quiz
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    echo '<div style="background:#ffe;border:1px solid #fc0;padding:8px;margin:8px 0;color:#333;font-family:monospace;">Redirect: no questions found for this quiz.<br>quiz_id: ' . htmlspecialchars($quiz_id) . '</div>';
    exit;
}

// Check if user has already taken this quiz
$stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM results WHERE user_id = ? AND quiz_id = ?");
$stmt->execute([$user['id'], $quiz_id]);
$result = $stmt->fetch();

if ($result['attempts'] > 0) {
    echo '<div style="background:#ffe;border:1px solid #fc0;padding:8px;margin:8px 0;color:#333;font-family:monospace;">';
    echo 'You have already taken this quiz. Redirecting to quiz results...<br>';
    echo '<a href="quiz_result.php?quiz_id=' . $quiz_id . '">View Results</a> | <a href="quizzes.php">Back to Quizzes</a>';
    echo '</div>';
    exit;
}

// Enforce quiz window (robust)
$quiz_window_open = true;
$quiz_start = null;
$quiz_end = null;

if (!empty($quiz['start_time']) && !empty($quiz['duration'])) {
    $quiz_start = new DateTime($quiz['start_time']);
    $quiz_end = (clone $quiz_start)->modify('+' . $quiz['duration'] . ' minutes');
    $now = new DateTime();
    $quiz_window_open = ($now >= $quiz_start && $now <= $quiz_end);
    // Debug output for troubleshooting
    if (!$quiz_window_open) {
        echo '<div style="background:#ffe;border:1px solid #fc0;padding:8px;margin:8px 0;color:#333;font-family:monospace;">';
        echo 'Now: ' . $now->format('Y-m-d H:i:s') . '<br>';
        echo 'Start: ' . $quiz_start->format('Y-m-d H:i:s') . '<br>';
        echo 'End: ' . $quiz_end->format('Y-m-d H:i:s') . '<br>';
        echo 'Quiz window open: NO<br>';
        echo '</div>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Quiz System</title>
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
                <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
                <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                <p><strong>Total Questions:</strong> <?php echo count($questions); ?></p>
                <p><strong>Instructions:</strong> Select the correct answer for each question. You can review your answers before submitting.</p>
            </div>

            <?php if (!$quiz_window_open): ?>
                <div class="card alert alert-info" style="text-align:center;">
                    <h3>This quiz is only available from <br><?php echo $quiz_start ? $quiz_start->format('M j, Y H:i') : '-'; ?> to <?php echo $quiz_end ? $quiz_end->format('M j, Y H:i') : '-'; ?>.</h3>
                    <p>Please come back during the scheduled time.</p>
                </div>
            <?php else: ?>
                <?php if ($quiz_start && $quiz_end): ?>
                    <div class="card" style="text-align:center;">
                        <h3>Time Remaining: <span id="timer"></span></h3>
                    </div>
                <?php endif; ?>
                <form method="POST" action="submit_quiz.php" id="quizForm">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    
                    <?php foreach ($questions as $index => $question): ?>
                    <div class="question">
                        <h3>Question <?php echo $index + 1; ?></h3>
                        <p><?php echo htmlspecialchars($question['question']); ?></p>
                        
                        <div class="options">
                            <label class="option">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="A" required>
                                <span><strong>A.</strong> <?php echo htmlspecialchars($question['option_a']); ?></span>
                            </label>
                            
                            <label class="option">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="B" required>
                                <span><strong>B.</strong> <?php echo htmlspecialchars($question['option_b']); ?></span>
                            </label>
                            
                            <label class="option">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="C" required>
                                <span><strong>C.</strong> <?php echo htmlspecialchars($question['option_c']); ?></span>
                            </label>
                            
                            <label class="option">
                                <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="D" required>
                                <span><strong>D.</strong> <?php echo htmlspecialchars($question['option_d']); ?></span>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="card" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" style="font-size: 1.2rem; padding: 1rem 2rem;">
                            Submit Quiz
                        </button>
                        <a href="quizzes.php" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <script>
<?php if ($quiz_window_open && $quiz_start && $quiz_end): ?>
    // Countdown timer
    var endTime = new Date("<?php echo $quiz_end->format('Y-m-d H:i:s'); ?>").getTime();
    var timerEl = document.getElementById('timer');
    var quizForm = document.getElementById('quizForm');
    function updateTimer() {
        var now = new Date().getTime();
        var distance = endTime - now;
        if (distance <= 0) {
            timerEl.innerHTML = '00:00:00';
            if (quizForm) quizForm.submit();
        } else {
            var hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
            var minutes = Math.floor((distance / (1000 * 60)) % 60);
            var seconds = Math.floor((distance / 1000) % 60);
            timerEl.innerHTML =
                (hours < 10 ? '0' : '') + hours + ':' +
                (minutes < 10 ? '0' : '') + minutes + ':' +
                (seconds < 10 ? '0' : '') + seconds;
        }
    }
    updateTimer();
    setInterval(updateTimer, 1000);
<?php endif; ?>
// Add confirmation before submitting
if (document.querySelector('form')) {
    document.querySelector('form').addEventListener('submit', function(e) {
        const unanswered = document.querySelectorAll('input[type="radio"]:not(:checked)').length;
        const totalQuestions = <?php echo count($questions); ?>;
        const answered = totalQuestions - unanswered;
        
        if (answered < totalQuestions) {
            if (!confirm(`You have answered ${answered} out of ${totalQuestions} questions. Are you sure you want to submit?`)) {
                e.preventDefault();
            }
        }
    });
}
</script>
</body>
</html> 