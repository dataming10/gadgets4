<?php
include('includes/config.php');

class ItemEditor {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function editItem($id, $name, $quantity, $removeImage, $imageFile) {
        $fetchItemSql = $this->conn->prepare("SELECT * FROM items WHERE id = ?");
        $fetchItemSql->bind_param("i", $id);
        $fetchItemSql->execute();

        $result = $fetchItemSql->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $fetchItemSql->close();

            if ($removeImage) {
                $this->removeImageAndUpdateItem($name, $quantity, $id);
            } else {
                $this->updateItemWithImage($name, $quantity, $id, $imageFile);
            }
        } else {
            $error = "Item not found.";
        }
    }

    private function removeImageAndUpdateItem($name, $quantity, $id) {
        $removeImageSql = $this->conn->prepare("UPDATE items SET name = ?, quantity = ?, image = NULL WHERE id = ?");
        $removeImageSql->bind_param("ssi", $name, $quantity, $id);
        $removeImageSql->execute();

        if ($removeImageSql->affected_rows > 0) {
            $this->redirectToDashboard("Item updated successfully.");
        } else {
            $error = "Error updating item: " . $this->conn->error;
        }

        $removeImageSql->close();
    }

    private function updateItemWithImage($name, $quantity, $id, $imageFile) {
        if ($imageFile && $imagePath = $this->uploadImage($imageFile)) {
            $updateItemSql = $this->conn->prepare("UPDATE items SET name = ?, quantity = ?, image = ? WHERE id = ?");
            $updateItemSql->bind_param("sssi", $name, $quantity, $imagePath, $id);
        } else {
            $updateItemSql = $this->conn->prepare("UPDATE items SET name = ?, quantity = ? WHERE id = ?");
            $updateItemSql->bind_param("ssi", $name, $quantity, $id);
        }

        $updateItemSql->execute();

        if ($updateItemSql->affected_rows > 0) {
            $this->redirectToDashboard("Item updated successfully.");
        } else {
            $error = "Error updating item: " . $this->conn->error;
        }

        $updateItemSql->close();
    }

    private function uploadImage($file) {
        if ($file['name'] === ''){
            return false;
        }

        $targetDir = "uploads/";
        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

        // Hash the file name to ensure uniqueness
        $hashedFilename = md5(uniqid()) . '.' . $imageFileType;
        $targetFile = $targetDir . $hashedFilename;

        // Check if the image file is a valid image
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return false;
        }

        // Check file size
        if ($file["size"] > 50000000) {
            return false;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif", "webp"])) {
            return false;
        }

        // If everything is ok, try to upload file
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        } else {
            return false;
        }
    }

    private function redirectToDashboard($message) {
        $success = $message;
        header("Location: dashboard.php");
        exit();
    }
}

$itemEditor = new ItemEditor($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['name']);
    $quantity = intval($_POST['quantity']);
    $removeImage = isset($_POST['remove_image']) ? intval($_POST['remove_image']) : 0;

    $itemEditor->editItem($id, $name, $quantity, $removeImage, $_FILES['image']);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $fetchItemSql = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $fetchItemSql->bind_param("i", $id);
    $fetchItemSql->execute();

    $result = $fetchItemSql->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $quantity = $row['quantity'];
    } else {
        $error = "Item not found.";
    }

    $fetchItemSql->close();
}

$conn->close();
?>

<!-- HTML CODE -->

<!DOCTYPE html>
<html>
<head>
    <title>Edit Inventory Item</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h2>Edit Inventory Item</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo $name; ?>" required><br>
        <label>Quantity:</label>
        <input type="number" name="quantity" value="<?php echo $quantity; ?>" required><br>
        <label>Change Image:</label>
        <input type="file" name="image"><br>
        <label>Remove Image: </label>
        <input type="checkbox" name="remove_image" value="1"><br>
        <button type="submit" name="submit">Update Item</button>
        <a href="dashboard.php" class="disregard" onclick="return confirm('Are you sure you want to discard this edit?')">Discard Edit</a>
    </form>
    <?php if(isset($error)) { echo $error; } ?>
    <?php if(isset($success)) { echo $success; } ?>
</body>
</html>
