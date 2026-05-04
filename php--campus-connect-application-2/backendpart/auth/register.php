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

// Generate CSRF token for response
$csrf_token = generateCSRFToken();

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

// Log the received data for debugging
error_log('Register POST data: ' . print_r($_POST, true));

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

// Get and sanitize input
$fullname = trim(htmlspecialchars($_POST['fullname'] ?? ''));
$email = trim(htmlspecialchars($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$student_id = trim(htmlspecialchars($_POST['student_id'] ?? ''));
$department = trim(htmlspecialchars($_POST['department'] ?? 'Software Engineering'));
$year = intval($_POST['year'] ?? 1);
$phone = trim(htmlspecialchars($_POST['phone'] ?? ''));

$errors = [];

// Validation
if (empty($fullname)) $errors[] = "Full name is required";
if (empty($email)) $errors[] = "Email is required";
if (empty($password)) $errors[] = "Password is required";
if (empty($student_id)) $errors[] = "Student ID is required";

// Email validation
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}
if (!empty($email) && !preg_match('/@aastustudent\.edu\.et$/', $email) && !preg_match('/@astu\.edu\.et$/', $email)) {
    $errors[] = "Please use your AASTU student email address (@aastustudent.edu.et)";
}

// Password validation
if (!empty($password)) {
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

if ($year < 1 || $year > 5) {
    $errors[] = "Invalid year of study (1-5)";
}

if (!empty($phone) && !preg_match('/^(09|07)[0-9]{8}$/', $phone)) {
    $errors[] = "Invalid phone number format (09xxxxxxxx or 07xxxxxxxx)";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
    exit;
}

try {
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    
    // Check if student ID exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Student ID already registered']);
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $sql = "INSERT INTO users (fullname, email, password, student_id, department, year, phone, role, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'student', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssis", $fullname, $email, $hashed_password, $student_id, $department, $year, $phone);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Auto-login after registration
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_fullname'] = $fullname;
        $_SESSION['user_role'] = 'student';
        $_SESSION['user_student_id'] = $student_id;
        $_SESSION['user_department'] = $department;
        $_SESSION['user_year'] = $year;
        
        // Generate new CSRF token
        $new_csrf = generateCSRFToken();
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'csrf_token' => $new_csrf,
            'data' => [
                'id' => $user_id,
                'fullname' => $fullname,
                'email' => $email,
                'student_id' => $student_id
            ]
        ]);
    } else {
        throw new Exception("Registration failed: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
}
?>