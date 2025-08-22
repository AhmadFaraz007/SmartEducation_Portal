<?php
session_start();
include('../includes/db.php');

// Redirect if not logged in or not faculty
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit;
}

$faculty_id = $_SESSION['user']['id'];
$name = $_SESSION['user']['name'];

// Handle course addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = $conn->real_escape_string($_POST['course_code']);
    $title = $conn->real_escape_string($_POST['course_name']);
    $description = $conn->real_escape_string($_POST['description']);

    $conn->query("INSERT INTO courses (course_code, title, description, faculty_id) VALUES ('$course_code', '$title', '$description', '$faculty_id')");
    header("Location: manage_courses.php?added=1");
    exit;
}

// Fetch courses
$courses = $conn->query("SELECT * FROM courses WHERE faculty_id = '$faculty_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - SmartEdu Portal</title>
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
            text-decoration: none;
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

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(0,64,128,0.05);
            transform: translateX(5px);
        }

        /* Button Styles */
        .btn-add {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,64,128,0.2);
            color: white;
        }

        .btn-manage {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 6px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-manage:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }

        .modal-body {
            padding: 25px;
        }

        .form-control {
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0,64,128,0.1);
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            border: none;
            animation: fadeIn 0.3s ease;
        }

        .alert-success {
            background: rgba(40,167,69,0.1);
            color: var(--success-color);
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

            .table-responsive {
                margin: 0 -15px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h4>👨‍🏫 Faculty Panel</h4>
    </div>
    <nav class="nav flex-column">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="manage_courses.php" class="nav-link active">
                <i class="fas fa-book"></i>
                <span>Manage Courses</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="manage_assignments.php" class="nav-link">
                <i class="fas fa-tasks"></i>
                <span>Assignments</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="students.php" class="nav-link">
                <i class="fas fa-user-graduate"></i>
                <span>Students</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="faculty_tts_tools.php" class="nav-link">
                <i class="fas fa-microphone"></i>
                <span>TTS Tools</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="manage_quizzes.php" class="nav-link">
                <i class="fas fa-question-circle"></i>
                <span>Quizzes</span>
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
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Manage Courses - Welcome, <?php echo htmlspecialchars($name); ?> 👋</h3>
                <p class="text-muted">Add, update, or remove courses from your portfolio</p>
            </div>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="fas fa-plus"></i> Add New Course
            </button>
        </div>
    </div>

    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Course added successfully.
        </div>
    <?php endif; ?>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($course['description'])); ?></td>
                            <td>
                                <a href="manage_course.php?id=<?php echo $course['id']; ?>" class="btn btn-manage">
                                    <i class="fas fa-cog"></i> Manage
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($courses->num_rows == 0): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No courses added yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="manage_courses.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="course_code" class="form-label">Course Code</label>
                        <input type="text" name="course_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="course_name" class="form-label">Course Title</label>
                        <input type="text" name="course_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (optional)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-add">Add Course</button>
                </div>
            </div>
        </form>
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

    document.querySelectorAll('.table-container').forEach((el) => observer.observe(el));
</script>

</body>
</html>
