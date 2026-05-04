<?php
require_once '../config/db.php'; 

session_start();
header('Content-Type: application/json');

// Function to generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data (support both form-data and JSON)
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    $_POST = array_merge($_POST, $input);
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$email = trim(htmlspecialchars($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'true';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, fullname, email, password, student_id, department, year, role, is_active FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    if ($user['is_active'] != 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Account is deactivated. Contact administrator']);
        exit;
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_fullname'] = $user['fullname'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_student_id'] = $user['student_id'];
    $_SESSION['user_department'] = $user['department'];
    $_SESSION['user_year'] = $user['year'];
    $_SESSION['login_time'] = time();
    
    // Remember me functionality
    if ($remember_me) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + (86400 * 30));
        
        $updateStmt = $conn->prepare("UPDATE users SET remember_token = ?, remember_token_expiry = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $token, $expiry, $user['id']);
        $updateStmt->execute();
        
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
    }
    
    // Update last login
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $user['id']);
    $updateStmt->execute();
    
    // Generate new CSRF token for next request
    $new_csrf = generateCSRFToken();
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'csrf_token' => $new_csrf,
        'data' => [
            'id' => $user['id'],
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'student_id' => $user['student_id'],
            'role' => $user['role']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()]);
}
?>