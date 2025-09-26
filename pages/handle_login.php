<?php
// Prevent direct access to this file
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// This file handles the POST request from the login form.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not a POST request, redirect to login
    header('Location: index.php?page=login');
    exit;
}

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Auth.php';
require_once BASE_PATH . '/src/Roles.php';
require_once BASE_PATH . '/src/Logger.php';

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

    // Fetch and store user permissions in the session
    $rolesModel = new Roles();
    $_SESSION['permissions'] = $rolesModel->getPermissionsForUser($user['officer_id']);

    Logger::log('login_success', "Benutzer '{$username}' hat sich erfolgreich angemeldet.", $user['officer_id']);

    // Redirect to the dashboard
    header('Location: index.php?page=dashboard');
    exit;

} else {
    // On failed login
    Logger::log('login_failed', "Fehlgeschlagener Anmeldeversuch für Benutzer '{$username}'.", null, ['username' => $username]);

    // Redirect back to the login page with an error message
    header('Location: index.php?page=login&error=invalid_credentials');
    exit;
}

?>