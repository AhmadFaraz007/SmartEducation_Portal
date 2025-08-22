<?php
session_start();
include('../includes/db.php');

// Check if the user is logged in and is a faculty
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch assignment ID
if (isset($_GET['id'])) {
    $assignmentId = $_GET['id'];

    // Delete the assignment from the database
    $stmt = $conn->prepare("DELETE FROM assignments WHERE id = ? AND course_id IN (SELECT id FROM courses WHERE faculty_id = ?)");
    $stmt->bind_param("ii", $assignmentId, $_SESSION['user']['id']);

    if ($stmt->execute()) {
        header("Location: manage_assignments.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to delete assignment. Please try again.";
        header("Location: manage_assignments.php");
        exit;
    }
} else {
    header("Location: manage_assignments.php");
    exit;
}
