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
    // Redirect if not a POST request
    header('Location: index.php?page=mein_dienst');
    exit;
}

$recordId = $_POST['record_id'] ?? null;

if (!$recordId) {
    // Redirect if the record ID is missing
    header('Location: index.php?page=mein_dienst&error=missing_record_id');
    exit;
}

require_once BASE_PATH . '/src/TimeClock.php';

try {
    // Instantiate TimeClock with the organization_id from the session
    $timeClock = new TimeClock($_SESSION['organization_id']);

    // Attempt to clock out the user
    $success = $timeClock->clockOut($_SESSION['officer_id'], $recordId);

    if (!$success) {
        // Handle cases where clock-out fails
        // header('Location: index.php?page=mein_dienst&error=clockout_failed');
        // exit;
    }

} catch (Exception $e) {
    // Log the error and redirect with a generic error message
    error_log("Clock-out error: " . $e->getMessage());
    // header('Location: index.php?page=mein_dienst&error=clockout_failed');
    // exit;
}

// Redirect back to the main service page
header('Location: index.php?page=mein_dienst');
exit;
?>