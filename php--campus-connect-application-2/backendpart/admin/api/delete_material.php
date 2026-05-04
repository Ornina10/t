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
    echo json_encode(['success' => false, 'message' => 'Invalid material ID']);
    exit;
}

include("../../config/db.php");

// Get file path to delete the actual file
$result = $conn->query("SELECT file_path FROM materials WHERE id = $id");
if ($result && $row = $result->fetch_assoc()) {
    $file_path = $row['file_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

$stmt = $conn->prepare("DELETE FROM materials WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Material deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>