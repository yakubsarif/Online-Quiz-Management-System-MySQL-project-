<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Handle quiz deletion
if (isset($_GET['delete'])) {
    $quiz_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    
    if ($stmt->execute([$quiz_id])) {
        $success = 'Quiz deleted successfully!';
    } else {
        $error = 'Error deleting quiz.';
    }
}

// Handle quiz editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_quiz'])) {
    $quiz_id = $_POST['quiz_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];
    $difficulty = $_POST['difficulty'];
    
    if (empty($title)) {
        $error = 'Please enter a quiz title.';
    } else {
        $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, description = ?, start_time = ?, duration = ?, difficulty = ? WHERE id = ?");
        
        if ($stmt->execute([$title, $description, $start_time, $duration, $difficulty, $quiz_id])) {
            $success = 'Quiz updated successfully!';
        } else {
            $error = 'Error updating quiz.';
        }
    }
}

// Get all quizzes
$stmt = $pdo->prepare("SELECT * FROM quizzes ORDER BY created_at DESC");
$stmt->execute();
$quizzes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - Admin</title>
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
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['edit'])): ?>
<?php
$edit_id = $_GET['edit'];
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$edit_id]);
$edit_quiz = $stmt->fetch();
?>
<div class="card">
    <h3>Edit Quiz</h3>
    <form method="POST" action="">
        <input type="hidden" name="quiz_id" value="<?php echo $edit_quiz['id']; ?>">
        <div class="form-group">
            <label for="title">Quiz Title</label>
            <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($edit_quiz['title']); ?>">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($edit_quiz['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="start_time">Start Time</label>
            <input type="datetime-local" id="start_time" name="start_time" class="form-control" value="<?php echo $edit_quiz['start_time'] ? date('Y-m-d\TH:i', strtotime($edit_quiz['start_time'])) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="duration">Duration (minutes)</label>
            <input type="number" id="duration" name="duration" class="form-control" min="1" value="<?php echo $edit_quiz['duration']; ?>">
        </div>
        <div class="form-group">
            <label for="difficulty">Difficulty</label>
            <select id="difficulty" name="difficulty" class="form-control">
                <option value="easy" <?php echo ($edit_quiz['difficulty'] === 'easy') ? 'selected' : ''; ?>>Easy</option>
                <option value="hard" <?php echo ($edit_quiz['difficulty'] === 'hard') ? 'selected' : ''; ?>>Hard</option>
            </select>
        </div>
        <button type="submit" name="edit_quiz" class="btn btn-primary">Update Quiz</button>
        <a href="manage_quizzes.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php endif; ?>
            <div class="card">
                <h2>Manage Quizzes</h2>
                
                <?php if ($quizzes): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Start Time</th>
                            <th>Duration</th>
                            <th>Difficulty</th>
                            <th>Questions</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quizzes as $quiz): ?>
                        <?php
                        // Get question count for this quiz
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM questions WHERE quiz_id = ?");
                        $stmt->execute([$quiz['id']]);
                        $question_count = $stmt->fetch()['count'];
                        ?>
                        <tr>
                            <td><?php echo $quiz['title']; ?></td>
                            <td><?php echo $quiz['description']; ?></td>
                            <td><?php echo $quiz['start_time'] ? date('M j, Y H:i', strtotime($quiz['start_time'])) : '-'; ?></td>
                            <td><?php echo $quiz['duration'] ? $quiz['duration'] . ' min' : '-'; ?></td>
                            <td><?php echo ucfirst($quiz['difficulty']); ?></td>
                            <td><?php echo $question_count; ?> questions</td>
                            <td><?php echo date('M j, Y', strtotime($quiz['created_at'])); ?></td>
                            <td>
                                <a href="add_questions.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-sm">Add Questions</a>
                                <a href="manage_quizzes.php?edit=<?php echo $quiz['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="manage_quizzes.php?delete=<?php echo $quiz['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this quiz?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="alert alert-info">
                    <p>No quizzes created yet. <a href="add_quiz.php">Create your first quiz</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html> 