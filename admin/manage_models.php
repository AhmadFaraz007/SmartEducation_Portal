<?php
session_start();
include('../includes/db.php');

// Redirect if not admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$name = $_SESSION['user']['name'];

// Handle search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = $search ? "WHERE name LIKE '%$search%' OR description LIKE '%$search%'" : '';
$sql = "SELECT id, name, description, file_path, created_at FROM models $whereClause ORDER BY created_at DESC";
$result = $conn->query($sql);

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM models WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Model deleted successfully!";
        header("Location: manage_models.php");
        exit;
    } else {
        $_SESSION['message'] = "Error deleting model!";
    }
}

// Handle model upload
if (isset($_POST['upload_model'])) {
    $model_name = $_POST['model_name'];
    $description = $_POST['description'];

    // Handle file upload
    $target_dir = "../uploads/models/";
    $target_file = $target_dir . basename($_FILES["model_file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a valid model
    if ($fileType != "h5" && $fileType != "pkl") {  // Assuming h5 or pkl models
        $_SESSION['message'] = "Sorry, only H5 and PKL files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1 && move_uploaded_file($_FILES["model_file"]["tmp_name"], $target_file)) {
        // Insert into database
        $insert_sql = "INSERT INTO models (name, description, file_path) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sss", $model_name, $description, $target_file);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Model uploaded successfully!";
            header("Location: manage_models.php");
            exit;
        } else {
            $_SESSION['message'] = "Error uploading model!";
        }
    } else {
        $_SESSION['message'] = "Sorry, there was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Models - SmartEdu Portal</title>
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

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: slideDown 0.5s ease;
        }

        .page-header h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0;
        }

        /* Upload Form */
        .upload-form {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: fadeIn 0.5s ease;
        }

        .upload-form .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }

        /* Search Form */
        .search-form {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            animation: fadeIn 0.5s ease;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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

        .btn-sm {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.875rem;
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
            animation: slideDown 0.5s ease;
        }

        .alert i {
            margin-right: 10px;
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
            <a href="manage_models.php" class="nav-link active">
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
    <div class="page-header">
        <h3>Manage Models</h3>
    </div>

    <!-- Show Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Upload Model Form -->
    <div class="upload-form">
        <h5 class="card-title">Upload New Model</h5>
        <form action="manage_models.php" method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="model_name" class="form-label">Model Name</label>
                    <input type="text" class="form-control" id="model_name" name="model_name" required>
                </div>
                <div class="col-md-8">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="1" required></textarea>
                </div>
                <div class="col-md-6">
                    <label for="model_file" class="form-label">Model File (H5/PKL)</label>
                    <input type="file" class="form-control" id="model_file" name="model_file" accept=".h5,.pkl" required>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" name="upload_model" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Model
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Search Form -->
    <div class="search-form">
        <form class="row g-3" method="GET">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name or description" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="manage_models.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Models Table -->
    <div class="table-container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>File Path</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </td>
                            <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                            <td>
                                <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this model?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-brain fa-2x mb-3"></i>
                            <p>No models found.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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

    document.querySelectorAll('.upload-form, .search-form, .table-container').forEach((el) => observer.observe(el));
</script>

</body>
</html>
