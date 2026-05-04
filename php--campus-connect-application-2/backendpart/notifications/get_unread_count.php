<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread_count' => 0]);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['unread_count' => (int)$row['count']]);

$conn->close();
?>