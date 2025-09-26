<?php
// api/dispatch_status.php

// --- BOOTSTRAP ---
header('Content-Type: application/json');
session_start();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Dispatch.php';

// --- SECURITY CHECK ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}


// --- DATA FETCHING ---
try {
    $dispatchModel = new Dispatch();
    $dispatch_data = $dispatchModel->getState();

    // --- OUTPUT ---
    echo json_encode($dispatch_data);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log("Dispatch API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}

?>