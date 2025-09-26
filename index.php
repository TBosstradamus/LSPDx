<?php
// LSPD Management Application - Stable Core Version
// Main Entry Point

// --- CONFIGURATION & INITIALIZATION ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
define('BASE_PATH', __DIR__);

// --- DEPENDENCIES ---
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Auth.php';

// --- ROUTING ---
$page = $_GET['page'] ?? 'login';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn && $page !== 'login' && $page !== 'handle_login') {
    header('Location: index.php?page=login');
    exit;
}

// --- Define Routes and their directories ---
$pageRoutes = [
    'login', 'handle_login', 'logout', 'dashboard', 'dispatch', 'mein_dienst',
    'hr', 'add_officer', 'handle_add_officer', 'edit_officer', 'handle_edit_officer',
    'sanctions', 'add_sanction', 'handle_add_sanction',
    'credentials', 'handle_regenerate_password',
    'fuhrpark', 'add_vehicle', 'handle_add_vehicle', 'edit_vehicle', 'handle_edit_vehicle', 'handle_delete_vehicle',
    'fleet_management', 'handle_toggle_vehicle_duty',
    'documents', 'add_document', 'handle_add_document', 'view_document', 'handle_delete_document',
    'training_modules', 'add_training_module', 'handle_add_training_module', 'view_training_module', 'handle_delete_training_module',
    'checklists', 'edit_checklist', 'handle_edit_checklist',
    'mailbox', 'compose_email', 'handle_compose_email', 'view_email',
    'it_logs',
    'time_approval',
    'handle_time_approval',
];

$apiRoutes = [
    'assign_officer_to_vehicle', 'unassign_officer', 'assign_officer_to_header', 'assign_officer_to_activity',
    'dispatch_status',
    'set_vehicle_status', 'set_vehicle_funk', 'set_vehicle_callsign',
    'get_settings',
];

$filePath = null;

if (in_array($page, $pageRoutes)) {
    $filePath = BASE_PATH . '/pages/' . $page . '.php';
} elseif (in_array($page, $apiRoutes)) {
    $filePath = BASE_PATH . '/api/' . $page . '.php';
}

if ($filePath && file_exists($filePath)) {
    include $filePath;
} else {
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>Die Seite '{$page}' konnte nicht gefunden werden oder ist nicht in der Whitelist.</p>";
}

?>