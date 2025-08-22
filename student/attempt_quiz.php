<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$quiz_id = $_GET['quiz_id'] ?? null;
$student_id = $_SESSION['user']['id'];

if (!$quiz_id) {
    echo "Quiz ID is required.";
    exit;
}

// Check if already attempted
$check = $conn->query("SELECT * FROM student_answers WHERE student_id='$student_id' AND quiz_id='$quiz_id'");
if ($check->num_rows > 0) {
    echo "<h3>❗ You have already attempted this quiz.</h3>";
    exit;
}

$quiz = $conn->query("SELECT * FROM quizzes WHERE id = '$quiz_id'")->fetch_assoc();
$questions = $conn->query("SELECT * FROM questions WHERE quiz_id = '$quiz_id'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attempt Quiz - <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>📝 Attempt Quiz: <?= htmlspecialchars($quiz['title']) ?></h3>
    <form action="submit_quiz.php" method="POST">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
        <?php $index = 1; ?>
        <?php while ($q = $questions->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Q<?= $index++ ?>: <?= htmlspecialchars($q['question_text']) ?></strong>
                </div>
                <div class="card-body">
                    <input type="hidden" name="question_ids[]" value="<?= $q['id'] ?>">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="option1" required>
                        <label class="form-check-label"><?= htmlspecialchars($q['option1']) ?></label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="option2" required>
                        <label class="form-check-label"><?= htmlspecialchars($q['option2']) ?></label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="option3" required>
                        <label class="form-check-label"><?= htmlspecialchars($q['option3']) ?></label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" value="option4" required>
                        <label class="form-check-label"><?= htmlspecialchars($q['option4']) ?></label>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        <button type="submit" class="btn btn-primary w-100">✅ Submit Quiz</button>
    </form>
</body>
</html>
