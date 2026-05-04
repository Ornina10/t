<?php
require_once '../../config/db.php';

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];

// Check permission
if ($user_id != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You can only view your own profile']);
    exit;
}

try {
    global $conn;
    
    $sql = "SELECT id, fullname, email, student_id, department, year, phone, bio, role, created_at, last_login 
            FROM users WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>