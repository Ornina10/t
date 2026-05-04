<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include("../config/db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to create a study group']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit;
}

// Get values - matching your table columns exactly
$name = trim($input['group_name'] ?? $input['subject'] ?? '');
$course_name = trim($input['subject'] ?? '');
$department = 'Software Engineering';
$year = trim($input['year'] ?? '');
$days = is_array($input['days'] ?? []) ? json_encode($input['days']) : ($input['days'] ?? '[]');
$start_time = $input['start_time'] ?? null;
$end_time = $input['end_time'] ?? null;
$description = trim($input['description'] ?? 'Study group for ' . $course_name);
$created_by = $_SESSION['user_id'];
$organizer = trim($input['organizer'] ?? $_SESSION['user_fullname'] ?? '');
$max_members = 20;
$member_count = 1;
$status = 'active';

// Validation
if (empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Group name is required']);
    exit;
}
if (empty($course_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Course name is required']);
    exit;
}
if (empty($year)) {
    echo json_encode(['status' => 'error', 'message' => 'Year is required']);
    exit;
}

// Fix time format (add :00 seconds if needed)
if ($start_time && strlen($start_time) == 5) {
    $start_time = $start_time . ':00';
}
if ($end_time && strlen($end_time) == 5) {
    $end_time = $end_time . ':00';
}

// Insert into groups table
$sql = "INSERT INTO `groups` (name, course_name, department, year, days, start_time, end_time, description, created_by, organizer, max_members, member_count, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssisiii", 
    $name, $course_name, $department, $year, $days, 
    $start_time, $end_time, $description, $created_by, 
    $organizer, $max_members, $member_count, $status
);

if ($stmt->execute()) {
    $group_id = $conn->insert_id;
    
    // Add creator as first member - including the 'role' column
    $member_sql = "INSERT INTO group_members (group_id, user_id, role, joined_at) VALUES (?, ?, 'admin', NOW())";
    $member_stmt = $conn->prepare($member_sql);
    $member_stmt->bind_param("ii", $group_id, $created_by);
    $member_stmt->execute();
    $member_stmt->close();
    
    echo json_encode(['status' => 'success', 'message' => 'Group created successfully', 'group_id' => $group_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>