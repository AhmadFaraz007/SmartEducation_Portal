<?php
session_start();
include('../includes/db.php');

// Check if the user is logged in and is a faculty
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit;
}

$name = $_SESSION['user']['name'];
$facultyId = $_SESSION['user']['id'];

// Fetch courses assigned to the faculty
$coursesResult = $conn->query("SELECT * FROM courses WHERE faculty_id = '$facultyId'");

// Fetch all assignments for the faculty's courses
$assignmentsResult = $conn->query("SELECT * FROM assignments WHERE course_id IN (SELECT id FROM courses WHERE faculty_id = '$facultyId') ORDER BY due_date DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - SmartEdu Portal</title>
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

        .btn-edit {
            background: white;
            color: var(--warning-color);
            border: 2px solid var(--warning-color);
            padding: 6px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-edit:hover {
            background: var(--warning-color);
            color: white;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: white;
            color: var(--danger-color);
            border: 2px solid var(--danger-color);
            padding: 6px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: var(--danger-color);
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
            <a href="manage_assignments.php" class="nav-link active">
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
                    <h3>Manage Assignments - Welcome, <?php echo htmlspecialchars($name); ?> 👋</h3>
                    <p class="text-muted">Add, update, or remove assignments for your courses</p>
                </div>
            <a href="add_assignment.php" class="btn btn-add">
                <i class="fas fa-plus"></i> Add Assignment
            </a>
                </div>
            </div>

    <div class="table-container">
            <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                        <tr>
                            <th>Course</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($assignment = $assignmentsResult->fetch_assoc()): 
                            $courseResult = $conn->query("SELECT title FROM courses WHERE id = '{$assignment['course_id']}' LIMIT 1");
                            $course = $courseResult->fetch_assoc();
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['description']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></td>
                            <td>
                            <a href="edit_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this assignment?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($assignmentsResult->num_rows == 0): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No assignments found.</td>
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
