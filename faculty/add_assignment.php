<?php
session_start();
include('../includes/db.php');

// Check if the user is logged in and is a faculty
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit;
}

$facultyId = $_SESSION['user']['id'];

// Fetch courses assigned to the faculty
$coursesResult = $conn->query("SELECT * FROM courses WHERE faculty_id = '$facultyId'");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // Insert the new assignment into the database
    $stmt = $conn->prepare("INSERT INTO assignments (course_id, title, description, due_date, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $course_id, $title, $description, $due_date);

    if ($stmt->execute()) {
        header("Location: manage_assignments.php");
        exit;
    } else {
        $error = "Failed to add assignment. Please try again.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Assignment - AI CRS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h3 class="mt-5">Add New Assignment</h3>
    <form action="add_assignment.php" method="POST">
        <div class="mb-3">
            <label for="course_id" class="form-label">Course</label>
            <select name="course_id" id="course_id" class="form-select" required>
                <?php while ($course = $coursesResult->fetch_assoc()): ?>
                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Assignment Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date</label>
            <input type="date" name="due_date" id="due_date" class="form-control" required>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Add Assignment</button>
        <a href="manage_assignments.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

</body>
</html>
