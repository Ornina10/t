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

include("../config/db.php");

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $skills = trim($input['skills'] ?? '');
    $department = trim($input['department'] ?? '');
    $year = trim($input['year'] ?? '');
    
    if (!empty($name) && !empty($email) && !empty($skills)) {
        // Check if already a mentor
        $check_sql = "SELECT id FROM mentors WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'You are already registered as a mentor']);
        } else {
            // Insert into mentors table
            $sql = "INSERT INTO mentors (name, email, skills, department, year, bio, is_active) 
                    VALUES (?, ?, ?, ?, ?, 'Pending approval - Mentor registration requested', 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $email, $skills, $department, $year);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Thank you! You have been registered as a mentor.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $stmt->error]);
            }
            $stmt->close();
        }
        $check_stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Name, email, and skills are required']);
    }
}

$conn->close();
?>