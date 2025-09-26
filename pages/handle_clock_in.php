<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=mein_dienst');
    exit;
}

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/TimeClock.php';

// --- LOGIC ---
$officerId = $_SESSION['officer_id'];

$timeClockModel = new TimeClock();
$success = $timeClockModel->clockIn($officerId);

if ($success) {
    header('Location: index.php?page=mein_dienst&status=clocked_in');
    exit;
} else {
    header('Location: index.php?page=mein_dienst&error=clock_in_failed');
    exit;
}
?>