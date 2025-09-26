<?php
// LSPD Management Application
// Main Entry Point

// --- CONFIGURATION & INITIALIZATION ---

// Simple error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session to manage user login state
session_start();

// Define a base path for includes
define('BASE_PATH', __DIR__);

// --- DEPENDENCIES ---
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Auth.php';


// --- ROUTING ---

// Determine the requested page, default to 'login'
// We'll use a simple query parameter like ?page=dashboard
$page = $_GET['page'] ?? 'login';

// Simple router to load the correct page content.
// We will create these files in the following steps.

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn && $page !== 'login' && $page !== 'handle_login') {
    // If not logged in, redirect to login page, but allow access to the login handler.
    header('Location: index.php?page=login');
    exit;
}

// Page whitelist to prevent including arbitrary files
$allowedPages = [
    'login',
    'handle_login',
    'logout',
    'dashboard', // Will be the main dispatch page
    'mein_dienst',
    'hr',
    'fuhrpark',
    // ... more pages to come
];

if (in_array($page, $allowedPages)) {
    $pagePath = BASE_PATH . '/pages/' . $page . '.php';
    if (file_exists($pagePath)) {
        // The page file will handle its own logic and template including.
        include $pagePath;
    } else {
        // For now, show a simple error if the page file is missing
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>Die Seite '{$page}' konnte nicht gefunden werden.</p>";
    }
} else {
    // If page is not in whitelist, show 404
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>Ungültige Seite angefordert.</p>";
}

?>