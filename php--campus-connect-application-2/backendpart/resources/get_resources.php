<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include("../config/db.php");

$sql = "SELECT * FROM materials ORDER BY id DESC";
$result = $conn->query($sql);

$materials = [];
while ($row = $result->fetch_assoc()) {
    $materials[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $materials]);
$conn->close();
?>