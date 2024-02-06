<?php
session_start();
include('includes/config.php');

class DeactivatedItemsManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function fetchDeactivatedItems() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $is_admin = $_SESSION['is_admin'];

        // Session fixation protection
        session_regenerate_id(true);

        // Fetch deactivated items using prepared statements
        $sql_deactivated_items = "SELECT * FROM items WHERE status = 0";
        $stmt_deactivated_items = $this->conn->prepare($sql_deactivated_items);

        if ($stmt_deactivated_items) {
            $stmt_deactivated_items->execute();
            $result_deactivated_items = $stmt_deactivated_items->get_result();
        } else {
            // Handle the error appropriately (e.g., log it)
            die("Error in prepared statement: " . $this->conn->error);
        }

        $this->conn->close();

        return $result_deactivated_items;
    }
}

$deactivatedItemsManager = new DeactivatedItemsManager($conn);
$result_deactivated_items = $deactivatedItemsManager->fetchDeactivatedItems();
?>


<!-- HTML CODE -->

<!DOCTYPE html>
<html>
<head>
    <title>Deactivated Items</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include('includes/side_navbar.php'); ?>
    <h2>Deactivated Items</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Serial</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Image</th>
            <th>Actions</th>
            <?php if($is_admin) { echo '<th>Action</th>'; } ?>
        </tr>
        <?php while($row = $result_deactivated_items->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['product_num']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><img src="<?php echo $row['image']; ?>" alt="Image" style="width: 150px; height: 150px;"></td>
                <?php if($is_admin == 0) { ?>
                    <td>
                    <a href="delete_item.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a> |
                        <a href="activate_item.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure want to activate it?')">Activate</a>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
