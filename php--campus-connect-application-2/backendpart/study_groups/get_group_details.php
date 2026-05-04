<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("../config/db.php");

$group_id = $_GET['id'] ?? 0;

if (empty($group_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Group ID required']);
    exit;
}

$sql = "SELECT g.group_id as id, g.group_name, g.subject, g.description, g.year, 
        g.days, g.start_time, g.end_time, g.max_members, g.organizer,
        COUNT(gm.user_name) as member_count
        FROM groups g
        LEFT JOIN group_members gm ON g.group_id = gm.group_id
        WHERE g.group_id = ?
        GROUP BY g.group_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $group = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'data' => $group]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Group not found']);
}

$conn->close();
?>