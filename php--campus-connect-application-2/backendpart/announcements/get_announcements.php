<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../config/db.php");

$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($sql);

$announcements = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'created_by' => $row['created_by'],
            'created_at' => $row['created_at']
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $announcements, 'count' => count($announcements)]);
} else {
    echo json_encode(['status' => 'success', 'data' => [], 'message' => 'No announcements found']);
}

$conn->close();
?>