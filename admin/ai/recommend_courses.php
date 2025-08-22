<?php
$studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Course Recommendations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 70%;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #0073e6;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background: #e9f3ff;
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid #0073e6;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }
        .back-link {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            color: #0073e6;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .input-form {
            margin-bottom: 30px;
        }
        .input-form input[type="number"] {
            padding: 10px;
            width: 120px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .input-form input[type="submit"] {
            padding: 10px 20px;
            border: none;
            background-color: #0073e6;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 10px;
        }
        .input-form input[type="submit"]:hover {
            background-color: #005bb5;
        }
        pre.debug {
            background: #f4f4f4;
            border: 1px dashed #ccc;
            padding: 10px;
            margin-top: 20px;
            font-size: 13px;
            color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>AI-Based Course Recommendations</h2>

    <!-- Student ID Input Form -->
    <form method="GET" class="input-form">
        <label for="student_id">Enter Student ID:</label>
        <input type="number" name="student_id" id="student_id" value="<?php echo htmlspecialchars($studentId); ?>" required>
        <input type="submit" value="Get Recommendations">
    </form>

    <?php
    if ($studentId > 0) {
        // Get absolute path for safety
        $scriptPath = realpath(__DIR__ . "/../../ml_api/recommend_courses.py");
        $python = "python"; // or "python3" or full path like "C:\\Python311\\python.exe"

        $command = escapeshellcmd("$python $scriptPath $studentId");
        $output = shell_exec($command);

        echo "<pre class='debug'>Raw Output:\n" . htmlspecialchars($output) . "</pre>";

        $recommendations = json_decode($output, true);

        if (is_array($recommendations)) {
            echo "<h3>Recommended Courses for Student ID <strong>$studentId</strong>:</h3><ul>";
            foreach ($recommendations as $course) {
                echo "<li><strong>{$course['course_code']}</strong>: {$course['title']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='error'>No recommendations found or an error occurred. Make sure Python is working and returning JSON.</p>";
        }
    } elseif (isset($_GET['student_id'])) {
        echo "<p class='error'>Please enter a valid student ID.</p>";
    }
    ?>

    <a href="../dashboard.php" class="back-link">← Back to AI Dashboard</a>
</div>
</body>
</html>
