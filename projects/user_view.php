<?php
session_start();
include('includes/config.php');

class InventoryManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getInventoryItems($isAdmin) {
        // Fetch only active items with quantity greater than 0
        $sql_items = "SELECT * FROM items WHERE status = 1 AND quantity > 0 ORDER BY id DESC";
        $result_items = $this->conn->query($sql_items);
        return $result_items;
    }
}

$inventoryManager = new InventoryManager($conn);
$result_items = $inventoryManager->getInventoryItems($_SESSION['is_admin']);

$conn->close();
?>

<!-- HTML CODE -->

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include('includes/side_navbar.php'); ?>
    <h2>Inventory Items</h2>

    <?php displayTable($result_items, $_SESSION['is_admin']); ?>

<?php
function displayTable($result, $isAdmin)
{
    ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Serial</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Image</th>
            <?php if ($isAdmin) ?>
        </tr>
        <?php if ($result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['product_num']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><img src="<?php echo $row['image']; ?>" alt="Image" style="width: 150px; height: 150px;"></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4" style="text-align: center;">No items available.</td>
            </tr>
        <?php } ?>
    </table>
    <?php
}
?>
</body>
</html>
