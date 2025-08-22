<?php
session_start();
include('../includes/db.php');

// Check if user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user']['id'];
$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interest'])) {
    $interest = trim($_POST['interest']);

    if (!empty($interest)) {
        $stmt = $conn->prepare("INSERT INTO student_interests (student_id, interest, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $studentId, $interest);

        if ($stmt->execute()) {
            $successMessage = "Interest added successfully!";
        } else {
            $errorMessage = "Error adding interest. Please try again.";
        }
    } else {
        $errorMessage = "Please provide an interest.";
    }
}

// Fetch all interests
$interestsResult = $conn->query("SELECT interest FROM student_interests WHERE student_id = '$studentId' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Interests - SmartEdu Portal</title>
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
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0,64,128,0.1);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,64,128,0.2);
        }

        /* Interest List Styles */
        .interest-list {
            margin-top: 30px;
        }

        .interest-item {
            background: var(--light-color);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease;
        }

        .interest-item:hover {
            transform: translateX(5px);
            background: #f0f2f5;
        }

        .interest-item i {
            color: var(--primary-color);
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
            animation: slideDown 0.5s ease;
        }

        .alert-success {
            background: rgba(40,167,69,0.1);
            color: var(--success-color);
        }

        .alert-danger {
            background: rgba(220,53,69,0.1);
            color: var(--danger-color);
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
            <a href="input_interests.php" class="nav-link active">
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
    <div class="content-card">
        <h3>✨ Input Your Interests</h3>
        <p class="text-muted">Enter your areas of interest to receive personalized course recommendations</p>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="input_interests.php" class="mb-4">
            <div class="mb-3">
                <label for="interest" class="form-label">Interest Area</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-star"></i>
                    </span>
                    <input type="text" class="form-control" id="interest" name="interest" required 
                           placeholder="Enter your interest (e.g., Web Development, Data Science)">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>
                Add Interest
            </button>
        </form>

        <div class="interest-list">
            <h4 class="mb-4">📌 Your Current Interests</h4>
            <?php if ($interestsResult->num_rows > 0): ?>
                <?php while ($row = $interestsResult->fetch_assoc()): ?>
                    <div class="interest-item">
                        <i class="fas fa-star"></i>
                        <span><?= htmlspecialchars($row['interest']) ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No interests added yet. Start entering your areas of interest above!</p>
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

    document.querySelectorAll('.interest-item').forEach((el) => observer.observe(el));
</script>

</body>
</html>
