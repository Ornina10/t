<?php
$conn = new mysqli("localhost", "root", "", "campus_connect");

$id = $_GET['id'] ?? 0;

// Update download count
$conn->query("UPDATE materials SET downloads_count = downloads_count + 1 WHERE id = $id");

$result = $conn->query("SELECT file_path, file_name FROM materials WHERE id = $id");
$file = $result->fetch_assoc();

if ($file && file_exists($file['file_path'])) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
    readfile($file['file_path']);
    exit;
} else {
    echo "File not found";
}
$conn->close();
?>