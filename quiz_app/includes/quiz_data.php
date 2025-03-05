<?php
/**
 * Function to fetch questions from Open Trivia Database API
 * 
 * @param int $amount Number of questions to fetch
 * @param string $category Category ID (optional)
 * @param string $difficulty Difficulty level (optional)
 * @return array Questions array or false on failure
 */
function fetchQuizQuestions($amount = 10, $category = null, $difficulty = null) {
    // Build API URL with parameters
    $url = "https://opentdb.com/api.php?amount={$amount}&type=multiple";
    
    // Add optional parameters if provided
    if ($category) {
        $url .= "&category={$category}";
    }
    
    if ($difficulty) {
        $url .= "&difficulty={$difficulty}";
    }
    
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Execute cURL session and get the response
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        return false;
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Decode JSON response
    $data = json_decode($response, true);
    
    // Check if response is valid
    if ($data['response_code'] !== 0) {
        return false;
    }
    
    return $data['results'];
}

/**
 * Function to decode HTML entities in quiz questions and answers
 * 
 * @param array $questions Array of questions from API
 * @return array Processed questions
 */
function processQuizQuestions($questions) {
    foreach ($questions as &$question) {
        // Decode HTML entities in question text
        $question['question'] = html_entity_decode($question['question'], ENT_QUOTES | ENT_HTML5);
        
        // Decode HTML entities in correct answer
        $question['correct_answer'] = html_entity_decode($question['correct_answer'], ENT_QUOTES | ENT_HTML5);
        
        // Decode HTML entities in incorrect answers
        foreach ($question['incorrect_answers'] as &$answer) {
            $answer = html_entity_decode($answer, ENT_QUOTES | ENT_HTML5);
        }
        
        // Prepare all answers in one array (with correct answer)
        $allAnswers = $question['incorrect_answers'];
        $allAnswers[] = $question['correct_answer'];
        
        // Shuffle answers
        shuffle($allAnswers);
        
        // Add all answers to the question
        $question['all_answers'] = $allAnswers;
    }
    
    return $questions;
}
?>
