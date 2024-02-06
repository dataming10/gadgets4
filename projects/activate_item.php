<?php
include('includes/config.php');

class ItemActivator {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function activateItem($id) {
        $id = intval($id); // Ensure id is an integer

        // Use prepared statement to prevent SQL injection
        $activateItemSql = $this->conn->prepare("UPDATE items SET status = 1 WHERE id = ?");
        $activateItemSql->bind_param("i", $id);

        if ($activateItemSql->execute()) {
            $success = "Item activated successfully.";
        } else {
            $error = "Error activating item: " . $this->conn->error;
        }

        $activateItemSql->close();
    }
}

$itemActivator = new ItemActivator($conn);

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];
    $itemActivator->activateItem($id);
}

$conn->close();
header("Location: dashboard.php");
exit();
?>
