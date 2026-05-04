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
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please login as admin.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
    exit;
}

include("../../config/db.php");

// First delete group members
$member_stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ?");
$member_stmt->bind_param("i", $id);
$member_stmt->execute();
$member_stmt->close();

// Then delete the group
$stmt = $conn->prepare("DELETE FROM `groups` WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Group deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>