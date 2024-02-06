<?php
include('includes/config.php');

class ItemStatusUpdater {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function updateStatus($id) {
        $updateStatusSql = "UPDATE items SET status = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($updateStatusSql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $success = "Item status updated successfully.";
        } else {
            $error = "Error updating item status: " . $stmt->error;
        }

        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    $itemStatusUpdater = new ItemStatusUpdater($conn);
    $itemStatusUpdater->updateStatus($id);
}

$conn->close();
header("Location: dashboard.php");
exit();
?>