<?php
include('../includes/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $course_id = $_POST['course_id'];

    $stmt = $conn->prepare("INSERT INTO quizzes (title, course_id) VALUES (?, ?)");
    $stmt->bind_param("si", $title, $course_id);
    $stmt->execute();
    header("Location: manage_quizzes.php");
}
