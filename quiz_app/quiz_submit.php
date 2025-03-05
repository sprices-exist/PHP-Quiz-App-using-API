<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if quiz questions exist in session
if (!isset($_SESSION['quiz_questions']) || !isset($_SESSION['current_attempt_id'])) {
    header("location: index.php");
    exit;
}

// Include configuration file
require_once "includes/config.php";

// Get questions from session
$questions = $_SESSION['quiz_questions'];
$attempt_id = $_SESSION['current_attempt_id'];

// Get user answers from form submission
$user_answers = isset($_POST['answers']) ? $_POST['answers'] : [];

// Calculate score
$score = 0;
$total_questions = count($questions);

// Process each question and save results
if ($total_questions > 0) {
    // Prepare statement for inserting results
    $stmt = $conn->prepare("INSERT INTO quiz_results (attempt_id, question_text, correct_answer, user_answer, is_correct, category, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Check if prepare succeeded
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("isssiss", $attempt_id, $question_text, $correct_answer, $user_answer, $is_correct, $category, $difficulty);
        
        // Process each question
        foreach ($questions as $index => $question) {
            // Get question details
            $question_text = $question['question'];
            $correct_answer = $question['correct_answer'];
            $category = $question['category'];
            $difficulty = $question['difficulty'];
            
            // Get user's answer for this question or empty string if not answered
            $user_answer = isset($user_answers[$index]) ? $user_answers[$index] : '';
            
            // Check if answer is correct
            $is_correct = ($user_answer === $correct_answer) ? 1 : 0;
            
            // If correct, increment score
            if ($is_correct) {
                $score++;
            }
            
            // Insert result into database
            $stmt->execute();
        }
        
        // Close statement
        $stmt->close();
        
        // Update the quiz attempt with end time and score
        $update_stmt = $conn->prepare("UPDATE quiz_attempts SET end_time = NOW(), score = ? WHERE attempt_id = ?");
        $update_stmt->bind_param("ii", $score, $attempt_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

// Clear the quiz questions from session
unset($_SESSION['quiz_questions']);
unset($_SESSION['current_attempt_id']);

// Redirect to results page
header("location: quiz_result.php?attempt_id=" . $attempt_id);
exit;
?>
