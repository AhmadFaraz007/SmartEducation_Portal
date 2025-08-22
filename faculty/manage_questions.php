<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit;
}

$quiz_id = $_GET['quiz_id'] ?? null;

if (!$quiz_id) {
    echo "Quiz ID is required.";
    exit;
}

// Fetch quiz details
$quiz = $conn->query("SELECT * FROM quizzes WHERE id = '$quiz_id'")->fetch_assoc();
$questions = $conn->query("SELECT * FROM questions WHERE quiz_id = '$quiz_id'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Questions - <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>📋 Manage Questions for Quiz: <?= htmlspecialchars($quiz['title']) ?></h3>

    <form method="POST" action="add_question.php" class="mb-4">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
        <div class="mb-2">
            <label>Question</label>
            <textarea name="question_text" class="form-control" required></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-2"><input name="option1" class="form-control" placeholder="Option 1" required></div>
            <div class="col-md-6 mb-2"><input name="option2" class="form-control" placeholder="Option 2" required></div>
            <div class="col-md-6 mb-2"><input name="option3" class="form-control" placeholder="Option 3" required></div>
            <div class="col-md-6 mb-2"><input name="option4" class="form-control" placeholder="Option 4" required></div>
        </div>
        <div class="mb-2">
            <label>Correct Option</label>
            <select name="correct_option" class="form-select" required>
                <option value="">Select correct option</option>
                <option value="option1">Option 1</option>
                <option value="option2">Option 2</option>
                <option value="option3">Option 3</option>
                <option value="option4">Option 4</option>
            </select>
        </div>
        <button class="btn btn-success w-100">➕ Add Question</button>
    </form>

    <hr>

    <h5>📌 Existing Questions</h5>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Question</th>
                <th>Options</th>
                <th>Correct</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($q = $questions->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($q['question_text']) ?></td>
                    <td>
                        1. <?= htmlspecialchars($q['option1']) ?><br>
                        2. <?= htmlspecialchars($q['option2']) ?><br>
                        3. <?= htmlspecialchars($q['option3']) ?><br>
                        4. <?= htmlspecialchars($q['option4']) ?>
                    </td>
                    <td><?= ucfirst($q['correct_option']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
