<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Get quiz information
$quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;

if (!$quiz_id) {
    redirect('manage_quizzes.php');
}

// Get quiz details
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    redirect('manage_quizzes.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_option = $_POST['correct_option'];
    
    if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$quiz_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option])) {
            $success = 'Question added successfully!';
            $_POST = array();
        } else {
            $error = 'An error occurred. Please try again.';
        }
    }
}

// Get existing questions for this quiz
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY created_at DESC");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Questions - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <h1>Admin Panel</h1>
                <ul class="nav-links">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="add_quiz.php">Add Quiz</a></li>
                    <li><a href="manage_quizzes.php">Manage Quizzes</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="card">
                <h2>Add Questions to: <?php echo $quiz['title']; ?></h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="question">Question</label>
                        <textarea id="question" name="question" class="form-control" rows="3" required><?php echo isset($_POST['question']) ? $_POST['question'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="option_a">Option A</label>
                        <input type="text" id="option_a" name="option_a" class="form-control" required 
                               value="<?php echo isset($_POST['option_a']) ? $_POST['option_a'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="option_b">Option B</label>
                        <input type="text" id="option_b" name="option_b" class="form-control" required 
                               value="<?php echo isset($_POST['option_b']) ? $_POST['option_b'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="option_c">Option C</label>
                        <input type="text" id="option_c" name="option_c" class="form-control" required 
                               value="<?php echo isset($_POST['option_c']) ? $_POST['option_c'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="option_d">Option D</label>
                        <input type="text" id="option_d" name="option_d" class="form-control" required 
                               value="<?php echo isset($_POST['option_d']) ? $_POST['option_d'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="correct_option">Correct Answer</label>
                        <select id="correct_option" name="correct_option" class="form-control" required>
                            <option value="">Select correct answer</option>
                            <option value="A" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] === 'A') ? 'selected' : ''; ?>>A</option>
                            <option value="B" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] === 'B') ? 'selected' : ''; ?>>B</option>
                            <option value="C" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] === 'C') ? 'selected' : ''; ?>>C</option>
                            <option value="D" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] === 'D') ? 'selected' : ''; ?>>D</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Question</button>
                    <a href="manage_quizzes.php" class="btn btn-secondary">Back to Quizzes</a>
                </form>
            </div>
            
            <?php if ($questions): ?>
            <div class="card">
                <h3>Existing Questions (<?php echo count($questions); ?>)</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Options</th>
                            <th>Correct Answer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $q): ?>
                        <tr>
                            <td><?php echo $q['question']; ?></td>
                            <td>
                                A: <?php echo $q['option_a']; ?><br>
                                B: <?php echo $q['option_b']; ?><br>
                                C: <?php echo $q['option_c']; ?><br>
                                D: <?php echo $q['option_d']; ?>
                            </td>
                            <td><strong><?php echo $q['correct_option']; ?></strong></td>
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