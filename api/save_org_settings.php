<?php
// api/save_org_settings.php

header('Content-Type: application/json');
session_start();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Organization.php';
require_once BASE_PATH . '/src/auth_helpers.php';
require_once BASE_PATH . '/src/Logger.php';

// --- SECURITY CHECK ---
// requirePermission('system_org_manage'); // Will be enforced later

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is accepted.']);
    exit;
}

// --- ACTION ---
try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['settings']) || !is_array($input['settings'])) {
        throw new Exception("Invalid input data.");
    }

    $orgModel = new Organization();
    $success = $orgModel->updateSharingSettings($input['settings']);

    if ($success) {
        Logger::log('org_sharing_updated', "Freigabeeinstellungen für Organisationen wurden aktualisiert.");
        echo json_encode(['success' => true, 'message' => 'Einstellungen erfolgreich gespeichert.']);
    } else {
        throw new Exception("Failed to save settings to the database.");
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Save Org Settings API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}
?>