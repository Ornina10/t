<?php
// File: backendpart/mentors/update_mentorship_status.php
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
    $id = $input['id'] ?? '';
    $status = $input['status'] ?? '';

    if (!empty($id) && !empty($status)) {
        $sql = "UPDATE mentorship_requests SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Mentorship status updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID and status are required']);
    }
}

$conn->close();
?>