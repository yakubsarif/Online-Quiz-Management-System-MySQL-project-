<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];
    $difficulty = $_POST['difficulty'];
    
    if (empty($title)) {
        $error = 'Please enter a quiz title.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO quizzes (title, description, start_time, duration, difficulty) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$title, $description, $start_time, $duration, $difficulty])) {
            $success = 'Quiz created successfully!';
            $_POST = array();
        } else {
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Quiz - Admin</title>
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
                <h2>Add New Quiz</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title">Quiz Title</label>
                        <input type="text" id="title" name="title" class="form-control" required 
                               value="<?php echo isset($_POST['title']) ? $_POST['title'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control" value="<?php echo isset($_POST['start_time']) ? $_POST['start_time'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration (minutes)</label>
                        <input type="number" id="duration" name="duration" class="form-control" min="1" value="<?php echo isset($_POST['duration']) ? $_POST['duration'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="difficulty">Difficulty</label>
                        <select id="difficulty" name="difficulty" class="form-control">
                            <option value="easy" <?php echo (isset($_POST['difficulty']) && $_POST['difficulty'] === 'easy') ? 'selected' : ''; ?>>Easy</option>
                            <option value="hard" <?php echo (isset($_POST['difficulty']) && $_POST['difficulty'] === 'hard') ? 'selected' : ''; ?>>Hard</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Quiz</button>
                    <a href="manage_quizzes.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </main>
</body>
</html> 