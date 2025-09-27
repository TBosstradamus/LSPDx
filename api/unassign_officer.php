<?php
// api/unassign_officer.php

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('dispatch_manage');

require_once BASE_PATH . '/src/Dispatch.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['officerId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input.']);
    exit;
}

try {
    $dispatchModel = new Dispatch($_SESSION['organization_id']);
    $success = $dispatchModel->unassignOfficer($data['officerId']);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to unassign officer.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An internal server error occurred.', 'message' => $e->getMessage()]);
}
exit;
?>