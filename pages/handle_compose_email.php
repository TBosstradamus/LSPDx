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
    header('Location: index.php?page=mailbox');
    exit;
}

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Email.php';

// --- LOGIC ---
$senderId = $_SESSION['officer_id'];
$recipientIds = $_POST['recipients'] ?? [];
$ccIds = $_POST['cc_recipients'] ?? [];
$subject = $_POST['subject'] ?? 'Kein Betreff';
$body = $_POST['body'] ?? '';

// Basic validation
if (empty($recipientIds) || empty($subject) || empty($body)) {
    header('Location: index.php?page=compose_email&error=missing_fields');
    exit;
}

$emailModel = new Email();
$success = $emailModel->send($senderId, $recipientIds, $ccIds, $subject, $body);

if ($success) {
    // Success: Redirect to the mailbox page with a success message.
    header('Location: index.php?page=mailbox&status=email_sent');
    exit;
} else {
    // Failure: Redirect back to the form with an error.
    header('Location: index.php?page=compose_email&error=send_failed');
    exit;
}
?>