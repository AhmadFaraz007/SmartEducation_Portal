<?php
include('../includes/db.php');
session_start();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Email already registered.";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registered successfully! Please login.";
            header("Location: login.php");
            exit;
        } else {
            $error = "Something went wrong. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SmartEdu Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #004080, #0073e6);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            transform: translateY(0);
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease;
        }

        .register-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h3 {
            color: #002244;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .register-header p {
            color: #666;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            color: #002244;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-group .input-group {
            position: relative;
        }

        .form-group .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #004080;
            font-size: 18px;
        }

        .form-control {
            padding: 12px 15px 12px 45px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: #004080;
            box-shadow: 0 0 0 3px rgba(0, 64, 128, 0.1);
            outline: none;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23004080' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }

        .btn-register {
            background: linear-gradient(45deg, #004080, #0073e6);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 64, 128, 0.3);
        }

        .btn-register::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #0073e6, #004080);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-register:hover::after {
            opacity: 1;
        }

        .btn-register span {
            position: relative;
            z-index: 1;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
        }

        .login-link a {
            color: #004080;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #0073e6;
        }

        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            border: none;
            animation: fadeIn 0.3s ease;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .role-select {
            position: relative;
        }

        .role-select i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #004080;
            font-size: 18px;
            z-index: 1;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
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
        @media (max-width: 576px) {
            .register-container {
                padding: 30px 20px;
            }

            .register-header h3 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="register-container">
    <div class="register-header">
        <h3>Create Account</h3>
        <p>Join SmartEdu Portal today</p>
    </div>
    
    <?php if ($error) echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> $error</div>"; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="name" required class="form-control" placeholder="Enter your full name">
            </div>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" required class="form-control" placeholder="Enter your email">
            </div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" required class="form-control" placeholder="Create a password">
            </div>
        </div>
        <div class="form-group">
            <label>Select Role</label>
            <div class="input-group role-select">
                <i class="fas fa-user-tag"></i>
                <select name="role" class="form-control" required>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-register">
            <span><i class="fas fa-user-plus"></i> Register</span>
        </button>
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login Now</a></p>
        </div>
    </form>
</div>

<script>
    // Add animation to form elements
    document.querySelectorAll('.form-control').forEach((input, index) => {
        input.style.animationDelay = `${index * 0.1}s`;
        input.classList.add('fadeInUp');
    });
</script>
</body>
</html>
