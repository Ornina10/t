<?php
include 'config/db.php';

if(isset($_POST['group_id'], $_POST['user_name'])) {
    $group_id = (int)$_POST['group_id'];
    $user_name = $conn->real_escape_string($_POST['user_name']);

    // Check if name is in the group
    $check = $conn->query("SELECT * FROM group_members WHERE group_id=$group_id AND user_name='$user_name'");
    if($check->num_rows > 0){
        $conn->query("DELETE FROM group_members WHERE group_id=$group_id AND user_name='$user_name'");
        echo "$user_name has left the group.";
    } else {
        echo "Name '$user_name' is not in this group!";
    }
}

// Fetch groups with member count
$groups = $conn->query("SELECT g.group_id, g.group_name, COUNT(gm.user_name) AS members, g.max_members
                        FROM groups g
                        LEFT JOIN group_members gm ON g.group_id = gm.group_id
                        GROUP BY g.group_id");
?>

<h2>Leave Group</h2>
<form method="POST">
    <input type="text" name="user_name" placeholder="Your Name" required>
    <select name="group_id">
        <?php while($row = $groups->fetch_assoc()): ?>
            <option value="<?= $row['group_id'] ?>">
                <?= htmlspecialchars($row['group_name']) ?> 
                (<?= $row['members'] ?>/<?= $row['max_members'] ?> members)
            </option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Leave Group</button>
</form>
