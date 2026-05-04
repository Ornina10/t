<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if connection exists
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    // Query to get all internships
    $sql = "SELECT * FROM internships ORDER BY deadline ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $formatted_internships = [];
    
    while ($row = $result->fetch_assoc()) {
        $stipend_type = 'unpaid';
        if (!empty($row['stipend']) && $row['stipend'] > 0 && $row['stipend'] != '0') {
            $stipend_type = 'paid';
        }
        
        $work_type = $row['work_type'] ?? 'on-site';
        
        $formatted_internships[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'company' => $row['company'],
            'description' => $row['description'] ?? '',
            'location' => $row['location'] ?? 'Not specified',
            'stipend' => $row['stipend'] ?? 0,
            'stipend_type' => $stipend_type,
            'duration' => $row['duration'] ?? 'Not specified',
            'deadline' => $row['deadline'],
            'requirements' => $row['requirements'] ?? '',
            'year_requirement' => $row['year_requirement'] ?? 'All',
            'work_type' => $work_type,
            'created_at' => $row['created_at'] ?? ''
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $formatted_internships,
        'count' => count($formatted_internships)
    ]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to retrieve internships: ' . $e->getMessage()
    ]);
}

$conn->close();
?>