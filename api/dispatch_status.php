<?php
// api/dispatch_status.php

// Prevent direct access and output buffering
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: application/json');

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/Vehicle.php';
require_once BASE_PATH . '/src/Dispatch.php';

$orgId = $_SESSION['organization_id'];

try {
    $officerModel = new Officer($orgId);
    $vehicleModel = new Vehicle($orgId);
    $dispatchModel = new Dispatch($orgId);

    $allOfficers = $officerModel->getAll();
    $allVehicles = $vehicleModel->getAll();
    $allAssignments = $dispatchModel->getAssignments();

    $assignedOfficerIds = array_column($allAssignments, 'officer_id');

    // Filter for available officers (those not in any assignment)
    $availableOfficers = array_filter($allOfficers, function ($officer) use ($assignedOfficerIds) {
        return !in_array($officer['id'], $assignedOfficerIds);
    });

    // Structure vehicle data and nest assigned officers
    $vehiclesWithAssignments = [];
    foreach ($allVehicles as $vehicle) {
        $vehicle['assigned_officers'] = array_fill(0, $vehicle['capacity'], null); // Initialize seats
        foreach ($allAssignments as $assignment) {
            if ($assignment['assignment_type'] === 'vehicle' && $assignment['assignment_id'] == $vehicle['id']) {
                $officer = array_values(array_filter($allOfficers, function($o) use ($assignment) {
                    return $o['id'] === $assignment['officer_id'];
                }))[0] ?? null;

                if ($officer && isset($assignment['seat_index'])) {
                    $vehicle['assigned_officers'][$assignment['seat_index']] = $officer;
                }
            }
        }
        $vehiclesWithAssignments[] = $vehicle;
    }

    // Separate assignments by type
    $headerAssignments = array_filter($allAssignments, fn($a) => $a['assignment_type'] === 'header');
    $activityAssignments = array_filter($allAssignments, fn($a) => $a['assignment_type'] === 'activity');


    // Prepare final JSON response
    $response = [
        'officers' => [
            'all' => array_values($allOfficers),
            'available' => array_values($availableOfficers),
        ],
        'vehicles' => $vehiclesWithAssignments,
        'assignments' => [
            'header' => array_values($headerAssignments),
            'activity' => array_values($activityAssignments)
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An internal server error occurred.', 'message' => $e->getMessage()]);
}

exit;
?>