<?php
session_start();
include('../includes/db.php');

// Redirect if not logged in or not a student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$studentId = $_SESSION['user']['id'];
$studentName = $_SESSION['user']['name'];
$message = "";

// Handle course registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $courseId = intval($_POST['course_id']);

    // Check if already registered in the enrollments table
    $stmt = $conn->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $studentId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "<div class='alert alert-warning'>You are already enrolled in this course.</div>";
    } else {
        // Register the student in the course (insert into course_registrations table)
        $regTime = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO course_registrations (student_id, course_id, registered_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $studentId, $courseId, $regTime);

        if ($stmt->execute()) {
            // After successful registration, insert into enrollments table
            $stmtEnroll = $conn->prepare("INSERT INTO enrollments (student_id, course_id, enrolled_on) VALUES (?, ?, ?)");
            $stmtEnroll->bind_param("iis", $studentId, $courseId, $regTime);
            $stmtEnroll->execute();

            $message = "<div class='alert alert-success'>Course registered and enrollment successful.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to register course. Please try again.</div>";
        }
    }
    $stmt->close();
}

// Get available courses
$query = "
    SELECT id, course_code, title 
    FROM courses 
    WHERE id NOT IN (
        SELECT course_id 
        FROM course_registrations 
        WHERE student_id = ?
    )
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$availableCourses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Course - SmartEdu Portal</title>
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
        .form-container {
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

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        .form-container:hover {
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

        .alert-warning {
            background: rgba(255,193,7,0.1);
            color: var(--warning-color);
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

            .form-container {
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
            <a href="register_course.php" class="nav-link active">
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
        <h3>Register a Course</h3>
        <p class="text-muted">Select a course from the available options below</p>

        <?= $message ?>

        <div class="form-container fade-in">
            <form method="POST">
                <div class="mb-4">
                    <label for="course_id" class="form-label">
                        <i class="fas fa-book me-2"></i>
                        Select a Course
                    </label>
                    <select class="form-select" name="course_id" id="course_id" required>
                        <option value="">-- Choose a course --</option>
                        <?php while ($course = $availableCourses->fetch_assoc()): ?>
                            <option value="<?= $course['id'] ?>">
                                <?= htmlspecialchars($course['course_code'] . ' - ' . $course['title']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>
                    Register Course
                </button>
            </form>
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

    document.querySelectorAll('.form-container').forEach((el) => observer.observe(el));
</script>

</body>
</html>
