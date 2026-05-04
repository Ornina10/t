<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id'];
$department = $input['department'] ?? '';
$year = intval($input['year'] ?? 1);
$phone = $input['phone'] ?? '';
$bio = $input['bio'] ?? '';

$sql = "UPDATE users SET department = ?, year = ?, phone = ?, bio = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sissi", $department, $year, $phone, $bio, $user_id);

if ($stmt->execute()) {
    // Update session
    $_SESSION['user_department'] = $department;
    $_SESSION['user_year'] = $year;
    
    echo json_encode(['success' => true, 'message' => 'Profile updated']);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>