<?php
session_start();
include('includes/config.php');

class Redirector {
    public static function redirectToDashboard() {
        header("Location: dashboard.php", true, 302);
        exit();
    }

    public static function redirectToUserView() {
        header("Location: user_view.php", true, 302);
        exit();
    }

    public static function redirectToLogin() {
        header("Location: login.php", true, 302);
        exit();
    }
}

if (!isset($_SESSION['user_id'])) {
    Redirector::redirectToLogin();
}

$is_admin = $_SESSION['is_admin'];

if ($is_admin) {
    Redirector::redirectToUserView();
} else {
    Redirector::redirectToDashboard();
}
?>
