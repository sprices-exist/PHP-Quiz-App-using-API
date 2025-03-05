<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include quiz data functions
require_once "includes/config.php";
require_once "includes/quiz_data.php";

// Get quiz parameters from the URL
$amount = isset($_GET['amount']) ? intval($_GET['amount']) : 10;
$category = isset($_GET['category']) && !empty($_GET['category']) ? $_GET['category'] : null;
$difficulty = isset($_GET['difficulty']) && !empty($_GET['difficulty']) ? $_GET['difficulty'] : null;

// Fetch quiz questions
$raw_questions = fetchQuizQuestions($amount, $category, $difficulty);

// Check if questions were fetched successfully
if (!$raw_questions) {
    $error = "Failed to fetch quiz questions. Please try again.";
} else {
    // Process questions (decode HTML entities, shuffle answers)
    $questions = processQuizQuestions($raw_questions);
    
    // Store questions in session for processing answers later
    $_SESSION['quiz_questions'] = $questions;
    
    // Create a new quiz attempt record
    $stmt = $conn->prepare("INSERT INTO quiz_attempts (user_id, total_questions) VALUES (?, ?)");
    $stmt->bind_param("ii", $_SESSION["id"], $amount);
    $stmt->execute();
    
    // Get the attempt ID and store it in session
    $_SESSION['current_attempt_id'] = $conn->insert_id;
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - Online Quiz</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Quiz Time!</h1>
            <div class="nav-links">
                <a href="index.php">Take Quiz</a>
                <a href="previous_results.php">Previous Results</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <p><?php echo $error; ?></p>
                <a href="index.php" class="btn">Go Back</a>
            </div>
        <?php else: ?>
            <form action="quiz_submit.php" method="post" id="quiz-form">
                <div class="quiz-questions">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-container">
                            <h3><?php echo ($index + 1) . '. ' . $question['question']; ?></h3>
                            <div class="answers">
                                <?php foreach ($question['all_answers'] as $answer_index => $answer): ?>
                                    <div class="answer-option">
                                        <input type="radio" name="answers[<?php echo $index; ?>]" 
                                               id="q<?php echo $index; ?>a<?php echo $answer_index; ?>" 
                                               value="<?php echo htmlspecialchars($answer); ?>" required>
                                        <label for="q<?php echo $index; ?>a<?php echo $answer_index; ?>">
                                            <?php echo $answer; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-group">
                    <input type="submit" value="Submit Answers" class="btn btn-primary">
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
