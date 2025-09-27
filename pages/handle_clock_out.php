<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['officer_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=mein_dienst');
    exit;
}

$recordId = $_POST['record_id'] ?? null;
if (!$recordId) {
    header('Location: index.php?page=mein_dienst&error=missing_record_id');
    exit;
}

require_once BASE_PATH . '/src/TimeClock.php';
require_once BASE_PATH . '/src/Dispatch.php';

try {
    $timeClock = new TimeClock($_SESSION['organization_id']);
    $success = $timeClock->clockOut($_SESSION['officer_id'], $recordId);

    if ($success) {
        // After successfully clocking out, also unassign the officer from dispatch.
        $dispatchModel = new Dispatch($_SESSION['organization_id']);
        $dispatchModel->unassignOfficer($_SESSION['officer_id']);
    } else {
        // Handle cases where clock-out fails, maybe set an error message
    }

} catch (Exception $e) {
    error_log("Clock-out process error: " . $e->getMessage());
}

// Redirect back to the main service page regardless of dispatch unassignment result
header('Location: index.php?page=mein_dienst');
exit;
?>