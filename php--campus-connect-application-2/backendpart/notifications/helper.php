<?php
// Helper function for creating notifications
function createNotification($user_id, $type, $title, $message, $link = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
    $stmt->bind_param("issss", $user_id, $type, $title, $message, $link);
    return $stmt->execute();
}
?>