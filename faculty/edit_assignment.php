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

// Fetch the assignment to be edited
if (isset($_GET['id'])) {
    $assignmentId = $_GET['id'];
    $assignmentResult = $conn->query("SELECT * FROM assignments WHERE id = '$assignmentId' AND course_id IN (SELECT id FROM courses WHERE faculty_id = '$facultyId')");

    if ($assignmentResult->num_rows > 0) {
        $assignment = $assignmentResult->fetch_assoc();
    } else {
        header("Location: manage_assignments.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // Update the assignment in the database
    $stmt = $conn->prepare("UPDATE assignments SET course_id = ?, title = ?, description = ?, due_date = ? WHERE id = ?");
    $stmt->bind_param("isssi", $course_id, $title, $description, $due_date, $assignmentId);

    if ($stmt->execute()) {
        header("Location: manage_assignments.php");
        exit;
    } else {
        $error = "Failed to update assignment. Please try again.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Assignment - AI CRS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h3 class="mt-5">Edit Assignment</h3>
    <form action="edit_assignment.php?id=<?php echo $assignmentId; ?>" method="POST">
        <div class="mb-3">
            <label for="course_id" class="form-label">Course</label>
            <select name="course_id" id="course_id" class="form-select" required>
                <?php while ($course = $coursesResult->fetch_assoc()): ?>
                    <option value="<?php echo $course['id']; ?>" <?php echo ($assignment['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Assignment Title</label>
            <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4" required><?php echo htmlspecialchars($assignment['description']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date</label>
            <input type="date" name="due_date" id="due_date" class="form-control" value="<?php echo $assignment['due_date']; ?>" required>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Update Assignment</button>
        <a href="manage_assignments.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

</body>
</html>
