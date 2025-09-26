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
require_once BASE_PATH . '/src/Auth.php';
require_once BASE_PATH . '/src/Officer.php';

// --- LOGIN LOGIC ---
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$auth = new Auth();
$user = $auth->login($username, $password);

if ($user) {
    // On successful login
    session_regenerate_id(true);

    // Fetch the full officer record to get the organization_id
    $officerModel = new Officer();
    $officer = $officerModel->findById($user['officer_id']);

    if (!$officer || !isset($officer['organization_id'])) {
        // Data integrity issue: user exists but corresponding officer or org_id is missing.
        error_log("Login Error: User ID {$user['id']} logged in, but officer data (ID: {$user['officer_id']}) is incomplete or missing.");
        header('Location: index.php?page=login&error=account_data_missing');
        exit;
    }

    // Store all required info in the session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['officer_id'] = $user['officer_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['organization_id'] = $officer['organization_id'];

    // Redirect to the dashboard
    header('Location: index.php?page=dashboard');
    exit;

} else {
    // On failed login
    header('Location: index.php?page=login&error=invalid_credentials');
    exit;
}
?>