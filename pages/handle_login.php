<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// This file handles the POST request from the login form.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=login');
    exit;
}

// --- DEPENDENCIES ---
// Auth class is already loaded by index.php

// --- LOGIN LOGIC ---
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$auth = new Auth();
$user = $auth->login($username, $password);

if ($user) {
    // On successful login
    session_regenerate_id(true);

    // Store user info in the session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['officer_id'] = $user['officer_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['organization_id'] = $user['organization_id'];
    $_SESSION['roles'] = $user['roles'] ?? []; // Store roles in session

    // Redirect to the dashboard
    header('Location: index.php?page=dashboard');
    exit;

} else {
    // On failed login
    header('Location: index.php?page=login&error=invalid_credentials');
    exit;
}
?>