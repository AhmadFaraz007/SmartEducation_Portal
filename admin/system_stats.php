<?php
session_start();
include('../includes/db.php');

// Redirect if not logged in or not admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$name = $_SESSION['user']['name'];

// ------------------- System Information ------------------- //

// Server Uptime (cross-platform fallback)
$uptime = 'Unavailable';
if (stristr(PHP_OS, 'WIN')) {
    $uptime = shell_exec('net stats workstation');
    if ($uptime) {
        preg_match("/since (.*)/", $uptime, $matches);
        $uptime = $matches[1] ?? 'Unavailable';
    }
} else {
    $uptime = shell_exec('uptime -p'); // Linux-style
}

// Total Models Uploaded
$totalModels = 0;
if ($result = $conn->query("SELECT COUNT(*) AS total FROM models")) {
    $totalModels = $result->fetch_assoc()['total'];
}

// Database Size
$dbSize = 'Unavailable';
if ($result = $conn->query("
    SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS db_size_mb
    FROM information_schema.tables 
    WHERE table_schema = 'ai_crs'")) {
    $dbSize = $result->fetch_assoc()['db_size_mb'] . " MB";
}

// Active Users Today
$activeUsersToday = 0;
if ($conn->query("SHOW TABLES LIKE 'user_activity'")->num_rows > 0) {
    $result = $conn->query("SELECT COUNT(DISTINCT user_id) AS active_today 
                            FROM user_activity 
                            WHERE DATE(login_time) = CURDATE()");
    if ($result) {
        $activeUsersToday = $result->fetch_assoc()['active_today'];
    }
}

// Additional Stats
$phpVersion = phpversion();
$memoryUsage = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$diskFree = round(disk_free_space("/") / 1024 / 1024 / 1024, 2);
$diskTotal = round(disk_total_space("/") / 1024 / 1024 / 1024, 2);
$diskUsed = $diskTotal - $diskFree;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Stats - SmartEdu Portal</title>
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
        .stats-header {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: slideDown 0.5s ease;
        }
        .stats-header h3 {
            color: var(--primary-color);
            font-weight: 600;
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
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .stat-card .icon {
            font-size: 2.2rem;
            color: var(--accent-color);
            flex-shrink: 0;
        }
        .stat-card .stat-label {
            color: #666;
            font-size: 1rem;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0;
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
            <a href="dashboard.php" class="nav-link">
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
            <a href="system_stats.php" class="nav-link active">
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
    <div class="stats-header">
        <h3>System Stats - Welcome, <?php echo htmlspecialchars($name); ?> 👋</h3>
        <p class="text-muted">View detailed system information</p>
    </div>

    <!-- System Stats Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                <span class="icon"><i class="fas fa-clock"></i></span>
                <div>
                    <div class="stat-label">Server Uptime</div>
                    <div class="stat-value text-info"><?php echo htmlspecialchars($uptime); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                <span class="icon"><i class="fas fa-upload"></i></span>
                <div>
                    <div class="stat-label">Total Models Uploaded</div>
                    <div class="stat-value text-success"><?php echo $totalModels; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                <span class="icon"><i class="fas fa-database"></i></span>
                <div>
                    <div class="stat-label">Database Size</div>
                    <div class="stat-value text-warning"><?php echo $dbSize; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- More Stats -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card fade-in" style="animation-delay: 0.4s;">
                <span class="icon"><i class="fas fa-user-check"></i></span>
                <div>
                    <div class="stat-label">Active Users Today</div>
                    <div class="stat-value text-primary"><?php echo $activeUsersToday; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card fade-in" style="animation-delay: 0.5s;">
                <span class="icon"><i class="fab fa-php"></i></span>
                <div>
                    <div class="stat-label">PHP Version</div>
                    <div class="stat-value text-secondary"><?php echo $phpVersion; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card fade-in" style="animation-delay: 0.6s;">
                <span class="icon"><i class="fas fa-memory"></i></span>
                <div>
                    <div class="stat-label">Memory Usage (PHP)</div>
                    <div class="stat-value text-secondary"><?php echo $memoryUsage; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Disk & Server Info -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="stat-card fade-in" style="animation-delay: 0.7s;">
                <span class="icon"><i class="fas fa-server"></i></span>
                <div>
                    <div class="stat-label">Server Software</div>
                    <div class="stat-value text-dark"><?php echo $serverSoftware; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card fade-in" style="animation-delay: 0.8s;">
                <span class="icon"><i class="fas fa-hdd"></i></span>
                <div>
                    <div class="stat-label">Disk Usage</div>
                    <div class="stat-value text-dark"><?php echo "$diskUsed GB / $diskTotal GB used"; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="fade-in" style="animation-delay: 0.9s;">
        <hr class="my-4">
        <h5>Additional Notes</h5>
        <ul>
            <li><i class="fas fa-tachometer-alt"></i> Server Load: <?php echo htmlspecialchars($uptime); ?></li>
            <li><i class="fas fa-history"></i> ML Model Training History: <span class="text-muted">[Coming Soon]</span></li>
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
    document.querySelectorAll('.stat-card, .fade-in').forEach((el) => observer.observe(el));
</script>

</body>
</html>
