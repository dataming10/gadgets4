<?php
include('includes/config.php');

class UserRegistration{
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function registerUser($username, $password){
        $check_result = $this->checkUsernameAvailability($username);

        if ($check_result->num_rows > 0){
            return "Username already taken. Please choose a different username.";
        } else{
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $registrationResult = $this->insertUser($username, $hashedPassword);

            if ($registrationResult === true){
                return "Registration successful. You can now login.";
            } else{
                return "Error: " . $registrationResult;
            }
        }
    }

    private function checkUsernameAvailability($username){
        $check_username_sql = "SELECT * FROM users WHERE LOWER(username) = LOWER(?)";
        $check_stmt = $this->conn->prepare($check_username_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();

        return $check_stmt->get_result();
    }

    private function insertUser($username, $hashedPassword){
        $insert_user_sql = "INSERT INTO users (username, password, is_admin, status) VALUES (?, ?, 1, 0)";
        $insert_stmt = $this->conn->prepare($insert_user_sql);
        $insert_stmt->bind_param("ss", $username, $hashedPassword);

        if ($insert_stmt->execute()){
            $insert_stmt->close();
            return true;
        } else{
            $error = $insert_stmt->error;
            $insert_stmt->close();
            return $error;
        }
    }

    public function closeConnection(){
        $this->conn->close();
    }
}

// Usage
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $userRegistration = new UserRegistration($conn);
    $result = $userRegistration->registerUser($username, $password);

    if (is_string($result)) {
        $error = $result;
    } else {
        $success = $result;
    }

    $userRegistration->closeConnection(); // Move the closeConnection call here
}

?>

<!-- HTML CODE --> 

<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h2>Registration</h2>
    <form method="post" action="">
        <label>Username:</label>
        <input type="text" name="username" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Register</button><br>
        <a href="login.php">Already have an account?</a>
    </form>
    <?php if(isset($error)) { echo $error; } ?>
    <?php if(isset($success)) { echo $success; } ?>
</body>
</html>