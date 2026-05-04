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
require_once '../notifications/helper.php';

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mentee_name = trim($input['mentee_name'] ?? '');
    $mentor_name = trim($input['mentor_name'] ?? '');
    $mentor_id = isset($input['mentor_id']) ? intval($input['mentor_id']) : null;
    $department = trim($input['department'] ?? '');
    $message = trim($input['message'] ?? '');

    if (!empty($mentee_name) && !empty($mentor_name) && !empty($department) && !empty($message)) {
        $sql = "INSERT INTO mentorship_requests (mentee_name, mentor_name, mentor_id, department, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiss", $mentee_name, $mentor_name, $mentor_id, $department, $message);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Mentorship request submitted successfully',
                'request_id' => $stmt->insert_id
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    }
}

$conn->close();
?>