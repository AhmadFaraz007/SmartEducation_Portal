<?php
include_once('../includes/db.php');

// Get quiz ID and student ID
$quiz_id = $_GET['quiz_id'];
$student_id = $_SESSION['student_id']; // Assuming student_id is stored in session

// Fetch the result for this quiz
$sql = "SELECT * FROM results WHERE student_id = ? AND quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $score = $row['score'];
    echo "<h2>Your Score: $score</h2>";
    echo "<p>Attempt Date: " . $row['attempt_date'] . "</p>";
} else {
    echo "<p>No results found for this quiz.</p>";
}
?>
