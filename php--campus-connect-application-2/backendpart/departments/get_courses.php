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

$field = $_GET['field'] ?? '';
$year = $_GET['year'] ?? '';
$semester = $_GET['semester'] ?? '';

$sql = "SELECT * FROM courses WHERE 1=1";
$params = [];
$types = "";

if (!empty($field)) {
    $sql .= " AND field = ?";
    $params[] = $field;
    $types .= "s";
}
if (!empty($year)) {
    $sql .= " AND year = ?";
    $params[] = (int)$year;
    $types .= "i";
}
if (!empty($semester)) {
    $sql .= " AND semester = ?";
    $params[] = (int)$semester;
    $types .= "i";
}

$sql .= " ORDER BY year ASC, semester ASC, course_code ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $courses, 'count' => count($courses)]);
} else {
    // Return sample data if no courses in database
    $sampleCourses = [
        ['course_code' => 'CS101', 'course_name' => 'Introduction to Programming', 'credit_hours' => 4, 'prerequisites' => 'None', 'year' => 2, 'semester' => 1, 'field' => 'Software Engineering'],
        ['course_code' => 'SE101', 'course_name' => 'Software Engineering Fundamentals', 'credit_hours' => 3, 'prerequisites' => 'CS101', 'year' => 2, 'semester' => 1, 'field' => 'Software Engineering'],
        ['course_code' => 'DB101', 'course_name' => 'Database Systems', 'credit_hours' => 3, 'prerequisites' => 'CS101', 'year' => 2, 'semester' => 2, 'field' => 'Software Engineering'],
        ['course_code' => 'WEB101', 'course_name' => 'Web Development', 'credit_hours' => 3, 'prerequisites' => 'CS101', 'year' => 2, 'semester' => 2, 'field' => 'Software Engineering']
    ];
    echo json_encode(['success' => true, 'data' => $sampleCourses, 'count' => count($sampleCourses), 'message' => 'Using sample data']);
}

$conn->close();
?>