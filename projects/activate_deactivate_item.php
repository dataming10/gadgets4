<?php
include('includes/config.php');

class ItemStatus {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function updateItemStatus($id) {
        $id = intval($id);

        // Fetch the current status of the item
        $status_query = $this->conn->prepare("SELECT status FROM items WHERE id = ?");
        $status_query->bind_param("i", $id);
        $status_query->execute();
        $status_result = $status_query->get_result();

        if ($status_result->num_rows == 1) {
            $row = $status_result->fetch_assoc();
            $current_status = $row['status'];

            // Toggle the status (activate/deactivate)
            $new_status = ($current_status == 1) ? 0 : 1;

            // Update the status in the database
            $update_status_query = $this->conn->prepare("UPDATE items SET status = ? WHERE id = ?");
            $update_status_query->bind_param("ii", $new_status, $id);

            if ($update_status_query->execute()) {
                $success = "Item status updated successfully.";
            } else {
                $error = "Error updating item status: " . $this->conn->error;
            }

            $update_status_query->close();
        } else {
            $error = "Item not found.";
        }

        $status_query->close();

        if (isset($success)) {
            return array('type' => 'success', 'message' => $success);
        } else if (isset($error)) {
            return array('type' => 'danger', 'message' => $error);
        }
    }
}

$itemStatus = new ItemStatus($conn);
$response = $itemStatus->updateItemStatus($_GET['id']);

$conn->close();
header("Location: deactivated_items.php");
exit;
?>