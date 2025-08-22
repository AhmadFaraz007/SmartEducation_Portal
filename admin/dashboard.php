<?php
session_start();
include('../includes/db.php');

// Redirect if not logged in or not admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$name = $_SESSION['user']['name'];

// Fetch data
$totalUsersResult = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalUsers = $totalUsersResult->fetch_assoc()['total'];

$totalFacultyResult = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'faculty'");
$totalFaculty = $totalFacultyResult->fetch_assoc()['total'];

$totalStudentsResult = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'student'");
$totalStudents = $totalStudentsResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SmartEdu Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
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
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
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
            background: linear-gradient(45deg, var(--accent-color), #2980b9);
        }

        .stat-card.bg-green::before {
            background: linear-gradient(45deg, var(--success-color), #27ae60);
        }

        .stat-card.bg-orange::before {
            background: linear-gradient(45deg, var(--warning-color), #f39c12);
        }

        .stat-card h5 {
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

        /* System Overview */
        .system-overview {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: fadeIn 0.5s ease;
        }

        .system-overview h5 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .system-overview ul {
            list-style: none;
            padding: 0;
        }

        .system-overview li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        .system-overview li:last-child {
            border-bottom: none;
        }

        .system-overview li i {
            color: var(--accent-color);
            margin-right: 10px;
            font-size: 1.1rem;
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
        <h4>👨‍💼 Admin Panel</h4>
    </div>
    <nav class="nav flex-column">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="manage_users.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="manage_models.php" class="nav-link">
                <i class="fas fa-brain"></i>
                <span>Manage Models</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="system_stats.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                <span>System Stats</span>
            </a>
        </div>
        <div class="nav-item mt-auto">
            <a href="../auth/logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</div>

<div class="main-content">
    <div class="welcome-section">
        <h3>Welcome, <?php echo htmlspecialchars($name); ?> 👋</h3>
        <p class="text-muted">AI-Based Course Recommendation System (Admin View)</p>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="stat-card bg-blue fade-in" style="animation-delay: 0.1s;">
                <h5>Total Users</h5>
                <p class="stat-value"><?php echo $totalUsers; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-green fade-in" style="animation-delay: 0.2s;">
                <h5>Total Faculty</h5>
                <p class="stat-value"><?php echo $totalFaculty; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-orange fade-in" style="animation-delay: 0.3s;">
                <h5>Total Students</h5>
                <p class="stat-value"><?php echo $totalStudents; ?></p>
            </div>
        </div>
    </div>

    <!-- System Overview -->
    <div class="system-overview fade-in" style="animation-delay: 0.4s;">
        <h5>System Overview</h5>
        <ul>
            <li><i class="fas fa-user-plus"></i> Manage and register faculty and students</li>
            <li><i class="fas fa-robot"></i> Upload and retrain ML models</li>
            <li><i class="fas fa-chart-pie"></i> View system analytics and usage stats</li>
        </ul>
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

    document.querySelectorAll('.stat-card, .system-overview').forEach((el) => observer.observe(el));
</script>

</body>
</html>
