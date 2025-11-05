<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect to login if not logged in
require_login();

$user = get_current_user_data();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('quizzes.php');
}

$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
$answers = isset($_POST['answers']) ? $_POST['answers'] : [];

if (!$quiz_id || empty($answers)) {
    redirect('quizzes.php');
}

// Get quiz details
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    redirect('quizzes.php');
}

// Get questions for this quiz
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    redirect('quizzes.php');
}

// Start transaction
$pdo->beginTransaction();

try {
    $correct_answers = 0;
    $total_questions = count($questions);
    
    // Process each answer
    foreach ($answers as $question_id => $selected_option) {
        // Get the question details
        $question = null;
        foreach ($questions as $q) {
            if ($q['id'] == $question_id) {
                $question = $q;
                break;
            }
        }
        
        if ($question) {
            $is_correct = ($selected_option === $question['correct_option']);
            
            if ($is_correct) {
                $correct_answers++;
            }
            
            // Save the answer
            $stmt = $pdo->prepare("
                INSERT INTO answers (user_id, quiz_id, question_id, selected_option, is_correct) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user['id'],
                $quiz_id,
                $question_id,
                $selected_option,
                $is_correct
            ]);
        }
    }
    
    // Calculate score and percentage
    $score = $correct_answers;
    $percentage = ($score / $total_questions) * 100;
    
    // Save the result
    $stmt = $pdo->prepare("
        INSERT INTO results (user_id, quiz_id, score, total_questions, percentage) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user['id'],
        $quiz_id,
        $score,
        $total_questions,
        $percentage
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    // Redirect to results page
    redirect("quiz_result.php?quiz_id=$quiz_id&score=$score&total=$total_questions&percentage=" . round($percentage, 2));
    
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollback();
    redirect('quizzes.php');
}
?> 