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
    // Return sample mentors if no mentors in database
    $sampleMentors = [
        [
            'id' => 1,
            'name' => 'Hana Bekele',
            'email' => 'hana@example.com',
            'skills' => 'React, Node.js, MongoDB',
            'department' => 'Software Engineering',
            'year' => '5th Year',
            'bio' => 'Full stack developer with experience in MERN stack'
        ],
        [
            'id' => 2,
            'name' => 'Yonas Tesfaye',
            'email' => 'yonas@example.com',
            'skills' => 'Flutter, Dart, Firebase',
            'department' => 'Software Engineering',
            'year' => '4th Year',
            'bio' => 'Mobile app development specialist'
        ],
        [
            'id' => 3,
            'name' => 'Lulit Alemu',
            'email' => 'lulit@example.com',
            'skills' => 'Java, Spring Boot, MySQL',
            'department' => 'Software Engineering',
            'year' => '5th Year',
            'bio' => 'Backend systems and database expert'
        ]
    ];
    echo json_encode(['status' => 'success', 'data' => $sampleMentors, 'count' => count($sampleMentors), 'message' => 'Sample data']);
}

$conn->close();
?>