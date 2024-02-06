<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
session_start();
include('includes/config.php');

class UserManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUsers() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $is_admin = $_SESSION['is_admin'];

        // Session fixation protection
        session_regenerate_id(true);

        // Check if the current user is an admin
        if ($is_admin == 1) {
            header("Location: access_denied.php");
            exit();
        }

        // Fetch only non-admin users using prepared statements
        $sql_users = "SELECT * FROM users WHERE is_admin = 1";
        $result_users = $this->conn->query($sql_users);

        return $result_users;
    }

    public function updateAdminStatus($user_id_to_update, $is_admin, $status) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        $user_id = $_SESSION['user_id'];

        // Ensure that the current user is not trying to change their own admin status or status
        if ($user_id_to_update == $user_id) {
            return "You cannot change your own admin status or account status.";
        }

        // Update the user's admin status and account status using prepared statements
        $update_admin_sql = "UPDATE users SET is_admin = ?, status = ? WHERE id = ?";
        $update_admin_stmt = $this->conn->prepare($update_admin_sql);
        $update_admin_stmt->bind_param("iii", $is_admin, $status, $user_id_to_update);

        if ($update_admin_stmt->execute()) {
            return true;
        } else {
            return "Error updating user admin status or account status.";
        }
    }

    public function deleteUser($user_id_to_delete) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        $user_id = $_SESSION['user_id'];

        // Ensure that the current user is not trying to delete themselves
        if ($user_id_to_delete == $user_id) {
            return "You cannot delete your own account.";
        }

        // Delete the user using prepared statements
        $delete_user_sql = "DELETE FROM users WHERE id = ?";
        $delete_user_stmt = $this->conn->prepare($delete_user_sql);
        $delete_user_stmt->bind_param("i", $user_id_to_delete);

        if ($delete_user_stmt->execute()) {
            return true;
        } else {
            return "Error deleting user.";
        }
    }
}

$userManager = new UserManager($conn);
$result_users = $userManager->getUsers();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $user_id_to_update = $_POST['user_id'];
    $is_admin = $_POST['is_admin'];
    $status = $_POST['status'];

    $updateResult = $userManager->updateAdminStatus($user_id_to_update, $is_admin, $status);
    if ($updateResult === true) {
        header("Location: users_list.php");
        exit();
    } else {
        $error = $updateResult;
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $user_id_to_delete = $_POST['user_id'];

    $deleteResult = $userManager->deleteUser($user_id_to_delete);
    if ($deleteResult === true) {
        header("Location: users_list.php");
        exit();
    } else {
        $error = $deleteResult;
    }
}

$conn->close();
?>

<!-- HTML CODE -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List</title>
    <link rel="stylesheet" href="assets/user_list.css">
</head>
<body>
<?php include('includes/side_navbar.php'); ?>
    <h2>Users List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Status</th>
            <th>Admin status</th>
            <th>Actions</th>
            <?php if ($is_admin) { echo '<th>Action</th>'; } ?>
        </tr>
        <?php while($row = $result_users->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo ($row['status'] == 1) ? 'Active' : 'Inactive'; ?></td>
                <td><?php echo ($row['is_admin'] == 0) ? 'Yes' : 'No'; ?></td>
                <?php if ($is_admin == 0) { ?>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            
                            <!-- Form for updating admin and user status -->
                            <label>Change Status:</label>
                            <select name="is_admin">
                                <option value="0" <?php echo ($row['is_admin'] == 0) ? 'selected' : ''; ?> onclick="return confirm('Are you sure you want to make this an admin?')">Admin</option>
                                <option value="1" <?php echo ($row['is_admin'] == 1) ? 'selected' : ''; ?>>User</option>
                            </select>
                            <select name="status">
                                <option value="1" <?php echo ($row['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($row['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <button type="submit" name="submit">Update Status</button>
                            <!-- Add a "Delete" button -->
        <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete User</button>
                        </form>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>
    <?php if(isset($error)) { echo $error; } ?>
    <?php if(isset($success)) { echo $success; } ?>
</body>
</html>
