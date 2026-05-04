<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated', 'notifications' => []]);
    exit;
}

// Fix: go up one level to backendpart, then into config
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $limit);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id'],
        'type' => $row['type'],
        'title' => $row['title'],
        'message' => $row['message'],
        'link' => $row['link'],
        'is_read' => (bool)$row['is_read'],
        'created_at' => $row['created_at'],
        'time_ago' => timeAgo($row['created_at'])
    ];
}

echo json_encode(['success' => true, 'notifications' => $notifications, 'count' => count($notifications)]);

function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    
    if ($time_difference < 60) {
        return $time_difference . " seconds ago";
    } elseif ($time_difference < 3600) {
        return round($time_difference / 60) . " minutes ago";
    } elseif ($time_difference < 86400) {
        return round($time_difference / 3600) . " hours ago";
    } elseif ($time_difference < 604800) {
        return round($time_difference / 86400) . " days ago";
    } else {
        return date("M d, Y", strtotime($timestamp));
    }
}

$conn->close();
?>