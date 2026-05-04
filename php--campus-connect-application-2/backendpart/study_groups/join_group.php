<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include("../config/db.php");

$input = json_decode(file_get_contents('php://input'), true);

$group_id = $input['group_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;
$user_name = trim($input['user_name'] ?? $_SESSION['user_fullname'] ?? '');

if (empty($group_id) || empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Group ID and user are required']);
    exit;
}

// Check if already a member
$check_sql = "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $group_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'You are already a member of this group']);
    exit;
}

// Add to group members - includes the 'role' column
$sql = "INSERT INTO group_members (group_id, user_id, role, joined_at) VALUES (?, ?, 'member', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $group_id, $user_id);

if ($stmt->execute()) {
    // Update member count in groups table
    $update_sql = "UPDATE `groups` SET member_count = (SELECT COUNT(*) FROM group_members WHERE group_id = ?) WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $group_id, $group_id);
    $update_stmt->execute();
    
    echo json_encode(['status' => 'success', 'message' => 'Successfully joined the group']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>