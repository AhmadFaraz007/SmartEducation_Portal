<?php
header('Content-Type: application/json');
include('../includes/db.php');

// Check if student_id is provided
if (!isset($_GET['student_id'])) {
    echo json_encode(['error' => 'student_id is required']);
    exit;
}

$student_id = intval($_GET['student_id']);

// Fetch student data from DB
$sql = "SELECT name, email, gpa, interests, completed_courses FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Student not found']);
    exit;
}

$student = $result->fetch_assoc();

// Prepare input data
$input_data = json_encode([
    'gpa' => floatval($student['gpa']),
    'interests' => $student['interests'],
    'completed_courses' => explode(',', $student['completed_courses']),
]);

// Escape and pipe JSON into Python script via stdin
$command = "echo " . escapeshellarg($input_data) . " | python3 ../ml_api/recommend.py";
$output = shell_exec($command);

if ($output === null || trim($output) === "") {
    echo json_encode(['error' => 'Prediction failed or Python script not found']);
    exit;
}

// Return JSON output from Python script
echo $output;
?>
