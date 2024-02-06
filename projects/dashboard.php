<?php
session_start();
include('includes/config.php');

class DashboardManager {
    private $conn;
    private $user_id;
    private $is_admin;

    public function __construct($conn, $user_id, $is_admin) {
        $this->conn = $conn;
        $this->user_id = $user_id;
        $this->is_admin = $is_admin;
    }

    public function redirectNonAdmin() {
        if ($this->is_admin != 0) {
            header("Location: user_view.php");
            exit();
        }
    }

    public function fetchItems() {
        $sql = "SELECT * FROM items WHERE status = 1 ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Failed to get result: " . $this->conn->error);
        }
        return $result;
    }

    public function fetchUsers() {
        $sql = "SELECT * FROM users WHERE status = 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Failed to get result: " . $this->conn->error);
        }
        return $result;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

// Check if session variables are set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin'])) {
    // Redirect to login page if session variables are not set
    header("Location: login.php");
    exit();
}

// Create DashboardManager instance
$dashboardManager = new DashboardManager($conn, $_SESSION['user_id'], $_SESSION['is_admin']);

try {
    // Redirect if user is not admin
    $dashboardManager->redirectNonAdmin();

    // Fetch items and users
    $result_items = $dashboardManager->fetchItems();
    $result_users = $dashboardManager->fetchUsers();

} catch (Exception $e) {
    // Handle any exceptions
    echo "Error: " . $e->getMessage();
}

// Close database connection
$dashboardManager->closeConnection();
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
<input type="text" id="searchInput" placeholder="Search an item" style="width: 50%;">

<table id="itemsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Serial</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Image</th>
            <th>Actions</th>
            <?php if($is_admin) { echo '<th>Action</th>'; } ?>
        </tr>
    </thead>
    <tbody id="itemsTableBody">
        <?php while($row = $result_items->fetch_assoc()) { ?>
            <tr class="itemRow">
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['product_num']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td style="color: <?php echo ($row['quantity'] == 0) ? 'red' : 'inherit'; ?>"><?php echo $row['quantity']; ?></td>
                <td><img src="<?php echo $row['image']; ?>" alt="Image" style="width: 150px; height: 150px;"></td>
                <?php if($is_admin == 0) { ?>
                    <td>
                        <a href="edit_item.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="delete_item.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a> |
                        <?php
                    $status_text = ($row['status'] == 1) ? 'Deactivate' : 'Activate';
                    $status_link = 'activate_deactivate_item.php?id=' . $row['id'];
                ?>
                <a href="<?php echo $status_link; ?>" onclick="return confirm('Are you sure you want to deactivate this item?')"><?php echo $status_text; ?></a>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </tbody>
</table>

    <?php if($is_admin) { ?>
        <h2>Users</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result_users->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><a href="deactivate_user.php?id=<?php echo $row['id']; ?>">Deactivate</a></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

<script src="assets/js/search.js"></script>
</body>
</html>
