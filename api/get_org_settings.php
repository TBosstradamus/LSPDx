<?php
// api/get_org_settings.php

header('Content-Type: application/json');
session_start();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Organization.php';
require_once BASE_PATH . '/src/auth_helpers.php';

// --- SECURITY CHECK ---
// This is a high-level admin function.
// requirePermission('system_org_manage'); // Will be enforced later


// --- ACTION ---
try {
    $orgModel = new Organization();

    $organizations = $orgModel->getAll();
    $sharingSettings = $orgModel->getSharingSettings();

    echo json_encode([
        'organizations' => $organizations,
        'sharing_settings' => $sharingSettings
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Get Org Settings API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}

?>