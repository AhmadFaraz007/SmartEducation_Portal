<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user']['id'];

if (!isset($_GET['quiz_id'])) {
    echo "Invalid request.";
    exit;
}

$quizId = intval($_GET['quiz_id']);

// Get quiz info
$quiz = $conn->query("SELECT title FROM quizzes WHERE id = $quizId")->fetch_assoc();
$quizTitle = $quiz ? $quiz['title'] : 'Unknown Quiz';

// Get result info
$result = $conn->query("SELECT score, submitted_at FROM quiz_results WHERE quiz_id = $quizId AND student_id = $studentId")->fetch_assoc();
if (!$result) {
    echo "Result not found.";
    exit;
}

// Get all questions
$questions = $conn->query("SELECT * FROM questions WHERE quiz_id = $quizId");

// Get student's answers
$studentAnswers = [];
$answersQuery = $conn->query("SELECT question_id, selected_option FROM quiz_answers WHERE quiz_id = $quizId AND student_id = $studentId");
while ($row = $answersQuery->fetch_assoc()) {
    $studentAnswers[$row['question_id']] = $row['selected_option'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Result - <?= htmlspecialchars($quizTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .correct { color: green; font-weight: bold; }
        .incorrect { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4 text-center">📊 Quiz Result: <?= htmlspecialchars($quizTitle) ?></h2>

    <div class="mb-4">
        <p><strong>Score:</strong> <?= $result['score'] ?> / <?= $questions->num_rows ?></p>
        <p><strong>Attempted on:</strong> <?= date("d M Y, h:i A", strtotime($result['submitted_at'])) ?></p>
    </div>

    <?php $qNo = 1; while ($question = $questions->fetch_assoc()): 
        $qid = $question['id'];
        $correct = $question['correct_option'];
        $selected = $studentAnswers[$qid] ?? 'Not Answered';
    ?>
        <div class="card mb-3">
            <div class="card-body">
                <h6><strong>Q<?= $qNo++ ?>:</strong> <?= htmlspecialchars($question['question_text']) ?></h6>
                <ul class="list-group mt-2">
                    <?php for ($i = 1; $i <= 4; $i++):
                        $opt = $question["option$i"];
                        $optionClass = '';
                        if ($i == $correct) $optionClass = 'list-group-item-success';
                        if ($i == $selected && $selected != $correct) $optionClass = 'list-group-item-danger';
                    ?>
                        <li class="list-group-item <?= $optionClass ?>">
                            <?= $i . ". " . htmlspecialchars($opt) ?>
                            <?php
                            if ($i == $correct) echo " <span class='badge bg-success ms-2'>Correct Answer</span>";
                            if ($i == $selected && $selected != $correct) echo " <span class='badge bg-danger ms-2'>Your Answer</span>";
                            if ($i == $selected && $selected == $correct) echo " <span class='badge bg-success ms-2'>✔ Your Answer</span>";
                            ?>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        </div>
    <?php endwhile; ?>

    <div class="text-center mt-4">
        <a href="quiz.php" class="btn btn-secondary">← Back to Quizzes</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
