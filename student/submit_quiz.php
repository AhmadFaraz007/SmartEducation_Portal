<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user']['id'];
    $quiz_id = $_POST['quiz_id'];
    $answers = $_POST['answers'] ?? [];

    $score = 0;
    $total = count($answers);

    foreach ($answers as $question_id => $selected_option) {
        // Fetch correct answer
        $res = $conn->query("SELECT correct_option FROM questions WHERE id = '$question_id'");
        $correct_option = $res->fetch_assoc()['correct_option'];

        // Store student's answer
        $stmt = $conn->prepare("INSERT INTO student_answers (student_id, quiz_id, question_id, selected_option) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $student_id, $quiz_id, $question_id, $selected_option);
        $stmt->execute();

        // Scoring
        if ($selected_option === $correct_option) {
            $score++;
        }
    }

    // Save result
    $stmt = $conn->prepare("INSERT INTO quiz_results (student_id, quiz_id, score, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $student_id, $quiz_id, $score, $total);
    $stmt->execute();

    echo "<h3>🎉 Quiz Submitted!</h3>";
    echo "<p>✅ Score: $score / $total</p>";
    echo "<a href='dashboard.php' class='btn btn-secondary mt-3'>Go to Dashboard</a>";
}
