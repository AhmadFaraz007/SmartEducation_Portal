<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit;
}

$facultyId = $_SESSION['user']['id'];

$courses = $conn->query("SELECT * FROM courses WHERE faculty_id = '$facultyId'");
$quizzes = $conn->query("
    SELECT q.*, c.title AS course_title 
    FROM quizzes q 
    JOIN courses c ON q.course_id = c.id 
    WHERE c.faculty_id = '$facultyId'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Quizzes - Faculty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>🧠 Manage Quizzes</h3>

    <form method="POST" action="create_quiz.php" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="title" class="form-control" placeholder="Quiz Title" required>
        </div>
        <div class="col-md-4">
            <select name="course_id" class="form-select" required>
                <option value="">Select Course</option>
                <?php while ($c = $courses->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-success w-100">+ Create Quiz</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Quiz Title</th>
                <th>Course</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($q = $quizzes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($q['title']) ?></td>
                    <td><?= htmlspecialchars($q['course_title']) ?></td>
                    <td><?= $q['created_at'] ?></td>
                    <td>
                        <a href="manage_questions.php?quiz_id=<?= $q['id'] ?>" class="btn btn-sm btn-primary">Manage Questions</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
