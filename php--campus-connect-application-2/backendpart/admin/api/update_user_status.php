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
$stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
$stmt->bind_param("ii", $input['is_active'], $input['id']);
echo json_encode(['success' => $stmt->execute()]);
?>