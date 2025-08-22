<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user']['id'];
$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['rating'])) {
    $courseId = intval($_POST['course_id']);
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments']);

    // Prevent duplicate feedback
    $check = $conn->prepare("SELECT id FROM feedback WHERE student_id = ? AND course_id = ?");
    $check->bind_param("ii", $studentId, $courseId);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO feedback (student_id, course_id, rating, comments) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiis", $studentId, $courseId, $rating, $comments);
        $insert->execute();
        $msg = "✅ Feedback submitted successfully.";
    } else {
        $msg = "⚠️ You've already submitted feedback for this course.";
    }
}

// Get all courses for feedback
$coursesQuery = $conn->prepare("
    SELECT DISTINCT c.id, c.title, c.course_code, c.description 
    FROM courses c 
    JOIN enrollments e ON e.course_id = c.id 
    WHERE e.student_id = ?
");
$coursesQuery->bind_param("i", $studentId);
$coursesQuery->execute();
$courses = $coursesQuery->get_result();

// Get feedbacks
$feedbackQuery = $conn->prepare("
    SELECT f.rating, f.comments, c.title, c.course_code, c.description 
    FROM feedback f
    JOIN courses c ON f.course_id = c.id
    WHERE f.student_id = ?
    ORDER BY f.id DESC
");
$feedbackQuery->bind_param("i", $studentId);
$feedbackQuery->execute();
$feedbacks = $feedbackQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Feedback - SmartEdu Portal</title>
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

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: slideDown 0.5s ease;
        }

        .content-card h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .content-card p {
            color: #666;
            margin-bottom: 20px;
        }

        /* Form Styles */
        .feedback-form {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .feedback-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        .feedback-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0,64,128,0.1);
        }

        .btn-success {
            background: linear-gradient(45deg, var(--success-color), #218838);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.2);
        }

        /* Feedback Card Styles */
        .feedback-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .feedback-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        .feedback-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .star {
            color: var(--accent-color);
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .star:hover {
            transform: scale(1.2);
        }

        /* Alert Styles */
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

            .feedback-form, .feedback-card {
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
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link">
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
            <a href="course_feedback.php" class="nav-link active">
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
    <div class="content-card">
        <h3>📝 Course Feedback</h3>
        <p class="text-muted">Share your thoughts and experiences about your courses</p>

        <?php if ($msg): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <h5 class="text-secondary mb-3">Submit Feedback:</h5>
        <?php if ($courses->num_rows > 0): ?>
            <?php while ($course = $courses->fetch_assoc()): ?>
                <?php
                $check = $conn->prepare("SELECT id FROM feedback WHERE student_id = ? AND course_id = ?");
                $check->bind_param("ii", $studentId, $course['id']);
                $check->execute();
                $checkResult = $check->get_result();
                if ($checkResult->num_rows > 0) continue;
                ?>
                <form method="POST" class="feedback-form fade-in">
                    <h5 class="text-primary"><?php echo htmlspecialchars($course['title']); ?></h5>
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    <p><strong>Code:</strong> <?php echo htmlspecialchars($course['course_code']); ?></p>
                    <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                    <div class="mb-3">
                        <label class="form-label"><strong>Rating (1 to 5):</strong></label>
                        <select name="rating" class="form-select" required>
                            <option value="">Select Rating</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo str_repeat("⭐", $i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Comments:</strong></label>
                        <textarea name="comments" rows="3" class="form-control" placeholder="Write your feedback..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i>
                        Submit Feedback
                    </button>
                </form>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">No courses available for feedback.</p>
        <?php endif; ?>

        <hr class="my-4">

        <h5 class="text-secondary mb-3">Your Submitted Feedback:</h5>
        <div class="row">
            <?php if ($feedbacks->num_rows > 0): ?>
                <?php while ($row = $feedbacks->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="feedback-card fade-in">
                            <h5 class="text-primary"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p><strong>Code:</strong> <?php echo htmlspecialchars($row['course_code']); ?></p>
                            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <hr>
                            <p><strong>Rating:</strong>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star"><?php echo $i <= $row['rating'] ? '★' : '☆'; ?></span>
                                <?php endfor; ?>
                            </p>
                            <p><strong>Comments:</strong><br><?php echo nl2br(htmlspecialchars($row['comments'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">You haven't submitted any course feedback yet.</p>
            <?php endif; ?>
        </div>
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

    document.querySelectorAll('.feedback-form, .feedback-card').forEach((el) => observer.observe(el));
</script>

</body>
</html>
