<?php
session_start();
include('../includes/db.php');

// Ensure user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentName = $_SESSION['user']['name'];
$studentId = $_SESSION['user']['id'];

// Count totals
$interestQuery = $conn->query("SELECT COUNT(*) AS total FROM student_interests WHERE student_id = '$studentId'");
$totalInterests = $interestQuery->fetch_assoc()['total'];

$recommendQuery = $conn->query("
    SELECT COUNT(DISTINCT recommended_course_id) AS total 
    FROM recommendations 
    WHERE student_id = '$studentId'
");
$totalRecommendations = $recommendQuery->fetch_assoc()['total'];

$feedbackQuery = $conn->query("SELECT COUNT(*) AS total FROM feedback WHERE student_id = '$studentId'");
$totalFeedbacks = $feedbackQuery->fetch_assoc()['total'];

// Fetch quiz attempts for the student
$quizAttemptQuery = $conn->query("
    SELECT q.title AS quiz_title, qa.score, qa.attempted_at
    FROM student_quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    WHERE qa.student_id = '$studentId'
    ORDER BY qa.attempted_at DESC
");

// Fetch assignments for registered courses
$assignmentQuery = $conn->query("
    SELECT a.*, c.title AS course_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN course_registrations cr ON cr.course_id = a.course_id
    WHERE cr.student_id = '$studentId'
    ORDER BY a.due_date ASC
");

// Fetch student's submitted assignments
$submissionQuery = $conn->query("
    SELECT s.*, a.title AS assignment_title
    FROM assignment_submissions s
    JOIN assignments a ON s.assignment_id = a.id
    WHERE s.student_id = '$studentId'
    ORDER BY s.submitted_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - SmartEdu Portal</title>
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

        /* Sidebar Styles */
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

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: slideDown 0.5s ease;
        }

        .welcome-section h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: #666;
            margin-bottom: 0;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card.bg-blue::before {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }

        .stat-card.bg-green::before {
            background: linear-gradient(45deg, #28a745, #1e7e34);
        }

        .stat-card.bg-orange::before {
            background: linear-gradient(45deg, #fd7e14, #d35400);
        }

        .stat-card h4 {
            color: #666;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-top: 25px;
            animation: fadeIn 0.5s ease;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,0.02);
        }

        /* List Group Styles */
        .list-group-item {
            border: none;
            margin-bottom: 10px;
            border-radius: 10px !important;
            background: var(--light-color);
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            transform: translateX(5px);
            background: #f0f2f5;
        }

        /* Animations */
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

        /* Responsive Design */
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

            .stat-card {
                margin-bottom: 20px;
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
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="input_interests.php" class="nav-link">
                <i class="fas fa-star"></i>
                <span>Input Interests</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="recommended_courses.php" class="nav-link">
                <i class="fas fa-book"></i>
                <span>Recommended Courses</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="course_feedback.php" class="nav-link">
                <i class="fas fa-comment-alt"></i>
                <span>Course Feedback</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="submit_assignment.php" class="nav-link">
                <i class="fas fa-tasks"></i>
                <span>Submit Assignment</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="register_course.php" class="nav-link">
                <i class="fas fa-plus-circle"></i>
                <span>Register Course</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="quiz.php" class="nav-link">
                <i class="fas fa-question-circle"></i>
                <span>Quizzes</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="complete_profile.php" class="nav-link">
                <i class="fas fa-user-edit"></i>
                <span>Complete Profile</span>
            </a>
        </div>
        <div class="nav-item mt-auto">
            <a href="../auth/logout.php" class="nav-link text-danger" onclick="return confirm('Are you sure you want to logout?');">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</div>

<div class="main-content">
    <div class="welcome-section">
        <h3>Welcome, <?php echo htmlspecialchars($studentName); ?> 👋</h3>
        <p class="text-muted">Explore your personalized AI-powered course recommendations</p>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card bg-blue fade-in" style="animation-delay: 0.1s;">
                <h4>Total Interests</h4>
                <p class="stat-value"><?php echo $totalInterests; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-green fade-in" style="animation-delay: 0.2s;">
                <h4>Recommended Courses</h4>
                <p class="stat-value"><?php echo $totalRecommendations; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-orange fade-in" style="animation-delay: 0.3s;">
                <h4>Feedbacks Submitted</h4>
                <p class="stat-value"><?php echo $totalFeedbacks; ?></p>
            </div>
        </div>
    </div>

    <!-- Quiz Attempts Section -->
    <div class="table-container fade-in" style="animation-delay: 0.4s;">
        <h5 class="mb-4">🎮 Your Quiz Attempts</h5>
        <?php if ($quizAttemptQuery->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Quiz Title</th>
                            <th>Score</th>
                            <th>Attempted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $quizAttemptQuery->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['quiz_title']) ?></td>
                                <td><?= htmlspecialchars($row['score']) ?></td>
                                <td><?= htmlspecialchars($row['attempted_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No quiz attempts found. Start attempting your quizzes!</p>
        <?php endif; ?>
    </div>

    <!-- Assignments Section -->
    <div class="table-container fade-in" style="animation-delay: 0.5s;">
        <h5 class="mb-4">📌 Assignments for Your Courses</h5>
        <?php if ($assignmentQuery->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $assignmentQuery->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['course_name']) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['due_date']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No assignments found for your registered courses.</p>
        <?php endif; ?>
    </div>

    <!-- Submitted Assignments Section -->
    <div class="table-container fade-in" style="animation-delay: 0.6s;">
        <h5 class="mb-4">📝 Your Submitted Assignments</h5>
        <?php if ($submissionQuery->num_rows > 0): ?>
            <div class="list-group">
                <?php while ($row = $submissionQuery->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <strong><?= htmlspecialchars($row['assignment_title']) ?></strong>
                        <p class="mb-1"><?= nl2br(htmlspecialchars($row['submission_text'])) ?></p>
                        <small class="text-muted">Submitted at: <?= htmlspecialchars($row['submitted_at']) ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No submissions yet. Start submitting your assignments!</p>
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

    document.querySelectorAll('.stat-card, .table-container').forEach((el) => observer.observe(el));
</script>

</body>
</html>
