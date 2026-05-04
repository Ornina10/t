<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include("../config/db.php");

$sql = "SELECT g.*, 
        COUNT(gm.id) as member_count,
        u.fullname as organizer_name
        FROM `groups` g
        LEFT JOIN group_members gm ON g.id = gm.group_id
        LEFT JOIN users u ON g.created_by = u.id
        GROUP BY g.id
        ORDER BY g.created_at DESC";

$result = $conn->query($sql);

$groups = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $groups[] = [
            'id' => $row['id'],
            'group_name' => $row['name'],
            'subject' => $row['course_name'],
            'description' => $row['description'] ?? '',
            'year' => $row['year'],
            'days' => $row['days'] ?? '[]',
            'start_time' => $row['start_time'] ?? 'TBD',
            'end_time' => $row['end_time'] ?? 'TBD',
            'member_count' => $row['member_count'] ?? 1,
            'max_members' => $row['max_members'] ?? 20,
            'organizer' => $row['organizer_name'] ?? $row['organizer'] ?? 'Admin'
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $groups, 'count' => count($groups)]);
} else {
    echo json_encode(['status' => 'success', 'data' => [], 'count' => 0]);
}

$conn->close();
?>