<?php
require_once '../config/db.php'; 

session_start();
header('Content-Type: application/json');

// Check session first
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'fullname' => $_SESSION['user_fullname'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'student_id' => $_SESSION['user_student_id'] ?? null,
            'department' => $_SESSION['user_department'] ?? null,
            'year' => $_SESSION['user_year'] ?? null
        ]
    ]);
    exit;
}

// Check remember me token
if (isset($_COOKIE['remember_token'])) {
    try {
        $token = $_COOKIE['remember_token'];
        $now = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("SELECT id, fullname, email, student_id, role, department, year FROM users 
                                WHERE remember_token = ? AND remember_token_expiry > ? AND is_active = 1");
        $stmt->bind_param("ss", $token, $now);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Restore session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_fullname'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_student_id'] = $user['student_id'];
            $_SESSION['user_department'] = $user['department'];
            $_SESSION['user_year'] = $user['year'];
            
            echo json_encode([
                'authenticated' => true,
                'user' => $user
            ]);
            exit;
        }
    } catch (Exception $e) {
        // Token invalid, continue to unauthenticated
    }
}

// Not authenticated
echo json_encode([
    'authenticated' => false,
    'message' => 'Not authenticated'
]);
?>