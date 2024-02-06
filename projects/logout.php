<?php
session_start();

class LogoutHandler {
    public static function logout() {
        // Unset all of the session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();

        // Redirect to the login page or any other page after logout
        header("Location: login.php");
        exit();
    }
}

LogoutHandler::logout();
?>
