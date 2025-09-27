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

require_once BASE_PATH . '/src/Auth.php';
require_once BASE_PATH . '/src/Log.php';

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
    $_SESSION['roles'] = $user['roles'] ?? [];
    $_SESSION['permissions'] = $user['permissions'] ?? [];

    // Add a log entry for successful login
    Log::add('login_success', "User '{$user['username']}' logged in successfully.");

    // Redirect to the dashboard
    header('Location: index.php?page=dashboard');
    exit;

} else {
    // Add a log entry for failed login attempt
    Log::add('login_failed', "Failed login attempt for username '{$username}'.");

    // On failed login
    header('Location: index.php?page=login&error=invalid_credentials');
    exit;
}
?>