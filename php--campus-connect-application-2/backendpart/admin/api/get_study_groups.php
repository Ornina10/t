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

$sql = "SELECT g.*, COUNT(gm.id) as member_count 
        FROM `groups` g 
        LEFT JOIN group_members gm ON g.id = gm.group_id 
        GROUP BY g.id 
        ORDER BY g.id DESC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['success' => true, 'data' => $data]);
?>