<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Email required']);
    exit;
}

$sql = "SELECT * FROM mentorship_requests WHERE mentee_name IN (SELECT fullname FROM users WHERE email = ?) ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $requests, 'count' => count($requests)]);

$conn->close();
?>