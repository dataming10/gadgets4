<?php
include('includes/config.php');

class SessionManager {
    private $user_id;
    private $is_admin;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
        $this->user_id = $_SESSION['user_id'];
        $this->is_admin = $_SESSION['is_admin'];
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function isAdmin() {
        return $this->is_admin;
    }
}

// Create SessionManager instance
$sessionManager = new SessionManager();
$user_id = $sessionManager->getUserId();
$is_admin = $sessionManager->isAdmin();
?>


<!-- HTML CODE -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      font-family: 'Arial', sans-serif;
    }

    .navbar {
      height: 100%;
      width: 0;
      position: fixed;
      z-index: 1;
      overflow-x: hidden;
      transition: 0.5s;
      background-color: #333;
      padding-top: 60px;
      left: 0; /* Set to the left side */
    }

    .navbar a {
      padding: 8px 8px 8px 32px;
      text-decoration: none;
      font-size: 18px;
      color: #818181;
      display: block;
      margin-top: 15px;
      transition: 0.3s;
    }

    .navbar a:hover {
      color: #f1f1f1;
    }

    .navbar .close-btn {
      position: absolute;
      top: 32px;
      right: 25px;
      font-size: 30px;
      margin-left: 50px;
    }

    #main {
      position: fixed;
      top: 0;
      right: 0;
      padding: 16px;
    }

    /* Style for the Logout button */
    .logout-btn {
      cursor: pointer;
      color: #818181;
      text-decoration: none;
      font-size: 18px;
      display: block;
      margin-top: 15px;
      padding: 8px 8px 8px 32px;
      transition: 0.3s;
    }

    .logout-btn:hover {
      color: #f1f1f1;
    }
  </style>
</head>
<body>
<?php if($is_admin != 1) { ?>
<div class="navbar" id="myNavbar">
  <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">×</a>
  <a href="dashboard.php">Dashboard</a>
  <a href="add_item.php">Add Item</a>
  <a href="users_list.php">Users List</a>
  <a href="deactivated_items.php">Deactivated Items</a>
  <a href="edit_profile.php">Profile Account</a>
  <!-- Logout button -->
  <a href="logout.php" class="logout-btn">Logout</a>
</div>

<?php } else { ?>
    <div class="navbar" id="myNavbar">
  <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">×</a>
  <a href="user_view.php">Home</a>
  <a href="edit_profile.php">Profile Account</a>
  <!-- Logout button -->
  <a href="logout.php" class="logout-btn">Logout</a>
</div>
<?php } ?>

<div id="main">
  <span style="font-size:30px;cursor:pointer;" onclick="openNav()">☰</span>
</div>

<script>
  function openNav() {
    document.getElementById("myNavbar").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
  }

  function closeNav() {
    document.getElementById("myNavbar").style.width = "0";
    document.getElementById("main").style.marginLeft= "0";
  }
</script>

</body>
</html>
