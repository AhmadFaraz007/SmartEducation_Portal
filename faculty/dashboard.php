<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit;
}

$name = $_SESSION['user']['name'];
$facultyId = $_SESSION['user']['id'];

$coursesCount = $conn->query("SELECT COUNT(*) AS total_courses FROM courses WHERE faculty_id = '$facultyId'")->fetch_assoc()['total_courses'];
$studentsCount = $conn->query("SELECT COUNT(DISTINCT student_id) AS total_students FROM enrollments WHERE course_id IN (SELECT id FROM courses WHERE faculty_id = '$facultyId')")->fetch_assoc()['total_students'];
$assignmentsResult = $conn->query("SELECT * FROM assignments WHERE course_id IN (SELECT id FROM courses WHERE faculty_id = '$facultyId') AND due_date >= CURDATE()");
$coursesResult = $conn->query("SELECT * FROM courses WHERE faculty_id = '$facultyId'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - SmartEdu Portal</title>
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

        .action-buttons .btn {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .action-buttons .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .action-buttons .btn-success {
            background: linear-gradient(45deg, var(--success-color), #34c759);
            border: none;
        }

        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Card Styles */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
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
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        .stat-card h6 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0;
        }

        /* Assignment List Styles */
        .assignment-list {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .assignment-item {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            background: var(--light-color);
            transition: all 0.3s ease;
        }

        .assignment-item:hover {
            transform: translateX(5px);
            background: #f0f2f5;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-today {
            background: var(--danger-color);
            color: white;
        }

        .badge-tomorrow {
            background: var(--warning-color);
            color: black;
        }

        .badge-upcoming {
            background: var(--success-color);
            color: white;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-top: 25px;
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

        .progress {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
        }

        .progress-bar {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 6px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
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
        <h4>👨‍🏫 Faculty Panel</h4>
    </div>
    <nav class="nav flex-column">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link active">
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
                    <h3>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h3>
                <p class="text-muted">Here's an overview of your teaching activity</p>
                </div>
            <div class="action-buttons">
                <a href="manage_courses.php" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> Add Course
                </a>
                <a href="manage_assignments.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Assignment
                </a>
            </div>
                </div>
            </div>

    <div class="row g-4">
                <div class="col-md-4">
            <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                        <h6>Total Courses</h6>
                <p class="stat-value"><?php echo $coursesCount; ?></p>
                    </div>
                </div>
                <div class="col-md-4">
            <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                        <h6>Total Students</h6>
                <p class="stat-value"><?php echo $studentsCount; ?></p>
                    </div>
                </div>
                <div class="col-md-4">
            <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                        <h6>Upcoming Assignments</h6>
                <div class="assignment-list mt-3">
                    <?php 
                    $assignmentsResult->data_seek(0);
                    while($assignment = $assignmentsResult->fetch_assoc()): 
                                $due = strtotime($assignment['due_date']);
                                $today = strtotime(date("Y-m-d"));
                                $badge = ($due == $today) ? 'today' : (($due == strtotime("+1 day", $today)) ? 'tomorrow' : 'upcoming');
                            ?>
                    <div class="assignment-item">
                                <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                        <span class="badge badge-<?php echo $badge; ?> float-end">
                                    Due: <?php echo date('M d', $due); ?>
                                </span>
                    </div>
                            <?php endwhile; ?>
                </div>
                    </div>
                </div>
            </div>

    <div class="table-container fade-in" style="animation-delay: 0.4s;">
        <h5 class="mb-4">Your Courses</h5>
            <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Course Code</th>
                            <th>Enrolled Students</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $coursesResult->data_seek(0);
                    while ($course = $coursesResult->fetch_assoc()):
                            $enrolled = $conn->query("SELECT COUNT(*) AS total FROM enrollments WHERE course_id = '{$course['id']}'")->fetch_assoc()['total'];
                            $percent = ($studentsCount > 0) ? round(($enrolled / $studentsCount) * 100) : 0;
                        ?>
                        <tr>
                          <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td><?php echo $enrolled; ?></td>
                            <td>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $percent; ?>%;" 
                                     aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </td>
                            <td>
                            <a href="manage_course.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-cog"></i> Manage
                            </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($coursesCount == 0): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No courses added yet.</td>
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

    document.querySelectorAll('.stat-card, .table-container').forEach((el) => observer.observe(el));
</script>

</body>
</html>
