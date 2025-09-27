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

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=mailbox');
    exit;
}

require_once BASE_PATH . '/src/Mail.php';

// Collect data from the form
$recipientIds = $_POST['recipients'] ?? [];
$subject = $_POST['subject'] ?? '';
$body = $_POST['body'] ?? '';
$senderId = $_SESSION['officer_id'];

// Basic validation
if (empty($recipientIds) || empty($subject) || empty($body)) {
    header('Location: index.php?page=compose_email&error=missing_fields');
    exit;
}

try {
    $mailModel = new Mail($_SESSION['organization_id']);
    $success = $mailModel->create($senderId, $recipientIds, $subject, $body);

    if ($success) {
        header('Location: index.php?page=mailbox&status=sent');
    } else {
        header('Location: index.php?page=compose_email&error=send_failed');
    }
} catch (Exception $e) {
    error_log("Error sending email: " . $e->getMessage());
    header('Location: index.php?page=compose_email&error=unknown');
}

exit;
?>