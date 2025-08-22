<?php
// Sample input - in a real app, this will come from a form or DB
$studentData = [
    'gpa' => 3.7,
    'interests' => 'AI,ML,Data Science',
    'completed_courses' => 'CS101,CS102'
];

// Convert PHP array to JSON
$jsonData = json_encode($studentData);

// Initialize cURL
$ch = curl_init('http://127.0.0.1:5000/recommend');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

// Execute the request
$response = curl_exec($ch);
curl_close($ch);

// Decode the response
$result = json_decode($response, true);

// Output
if ($result && $result['status'] === 'success') {
    echo "<h3>Recommended Courses:</h3><ul>";
    foreach ($result['recommendations'] as $course) {
        echo "<li>$course</li>";
    }
    echo "</ul>";
} else {
    echo "Error: " . ($result['message'] ?? 'Unable to get recommendations.');
}
?>
