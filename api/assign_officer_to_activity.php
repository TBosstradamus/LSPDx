<?php
// api/assign_officer_to_activity.php

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
$activityName = $input['activityName'] ?? null;

if (!is_numeric($officerId) || empty(trim($activityName))) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data provided.']);
    exit;
}


// --- ACTION ---
try {
    $dispatchModel = new Dispatch();
    $success = $dispatchModel->assignOfficerToActivity($officerId, $activityName);

    if ($success) {
        Logger::log('activity_assigned', "Beamter {$officerId} wurde der Tätigkeit '{$activityName}' zugewiesen.");
        echo json_encode(['success' => true, 'message' => 'Officer assigned to activity successfully.']);
    } else {
        http_response_code(500);
        Logger::log('activity_assign_failed', "Fehler bei Zuweisung von Beamten {$officerId} zur Tätigkeit {$activityName}.", null, $input);
        echo json_encode(['success' => false, 'message' => 'Failed to assign officer to activity.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Assign Officer to Activity API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}

?>