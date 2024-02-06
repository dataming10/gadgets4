<?php
include('includes/config.php');

class UserStatusUpdater {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function updateUserStatus($id) {
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
            $id = intval($_GET['id']);  // Ensure id is an integer

            // Use prepared statement to prevent SQL injection
            $status_query = $this->conn->prepare("SELECT status FROM users WHERE id = ?");
            $status_query->bind_param("i", $id);
            $status_query->execute();
            $status_result = $status_query->get_result();

            if ($status_result->num_rows == 1) {
                $row = $status_result->fetch_assoc();
                $current_status = $row['status'];

                // Toggle the status (activate/deactivate)
                $new_status = ($current_status == 1) ? 0 : 1;

                // Update the status in the database using a prepared statement
                $update_status_query = $this->conn->prepare("UPDATE users SET status = ? WHERE id = ?");
                $update_status_query->bind_param("ii", $new_status, $id);

                if ($update_status_query->execute()) {
                    return "User status updated successfully.";
                } else {
                    return "Error updating user status: " . $this->conn->error;
                }

                $update_status_query->close();
            } else {
                return "User not found.";
            }

            $status_query->close();
        }
    }
}

$userStatusUpdater = new UserStatusUpdater($conn);

$updateResult = null;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $updateResult = $userStatusUpdater->updateUserStatus($_GET['id']);
}

$conn->close();

// Redirect to the user list page after updating user status
header("Location: users_list.php");
exit();
?>
