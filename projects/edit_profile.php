<?php
session_start();
include('includes/config.php');

class PasswordUpdater {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function updateUsername($username) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $update_user_sql = "UPDATE users SET username = ? WHERE id = ?";
                $stmt = $this->conn->prepare($update_user_sql);
                $stmt->bind_param("si", $username, $user_id);

                if ($stmt->execute()) {
                    return "Username updated successfully.";
                } else {
                    return "Error updating username: " . $stmt->error;
                }
            } else {
                return "User not logged in. Please log in first.";
            }
        }
    }

    public function updatePassword($password, $currentPassword) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $fetch_password_sql = "SELECT password FROM users WHERE id = ?";
                $stmt = $this->conn->prepare($fetch_password_sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $fetch_password_result = $stmt->get_result();

                if ($fetch_password_result !== false && $fetch_password_result->num_rows === 1) {
                    $row = $fetch_password_result->fetch_assoc();
                    $hashedPasswordFromDB = $row['password'];

                    if (password_verify($currentPassword, $hashedPasswordFromDB)) {
                        $hashedNewPassword = password_hash($password, PASSWORD_DEFAULT);
                        $update_user_sql = "UPDATE users SET password = ? WHERE id = ?";
                        $stmt = $this->conn->prepare($update_user_sql);
                        $stmt->bind_param("si", $hashedNewPassword, $user_id);

                        if ($stmt->execute()) {
                            return "Password updated successfully.";
                        } else {
                            return "Error updating password: " . $stmt->error;
                        }
                    } else {
                        return "Incorrect current password. Please try again.";
                    }
                } else {
                    return "Error fetching current password: " . $this->conn->error;
                }
            } else {
                return "User not logged in. Please log in first.";
            }

            session_regenerate_id(true);
        }
    }
}

$passwordUpdater = new PasswordUpdater($conn);

$updateResult = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['username'])) {
        $updateResult = $passwordUpdater->updateUsername($_POST['username']);
    }
    if (!empty($_POST['password']) && !empty($_POST['current_password'])) {
        $updateResult = $passwordUpdater->updatePassword($_POST['password'], $_POST['current_password']);
    }
}

$conn->close();
?>

<!-- HTML CODE -->

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include('includes/side_navbar.php'); ?>
    <h2>Edit Profile</h2>
    <form method="post" action="">
        <label>New Username:</label>
        <input type="text" name="username">
        <label>New Password:</label>
        <input type="password" name="password">
        <label>Current Password:</label>
        <input type="password" name="current_password" required><br>
        <button type="submit">Update Profile</button>
    </form>
    <?php if(isset($updateResult)) { ?>
        <div style="color: <?= strpos($updateResult, 'Error') !== false ? 'red' : 'green'; ?>">
            <?= $updateResult; ?>
        </div>
    <?php } ?>
</body>
</html>
