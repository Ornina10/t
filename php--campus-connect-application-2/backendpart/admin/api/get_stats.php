<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized. Please login as admin.']);
    exit;
}

include("../../config/db.php");

$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total internships
$result = $conn->query("SELECT COUNT(*) as count FROM internships");
$stats['total_internships'] = $result->fetch_assoc()['count'];

// Total mentors
$result = $conn->query("SELECT COUNT(*) as count FROM mentors");
$stats['total_mentors'] = $result->fetch_assoc()['count'];

// Total study groups
$result = $conn->query("SELECT COUNT(*) as count FROM `groups`");
$stats['total_study_groups'] = $result->fetch_assoc()['count'];

// Total materials
$result = $conn->query("SELECT COUNT(*) as count FROM materials");
$stats['total_materials'] = $result->fetch_assoc()['count'];

echo json_encode($stats);
?>