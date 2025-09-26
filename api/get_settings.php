<?php
// api/get_settings.php

// --- BOOTSTRAP ---
header('Content-Type: application/json');
session_start();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Settings.php';

// --- SECURITY CHECK ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

// --- ACTION ---
try {
    $settingsModel = new Settings();
    $callsignDataJson = $settingsModel->getSetting('callsign_data');

    if ($callsignDataJson) {
        // The data is stored as a JSON string, so we decode it before sending.
        $callsignData = json_decode($callsignDataJson, true);
        echo json_encode($callsignData);
    } else {
        echo json_encode(['error' => 'Callsign data not found.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Get Settings API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}

?>