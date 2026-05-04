<?php

require_once '../config/db.php';
session_start();

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to post internship']);
    exit;
}

// Get POST data
$title = $_POST['title'] ?? '';
$company = $_POST['company'] ?? '';
$description = $_POST['description'] ?? '';
$location = $_POST['location'] ?? '';
$stipend = $_POST['stipend'] ?? '';
$duration = $_POST['duration'] ?? '';
$deadline = $_POST['deadline'] ?? '';
$requirements = $_POST['requirements'] ?? '';
$year_requirement = $_POST['year_requirement'] ?? '';
$work_type = $_POST['work_type'] ?? '';


if (empty($title) || empty($company) || empty($deadline)) {
    echo json_encode(['status' => 'error', 'message' => 'Title, company, and deadline are required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->prepare("INSERT INTO internships 
                          (title, company, description, location, stipend, duration, 
                           deadline, requirements, year_requirement, work_type, posted_by, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $result = $stmt->execute([
        $title, $company, $description, $location, $stipend, $duration,
        $deadline, $requirements, $year_requirement, $work_type, $_SESSION['user_id']
    ]);
    
    if ($result) {
        $internship_id = $db->lastInsertId();
        echo json_encode([
            'status' => 'success',
            'message' => 'Internship posted successfully',
            'internship_id' => $internship_id
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to post internship']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
