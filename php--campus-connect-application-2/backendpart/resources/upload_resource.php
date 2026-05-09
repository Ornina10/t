<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include("../config/db.php");
require_once '../notifications/helper.php';

$title = $_POST['title'] ?? '';
$full_name = $_POST['full_name'] ?? 'Anonymous';
$year = $_POST['year'] ?? '';
$department = $_POST['department'] ?? '';
$material_type = $_POST['material_type'] ?? '';
$subject = $_POST['subject'] ?? ''; // ✅ NEW
$uploaded_by = $_SESSION['user_id'] ?? null;

if (empty($title) || empty($year) || empty($subject) || !isset($_FILES['file'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file = $_FILES['file'];
$file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
$file_path = $upload_dir . $file_name;

if (move_uploaded_file($file['tmp_name'], $file_path)) {
    $sql = "INSERT INTO materials (title, full_name, year, department, material_type, subject, file_name, file_path, uploaded_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $title, $full_name, $year, $department, $material_type, $subject, $file_name, $file_path, $uploaded_by);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Upload successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'File upload failed']);
}
$conn->close();
?>
