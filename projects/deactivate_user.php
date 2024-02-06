<?php
include('includes/config.php');

class ItemDeleter {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function deleteItem($id) {
        $deleteItemSql = "DELETE FROM items WHERE id = ?";
        $stmt = $this->conn->prepare($deleteItemSql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return "Item deleted successfully.";
        } else {
            return "Error deleting item: " . $stmt->error;
        }

        $stmt->close();
    }
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $itemDeleter = new ItemDeleter($conn);
    $deleteResult = $itemDeleter->deleteItem($id);
    
    if (strpos($deleteResult, 'Error') !== false) {
        // Error occurred
        $error = $deleteResult;
    } else {
        // Success
        $success = $deleteResult;
    }
} else {
    $error = "Invalid item ID.";
}

$conn->close();

header("Location: dashboard.php");
exit();
?>
