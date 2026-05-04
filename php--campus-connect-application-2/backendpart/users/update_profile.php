<?php
require_once '../../config/db.php'; 

session_start();
header('Content-Type: application/json');

// CSRF verification function
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

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

// Verify CSRF token
if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$user_id = $_SESSION['user_id'];

// ADDED: Get fullname from input
$fullname = trim(htmlspecialchars($input['fullname'] ?? ''));
$department = trim(htmlspecialchars($input['department'] ?? ''));
$year = intval($input['year'] ?? 0);
$phone = trim(htmlspecialchars($input['phone'] ?? ''));
$bio = trim(htmlspecialchars($input['bio'] ?? ''));

// Validate year
if ($year < 1 || $year > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid year of study (1-5)']);
    exit;
}

try {
    global $conn;
    
    // UPDATED: Added fullname to the SQL query
    $sql = "UPDATE users SET fullname = ?, department = ?, year = ?, phone = ?, bio = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    // UPDATED: Added 's' for fullname string parameter
    $stmt->bind_param("ssissi", $fullname, $department, $year, $phone, $bio, $user_id);
    
    if ($stmt->execute()) {
        // Update session with new values
        $_SESSION['user_fullname'] = $fullname;
        $_SESSION['user_department'] = $department;
        $_SESSION['user_year'] = $year;
        $_SESSION['user_phone'] = $phone;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        throw new Exception("Update failed");
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
}
?>