<?php
// api/set_vehicle_callsign.php

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

require_once BASE_PATH . '/src/Vehicle.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['vehicleId']) || !isset($data['callsign'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input.']);
    exit;
}

try {
    $vehicleModel = new Vehicle($_SESSION['organization_id']);
    $success = $vehicleModel->updateCallsign($data['vehicleId'], $data['callsign']);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update vehicle callsign.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An internal server error occurred.', 'message' => $e->getMessage()]);
}
exit;
?>