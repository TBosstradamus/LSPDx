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

require_once BASE_PATH . '/src/TimeClock.php';

try {
    // Instantiate TimeClock with the organization_id from the session
    $timeClock = new TimeClock($_SESSION['organization_id']);

    // Attempt to clock in the user
    $success = $timeClock->clockIn($_SESSION['officer_id']);

    if (!$success) {
        // Handle cases where clock-in is not allowed (e.g., already clocked in)
        // You can add a specific error message if you want.
        // For now, just redirect.
    }

} catch (Exception $e) {
    // Log the error and redirect with a generic error message
    error_log("Clock-in error: " . $e->getMessage());
    // In a real app, you might want a more user-friendly error page
    // header('Location: index.php?page=mein_dienst&error=clockin_failed');
    // exit;
}

// Redirect back to the main service page
header('Location: index.php?page=mein_dienst');
exit;
?>