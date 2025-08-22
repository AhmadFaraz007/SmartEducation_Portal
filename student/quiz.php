<?php
session_start();
include('../includes/db.php');

// Ensure only students can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user']['id'];

// Fetch all quizzes
$quizQuery = $conn->query("SELECT id, title, created_at FROM quizzes ORDER BY created_at DESC");

// Fetch all attempts by the student from quiz_results
$attempts = [];
$attemptQuery = $conn->query("SELECT quiz_id, score FROM quiz_results WHERE student_id = '$studentId'");
while ($row = $attemptQuery->fetch_assoc()) {
    $attempts[$row['quiz_id']] = $row['score'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quizzes - SmartEdu Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #004080;
            --secondary-color: #0073e6;
            --accent-color: #ffcc00;
            --dark-color: #1e1e2f;
            --light-color: #f8f9fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: var(--light-color);
            min-height: 100vh;
        }
        .sidebar {
            height: 100vh;
            background: linear-gradient(180deg, var(--dark-color), #2a2a3c);
            color: white;
            position: fixed;
            width: 250px;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
        }
        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent-color);
        }
        .nav-item {
            padding: 0;
            margin: 5px 0;
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--accent-color);
        }
        .nav-link i {
            width: 20px;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: slideDown 0.5s ease;
        }
        .content-card h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 5px;
        }
        .quiz-card {
            background: linear-gradient(135deg, #e3f0ff 60%, #f8f9fa 100%);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 25px 20px;
            margin-bottom: 25px;
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .quiz-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 25px rgba(0,64,128,0.08);
        }
        .quiz-card .card-title {
            color: var(--primary-color);
            font-weight: 600;
        }
        .quiz-card .badge {
            font-size: 1rem;
            padding: 0.5em 1em;
            border-radius: 8px;
        }
        .quiz-card .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .quiz-card .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
        }
        .quiz-card .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        .quiz-card .btn-outline-primary:hover {
            background: var(--primary-color);
            color: #fff;
        }
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
            animation: slideDown 0.5s ease;
        }
        .alert-info {
            background: rgba(0,123,255,0.1);
            color: var(--primary-color);
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            .sidebar-header h4 {
                display: none;
            }
            .nav-link span {
                display: none;
            }
            .main-content {
                margin-left: 70px;
            }
            .quiz-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <h4>🎓 Student Panel</h4>
    </div>
    <nav class="nav flex-column">
        <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></div>
        <div class="nav-item"><a href="input_interests.php" class="nav-link"><i class="fas fa-star"></i><span>Input Interests</span></a></div>
        <div class="nav-item"><a href="recommended_courses.php" class="nav-link"><i class="fas fa-book"></i><span>Recommended Courses</span></a></div>
        <div class="nav-item"><a href="course_feedback.php" class="nav-link"><i class="fas fa-comment-alt"></i><span>Course Feedback</span></a></div>
        <div class="nav-item"><a href="submit_assignment.php" class="nav-link"><i class="fas fa-tasks"></i><span>Submit Assignment</span></a></div>
        <div class="nav-item"><a href="register_course.php" class="nav-link"><i class="fas fa-plus-circle"></i><span>Register Course</span></a></div>
        <div class="nav-item"><a href="quiz.php" class="nav-link active"><i class="fas fa-question-circle"></i><span>Quizzes</span></a></div>
        <div class="nav-item"><a href="complete_profile.php" class="nav-link"><i class="fas fa-user-edit"></i><span>Complete Profile</span></a></div>
        <div class="nav-item mt-auto"><a href="../auth/logout.php" class="nav-link text-danger" onclick="return confirm('Are you sure you want to logout?');"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
    </nav>
</div>
<div class="main-content">
    <div class="content-card">
        <h2 class="mb-4"><i class="fas fa-clipboard-list me-2"></i>My Quizzes</h2>
        <?php if ($quizQuery->num_rows > 0): ?>
            <div class="row">
                <?php while ($quiz = $quizQuery->fetch_assoc()): 
                    $quizId = $quiz['id'];
                    $title = $quiz['title'];
                    $createdAt = date("d M Y", strtotime($quiz['created_at']));
                    $attempted = isset($attempts[$quizId]);
                    $score = $attempted ? $attempts[$quizId] : null;
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="quiz-card fade-in">
                            <h5 class="card-title mb-2"><i class="fas fa-question-circle me-2"></i><?= htmlspecialchars($title) ?></h5>
                            <p class="text-muted mb-2"><i class="fas fa-calendar-alt me-1"></i>Created on: <?= $createdAt ?></p>
                            <span class="badge bg-<?= $attempted ? 'success' : 'warning' ?>">
                                <?= $attempted ? "Attempted (Score: $score)" : "Not Attempted" ?>
                            </span>
                            <div class="mt-3">
                                <?php if ($attempted): ?>
                                    <a href="view_result.php?quiz_id=<?= $quizId ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i>View Questions</a>
                                <?php else: ?>
                                    <a href="attempt_quiz.php?quiz_id=<?= $quizId ?>" class="btn btn-primary btn-sm"><i class="fas fa-play me-1"></i>Attempt Quiz</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="alert alert-info text-center"><i class="fas fa-info-circle me-2"></i>No quizzes available yet. Please check back later.</p>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add animation to elements when they come into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    });
    document.querySelectorAll('.quiz-card').forEach((el) => observer.observe(el));
</script>
</body>
</html>
