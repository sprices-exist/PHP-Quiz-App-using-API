<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include configuration file
require_once "includes/config.php";

// Check if attempt_id is provided
if (!isset($_GET['attempt_id']) || empty($_GET['attempt_id'])) {
    header("location: previous_results.php");
    exit;
}

$attempt_id = intval($_GET['attempt_id']);

// Get attempt info
$stmt = $conn->prepare("SELECT a.*, u.username FROM quiz_attempts a JOIN users u ON a.user_id = u.id WHERE a.attempt_id = ? AND a.user_id = ?");
$stmt->bind_param("ii", $attempt_id, $_SESSION["id"]);
$stmt->execute();
$result = $stmt->get_result();

// Check if the attempt exists and belongs to the current user
if ($result->num_rows !== 1) {
    header("location: previous_results.php");
    exit;
}

$attempt = $result->fetch_assoc();
$stmt->close();

// Get quiz results
$stmt = $conn->prepare("SELECT * FROM quiz_results WHERE attempt_id = ? ORDER BY result_id ASC");
$stmt->bind_param("i", $attempt_id);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate percentage
$percentage = ($attempt['total_questions'] > 0) ? round(($attempt['score'] / $attempt['total_questions']) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Result - Online Quiz</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Quiz Result Details</h1>
            <div class="nav-links">
                <a href="index.php">Take Quiz</a>
                <a href="previous_results.php">Previous Results</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        
        <div class="quiz-results">
            <div class="result-summary">
                <h2>Quiz Summary</h2>
                <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($attempt['end_time'])); ?></p>
                <p><strong>Score:</strong> <?php echo $attempt['score']; ?> out of <?php echo $attempt['total_questions']; ?></p>
                <p><strong>Percentage:</strong> <?php echo $percentage; ?>%</p>
                
                <?php if ($percentage >= 70): ?>
                    <p class="result-message success">Great job! You passed the quiz!</p>
                <?php else: ?>
                    <p class="result-message failure">You didn't pass. Try again!</p>
                <?php endif; ?>
            </div>
            
            <h2>Detailed Results</h2>
            
            <?php foreach ($results as $index => $result): ?>
                <div class="question-result <?php echo $result['is_correct'] ? 'correct' : 'incorrect'; ?>">
                    <h3><?php echo ($index + 1) . '. ' . $result['question_text']; ?></h3>
                    <p><strong>Your Answer:</strong> <?php echo $result['user_answer']; ?></p>
                    <p><strong>Correct Answer:</strong> <?php echo $result['correct_answer']; ?></p>
                    <p><strong>Category:</strong> <?php echo $result['category']; ?></p>
                    <p><strong>Difficulty:</strong> <?php echo ucfirst($result['difficulty']); ?></p>
                    <p class="result-indicator">
                        <?php if ($result['is_correct']): ?>
                            <span class="correct">Correct</span>
                        <?php else: ?>
                            <span class="incorrect">Incorrect</span>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
            
            <div class="actions">
                <a href="previous_results.php" class="btn">Back to Results</a>
                <a href="index.php" class="btn btn-primary">Take Another Quiz</a>
            </div>
        </div>
    </div>
</body>
</html>
