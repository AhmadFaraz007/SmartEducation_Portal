<?php
include('../includes/db.php');
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            "id" => $user['id'],
            "name" => $user['name'],
            "role" => $user['role']
        ];

        // Redirect based on role
        $role = $user['role'];
        if ($role == "admin") header("Location: ../admin/dashboard.php");
        else if ($role == "student") header("Location: ../student/dashboard.php");
        else if ($role == "faculty") header("Location: ../faculty/dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartEdu Portal</title>
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

        .login-container {
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

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h3 {
            color: #002244;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-header p {
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

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 64, 128, 0.3);
        }

        .btn-login::after {
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

        .btn-login:hover::after {
            opacity: 1;
        }

        .btn-login span {
            position: relative;
            z-index: 1;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
        }

        .register-link a {
            color: #004080;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
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

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
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
            .login-container {
                padding: 30px 20px;
            }

            .login-header h3 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <h3>Welcome Back!</h3>
        <p>Login to access your SmartEdu Portal</p>
    </div>
    
    <?php if ($error) echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> $error</div>"; ?>
    <?php if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> " . $_SESSION['success'] . "</div>";
        unset($_SESSION['success']);
    } ?>

    <form method="POST">
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
                <input type="password" name="password" required class="form-control" placeholder="Enter your password">
            </div>
        </div>
        <button type="submit" class="btn-login">
            <span><i class="fas fa-sign-in-alt"></i> Login</span>
        </button>
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register Now</a></p>
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
