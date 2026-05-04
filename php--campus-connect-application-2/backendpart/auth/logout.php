<?php
require_once '../config/db.php';

session_start();
header('Content-Type: application/json');

// Clear remember me token from database
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("UPDATE users SET remember_token = NULL, remember_token_expiry = NULL WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
    } catch (Exception $e) {
        // Ignore database errors during logout
    }
}

// Clear session
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
?>