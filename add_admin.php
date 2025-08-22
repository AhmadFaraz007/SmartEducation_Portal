<?php
// add_admin.php — Only use for development or secured access

require_once 'includes/db.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $role     = 'admin';

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $success = "Admin account created successfully.";
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f2f2f2;
            padding: 50px;
        }
        .form-container {
            max-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin: auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin-top: 15px;
            color: #555;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            border: none;
            background: #007BFF;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .message {
            margin-top: 20px;
            text-align: center;
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create Admin</h2>
        <?php if (!empty($success)) echo "<p class='message'>$success</p>"; ?>
        <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

        <form method="POST" action="">
            <label for="name">Admin Name</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Admin Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Admin Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Create Admin</button>
        </form>
    </div>
</body>
</html>
