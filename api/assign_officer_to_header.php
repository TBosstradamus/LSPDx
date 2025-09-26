<?php
// api/assign_officer_to_header.php

// --- BOOTSTRAP ---
header('Content-Type: application/json');
session_start();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Dispatch.php';
require_once BASE_PATH . '/src/Logger.php';

// --- SECURITY & INPUT VALIDATION ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is accepted.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$officerId = $input['officerId'] ?? null;
$roleName = $input['roleName'] ?? null;

$allowedRoles = ['dispatch', 'co-dispatch', 'air1', 'air2'];
if (!is_numeric($officerId) || !in_array($roleName, $allowedRoles)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data provided.']);
    exit;
}


// --- ACTION ---
try {
    $dispatchModel = new Dispatch();
    $success = $dispatchModel->assignOfficerToHeader($officerId, $roleName);

    if ($success) {
        Logger::log('header_role_assigned', "Beamter {$officerId} wurde der Rolle '{$roleName}' zugewiesen.");
        echo json_encode(['success' => true, 'message' => 'Header role assigned successfully.']);
    } else {
        http_response_code(500);
        Logger::log('header_role_assign_failed', "Fehler bei Zuweisung von Beamten {$officerId} zur Rolle {$roleName}.", null, $input);
        echo json_encode(['success' => false, 'message' => 'Failed to assign officer to header role.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Assign Officer to Header API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}

?>