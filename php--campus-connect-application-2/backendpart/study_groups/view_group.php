<?php
include 'config/db.php'; // Make sure this connects to your database

// Fetch all groups with member count
$result = $conn->query("
    SELECT g.group_id, g.group_name, g.description, COUNT(gm.user_name) AS members
    FROM groups g
    LEFT JOIN group_members gm ON g.group_id = gm.group_id
    GROUP BY g.group_id, g.group_name, g.description
    ORDER BY g.group_name ASC
");
?>

<h2>Available Groups</h2>

<?php if($result->num_rows > 0): ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Group Name</th>
            <th>Description</th>
            <th>Members</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['group_name']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= $row['members'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No groups available yet.</p>
<?php endif; ?>

