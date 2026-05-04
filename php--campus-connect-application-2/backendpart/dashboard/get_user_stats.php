<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stats = [
    'downloads' => 0,
    'groups' => 0,
    'applications' => 0,
    'mentorships' => 0
];

// Get downloads count
$result = $conn->query("SELECT COUNT(*) as count FROM downloads WHERE user_id = $user_id");
if ($result) {
    $stats['downloads'] = $result->fetch_assoc()['count'] ?? 0;
}

// Get groups count
$result = $conn->query("SELECT COUNT(*) as count FROM group_members WHERE user_id = $user_id");
if ($result) {
    $stats['groups'] = $result->fetch_assoc()['count'] ?? 0;
}

// Get applications count
$result = $conn->query("SELECT COUNT(*) as count FROM internship_applications WHERE user_id = $user_id");
if ($result) {
    $stats['applications'] = $result->fetch_assoc()['count'] ?? 0;
}

// Get mentorships count
$result = $conn->query("SELECT COUNT(*) as count FROM mentorship_requests WHERE mentee_name = (SELECT fullname FROM users WHERE id = $user_id)");
if ($result) {
    $stats['mentorships'] = $result->fetch_assoc()['count'] ?? 0;
}

echo json_encode(['success' => true, 'data' => $stats]);
?>