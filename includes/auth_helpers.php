<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: /"); // Redirect to home if not admin
        exit;
    }
}

function currentUser() {
    return isset($_SESSION['user_id']) ? $_SESSION : null;
}

function logout() {
    session_unset();
    session_destroy();
}
?>
