<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized. Please login as admin.']);
    exit;
}

include("../../config/db.php");

$result = $conn->query("SELECT id, fullname, email, student_id, role, is_active FROM users ORDER BY id DESC");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['success' => true, 'data' => $users]);
?>