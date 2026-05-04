<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (strlen($query) < 2) {
    echo json_encode(['status' => 'error', 'message' => 'Search term too short', 'query' => $query]);
    exit;
}

$searchTerm = '%' . $conn->real_escape_string($query) . '%';

// Search Internships
$sql = "SELECT id, title, company, description FROM internships WHERE title LIKE ? OR description LIKE ? OR company LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['type'] = 'internship';
    $results[] = $row;
}

// Search Announcements - COMMENT OUT OR REMOVE THIS SECTION
/*
$sql = "SELECT id, title, message FROM announcements WHERE title LIKE ? OR message LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['type'] = 'announcement';
    $results[] = $row;
}
*/
// Search Study Groups - Using correct column names from your table
// Your table has: id, name, description, course_name, department, year
$sql = "SELECT id, name, course_name as subject, description FROM `groups` WHERE name LIKE ? OR course_name LIKE ? OR description LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['type'] = 'study_group';
    $row['group_name'] = $row['name'];
    $results[] = $row;
}

// Search Materials
$sql = "SELECT id, title, full_name, material_type FROM materials WHERE title LIKE ? OR full_name LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['type'] = 'material';
    $results[] = $row;
}

// Search Courses
$sql = "SELECT id, course_code, course_name, field, year, semester, credit_hours FROM courses WHERE course_code LIKE ? OR course_name LIKE ? OR field LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['type'] = 'course';
    $results[] = $row;
}

// Search Announcements
$sql = "SELECT id, title, message FROM announcements WHERE title LIKE ? OR message LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['type'] = 'announcement';
    $results[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $results,
    'count' => count($results),
    'query' => $query
]);

$conn->close();
?>