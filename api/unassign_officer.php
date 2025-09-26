<?php
// api/unassign_officer.php

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

if (!is_numeric($officerId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data provided.']);
    exit;
}


// --- ACTION ---
try {
    $dispatchModel = new Dispatch();
    $success = $dispatchModel->unassignOfficerFromAll($officerId);

    if ($success) {
        Logger::log('officer_unassigned', "Beamter {$officerId} wurde von allen Zuweisungen entfernt.");
        echo json_encode(['success' => true, 'message' => 'Officer unassigned successfully.']);
    } else {
        http_response_code(500);
        Logger::log('officer_unassign_failed', "Fehler beim Entfernen der Zuweisung für Beamten {$officerId}.", null, $input);
        echo json_encode(['success' => false, 'message' => 'Failed to unassign officer.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Unassign Officer API Error: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
}

?>