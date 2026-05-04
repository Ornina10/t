<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized. Please login as admin.']);
    exit;
}
include("../../config/db.php");
$input = json_decode(file_get_contents('php://input'), true);
$stmt = $conn->prepare("INSERT INTO announcements (title, message, created_by) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $input['title'], $input['message'], $input['created_by']);
echo json_encode(['success' => $stmt->execute()]);
?>