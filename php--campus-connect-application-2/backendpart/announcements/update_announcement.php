<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../config/db.php");

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "PUT") {
    $id = $input['id'] ?? '';
    $title = trim($input['title'] ?? '');
    $message = trim($input['message'] ?? '');

    if (!empty($id) && !empty($title) && !empty($message)) {
        $sql = "UPDATE announcements SET title = ?, message = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $message, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Announcement updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID, title, and message are required']);
    }
}

$conn->close();
?>