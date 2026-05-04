<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../config/db.php");

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($input['title'] ?? '');
    $message = trim($input['message'] ?? '');
    $created_by = trim($input['created_by'] ?? 'Admin');

    if (!empty($title) && !empty($message)) {
        $sql = "INSERT INTO announcements (title, message, created_by) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $title, $message, $created_by);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Announcement created successfully',
                'id' => $stmt->insert_id
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Title and message are required']);
    }
}

$conn->close();
?>