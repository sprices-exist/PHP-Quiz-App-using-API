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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Quiz</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Online Quiz, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            <div class="nav-links">
                <a href="index.php" class="active">Take Quiz</a>
                <a href="previous_results.php">Previous Results</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        
        <div class="quiz-settings">
            <h2>Start a New Quiz</h2>
            <form action="quiz.php" method="get">
                <div class="form-group">
                    <label for="amount">Number of Questions:</label>
                    <select name="amount" id="amount">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category">
                        <option value="">Any Category</option>
                        <option value="9">General Knowledge</option>
                        <option value="10">Entertainment: Books</option>
                        <option value="11">Entertainment: Film</option>
                        <option value="12">Entertainment: Music</option>
                        <option value="14">Entertainment: Television</option>
                        <option value="15">Entertainment: Video Games</option>
                        <option value="17">Science & Nature</option>
                        <option value="18">Science: Computers</option>
                        <option value="19">Science: Mathematics</option>
                        <option value="21">Sports</option>
                        <option value="22">Geography</option>
                        <option value="23">History</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="difficulty">Difficulty:</label>
                    <select name="difficulty" id="difficulty">
                        <option value="">Any Difficulty</option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <input type="submit" value="Start Quiz" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
