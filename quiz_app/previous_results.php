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

// Get user's quiz attempts
$stmt = $conn->prepare("SELECT * FROM quiz_attempts WHERE user_id = ? ORDER BY start_time DESC");
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Previous Results - Online Quiz</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your Previous Quiz Results</h1>
            <div class="nav-links">
                <a href="index.php">Take Quiz</a>
                <a href="previous_results.php" class="active">Previous Results</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        
        <div class="previous-results">
            <?php if (empty($attempts)): ?>
                <div class="no-results">
                    <p>You haven't taken any quizzes yet. <a href="index.php">Take a quiz now!</a></p>
                </div>
            <?php else: ?>
                <div class="attempts-list">
                    <h2>Your Quiz History</h2>
                    <table class="attempts-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Score</th>
                                <th>Percentage</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attempts as $attempt): ?>
                                <?php 
                                    $date = !empty($attempt['end_time']) ? date('F j, Y, g:i a', strtotime($attempt['end_time'])) : 'Incomplete';
                                    $score = !empty($attempt['score']) ? $attempt['score'] : 'N/A';
                                    $percentage = ($attempt['total_questions'] > 0 && !empty($attempt['score'])) 
                                                ? round(($attempt['score'] / $attempt['total_questions']) * 100) 
                                                : 'N/A';
                                ?>
                                <tr>
                                    <td><?php echo $date; ?></td>
                                    <td><?php echo $score; ?> / <?php echo $attempt['total_questions']; ?></td>
                                    <td><?php echo $percentage; ?>%</td>
                                    <td>
                                        <a href="view_result.php?attempt_id=<?php echo $attempt['attempt_id']; ?>" class="btn btn-small">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
