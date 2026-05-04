<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? 0;
$user_id = $_SESSION['user_id'];

if ($id === 0) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $success = $stmt->execute();
} else {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $success = $stmt->execute();
}

echo json_encode(['success' => $success]);

$conn->close();
?>