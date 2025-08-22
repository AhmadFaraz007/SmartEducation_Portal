<?php
session_start();
include('../includes/db.php');

// Check if user is logged in and is a faculty
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit;
}

$name = $_SESSION['user']['name'];
$facultyId = $_SESSION['user']['id'];

// Fetch all courses taught by this faculty
$coursesQuery = $conn->query("SELECT * FROM courses WHERE faculty_id = '$facultyId'");
$courseIds = [];

while ($row = $coursesQuery->fetch_assoc()) {
    $courseIds[] = $row['id'];
}

$courseIdsStr = implode(',', $courseIds);

// Fetch enrolled students in those courses
$studentsResult = $conn->query("
    SELECT users.id AS student_id, users.name AS student_name, users.email, courses.title AS course_title
    FROM enrollments
    JOIN users ON enrollments.student_id = users.id
    JOIN courses ON enrollments.course_id = courses.id
    WHERE courses.id IN ($courseIdsStr)
    ORDER BY users.name ASC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - SmartEdu Portal</title>
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

        /* Student Card Styles */
        .student-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .student-details h5 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }

        .student-details p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
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
            <a href="manage_courses.php" class="nav-link">
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
            <a href="students.php" class="nav-link active">
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
                    <h3>Enrolled Students - Welcome, <?php echo htmlspecialchars($name); ?> 👋</h3>
                    <p class="text-muted">View students enrolled in your courses</p>
            </div>
                </div>
            </div>

    <div class="table-container">
            <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Course</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($studentsResult && $studentsResult->num_rows > 0): ?>
                            <?php while ($student = $studentsResult->fetch_assoc()): ?>
                                <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            <?php echo strtoupper(substr($student['student_name'], 0, 1)); ?>
                                        </div>
                                        <div class="student-details">
                                            <h5><?php echo htmlspecialchars($student['student_name']); ?></h5>
                                        </div>
                                    </div>
                                </td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['course_title']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No students enrolled in your courses.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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

    document.querySelectorAll('.table-container').forEach((el) => observer.observe(el));
</script>

</body>
</html>
