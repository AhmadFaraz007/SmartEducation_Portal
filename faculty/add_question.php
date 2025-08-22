<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = $_POST['quiz_id'];
    $question_text = $_POST['question_text'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $option4 = $_POST['option4'];
    $correct_option = $_POST['correct_option'];

    $stmt = $conn->prepare("INSERT INTO questions 
        (quiz_id, question_text, option1, option2, option3, option4, correct_option)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $quiz_id, $question_text, $option1, $option2, $option3, $option4, $correct_option);
    $stmt->execute();

    header("Location: manage_questions.php?quiz_id=" . $quiz_id);
    exit;
}
