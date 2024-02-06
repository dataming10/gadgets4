<?php
session_start();
include('includes/config.php');

class ItemManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addItem($name, $quantity, $image) {
        $name = mysqli_real_escape_string($this->conn, $name);
    
        // Generate a serial number for product_num with a maximum length of 10 characters
        $identifier = "PR-";
        $product_num = $identifier . strtoupper(substr(preg_replace('/-/', '', uniqid('', true)), 0, 10));
    
        // Check if the product with the same name or image already exists
        if ($this->isProductExists($product_num)) {
            $error = "Product with the same name or image already exists.";
            return;
        }
    
        $uploadResult = $this->uploadImage($image);
    
        if ($uploadResult['success']) {
            $target_file = $uploadResult['target_file'];
    
            $insertItemSql = $this->conn->prepare("INSERT INTO items (product_num, name, quantity, image) VALUES (?, ?, ?, ?)");
            // Modify the bind_param to use 'ssi' for the string, string, and integer types
            $insertItemSql->bind_param("ssis", $product_num, $name, $quantity, $target_file); // Change this line

            if ($insertItemSql->execute()) {
                $success = "Item added successfully.";
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Error: " . $this->conn->error;
            }
    
            $insertItemSql->close();
        } else {
            $error = $uploadResult['error'];
        }
    }
    
    private function isProductExists($product_num) {
        $checkProductSql = $this->conn->prepare("SELECT id FROM items WHERE product_num = ?");
        $checkProductSql->bind_param("s", $product_num);
        $checkProductSql->execute();
        $result = $checkProductSql->get_result();

        return $result->num_rows > 0;
    }

    private function uploadImage($image) {
        $targetDir = "uploads/";
        $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));

        // Hash the file name to ensure uniqueness
        $hashedFilename = md5(uniqid()) . '.' . $imageFileType;
        $targetFile = $targetDir . $hashedFilename;
        $uploadOk = 1;

        // Check if image file is a valid image
        $check = getimagesize($image["tmp_name"]);
        if ($check === false) {
            return ['success' => false, 'error' => "File is not a valid image."];
        }

        // Check file size
        if ($image["size"] > 5000000) {
            return ['success' => false, 'error' => "Sorry, your file is too large."];
        }

        // Allow only certain file formats
        $allowedFormats = array("jpg", "jpeg", "png", "gif", "webp");
        if (!in_array($imageFileType, $allowedFormats)) {
            return ['success' => false, 'error' => "Sorry, only JPG, JPEG, PNG, and GIF files are allowed."];
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            return ['success' => false, 'error' => "Sorry, your file was not uploaded."];
        } else {
            // If everything is ok, try to upload file
            if (move_uploaded_file($image["tmp_name"], $targetFile)) {
                return ['success' => true, 'target_file' => $targetFile];
            } else {
                return ['success' => false, 'error' => "Sorry, there was an error uploading your file."];
            }
        }
    }
}

$itemManager = new ItemManager($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $quantity = intval($_POST['quantity']);
    $image = $_FILES["image"];

    $itemManager->addItem($name, $quantity, $image);
}

$conn->close();
?>

<!-- HTML CODE -->

<!DOCTYPE html>
<html>
<head>
    <title>Add Inventory Item</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include('includes/side_navbar.php'); ?>
    <h2>Add Inventory Item</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="name" required><br>
        <label>Quantity:</label>
        <input type="number" name="quantity" required><br>
        <label>Image:</label>
        <input type="file" name="image" accept="image/*" required><br>
        <button type="submit">Add Item</button>
    </form>
    <?php if(isset($error)) { echo '<div class="error">' . $error . '</div>'; } ?>
<?php if(isset($success)) { echo '<div class="message">' . $success . '</div>'; } ?>
</body>
</html>
