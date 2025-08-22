<?php
session_start();
include('../includes/db.php');

// Check if user is logged in and is an admin or faculty
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'faculty'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch students and courses
$studentsQuery = $conn->query("SELECT id, name FROM users WHERE role = 'student'");
$coursesQuery = $conn->query("SELECT id, title FROM courses");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'];
    $courseId = $_POST['course_id'];

    // Insert enrollment
    $insertQuery = $conn->prepare("INSERT INTO enrollments (student_id, course_id, enrolled_on) VALUES (?, ?, NOW())");
    $insertQuery->bind_param("ii", $studentId, $courseId);
    $insertQuery->execute();

    echo "Student has been enrolled successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enroll Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h3>Enroll Student in Course</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="student_id" class="form-label">Select Student</label>
            <select id="student_id" name="student_id" class="form-select">
                <?php while ($student = $studentsQuery->fetch_assoc()): ?>
                    <option value="<?php echo $student['id']; ?>"><?php echo $student['name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="course_id" class="form-label">Select Course</label>
            <select id="course_id" name="course_id" class="form-select">
                <?php while ($course = $coursesQuery->fetch_assoc()): ?>
                    <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Enroll</button>
    </form>
</div>

</body>
</html>
