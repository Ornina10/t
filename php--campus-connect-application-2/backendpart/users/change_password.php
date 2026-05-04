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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$user_id = $_SESSION['user_id'];
$current_password = $input['current_password'] ?? '';
$new_password = $input['new_password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

$errors = [];

if (empty($current_password)) $errors[] = "Current password is required";
if (empty($new_password)) $errors[] = "New password is required";
if ($new_password !== $confirm_password) $errors[] = "New passwords do not match";

// Password strength validation
if (!empty($new_password)) {
    if (strlen($new_password) < 8) $errors[] = "Password must be at least 8 characters";
    if (!preg_match('/[A-Z]/', $new_password)) $errors[] = "Password must contain at least one uppercase letter";
    if (!preg_match('/[a-z]/', $new_password)) $errors[] = "Password must contain at least one lowercase letter";
    if (!preg_match('/[0-9]/', $new_password)) $errors[] = "Password must contain at least one number";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
    exit;
}

try {
    global $conn;
    
    // Get current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($current_password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Hash new password
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->bind_param("si", $new_hash, $user_id);
    
    if ($updateStmt->execute()) {
        // Clear remember token for security
        $clearStmt = $conn->prepare("UPDATE users SET remember_token = NULL, remember_token_expiry = NULL WHERE id = ?");
        $clearStmt->bind_param("i", $user_id);
        $clearStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Password changed successfully. Please login again.'
        ]);
    } else {
        throw new Exception("Password change failed");
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Password change failed: ' . $e->getMessage()]);
}
?>