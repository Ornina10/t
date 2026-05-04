<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../config/db.php");

// Check connection
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get mentors from mentors table
$sql = "SELECT id, name, email, skills, department, year, bio 
        FROM mentors 
        WHERE is_active = 1 
        ORDER BY name ASC";
$result = $conn->query($sql);

$mentors = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mentors[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'skills' => $row['skills'] ?? 'Software Development',
            'department' => $row['department'] ?? 'Software Engineering',
            'year' => $row['year'] ? $row['year'] . 'th Year' : '4th Year',
            'bio' => $row['bio'] ?? 'Experienced mentor ready to guide you'
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $mentors, 'count' => count($mentors)]);
} else {
    echo json_encode(['status' => 'success', 'data' => [], 'count' => 0, 'message' => 'No mentors found in database. Please add mentors.']);
}

$conn->close();
?>