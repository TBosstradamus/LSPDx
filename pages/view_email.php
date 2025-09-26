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

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Email.php';

// --- PAGE-SPECIFIC LOGIC ---
$emailId = $_GET['id'] ?? null;
if (!$emailId) {
    header('Location: index.php?page=mailbox');
    exit;
}

$emailModel = new Email();

// Mark the email as read for the current user upon viewing
$emailModel->markAsRead($emailId, $_SESSION['officer_id']);

// Fetch the email details
$email = $emailModel->findById($emailId);

if (!$email) {
    header('Location: index.php?page=mailbox&error=not_found');
    exit;
}


// --- TEMPLATE ---
$pageTitle = 'E-Mail anzeigen';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
.email-view-container {
    background-color: #2d3748;
    border-radius: 0.5rem;
    overflow: hidden;
}
.email-header {
    padding: 1.5rem;
    border-bottom: 1px solid #4a5568;
}
.email-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    color: #f7fafc;
}
.email-meta-grid {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: 0.5rem 1rem;
    color: #cbd5e0;
}
.email-meta-grid .label {
    font-weight: bold;
    color: #a0aec0;
    text-align: right;
}
.email-body {
    padding: 1.5rem;
    line-height: 1.7;
    white-space: pre-wrap; /* Respects line breaks and spacing */
}
.email-actions {
    padding: 1rem;
    border-top: 1px solid #4a5568;
}
</style>

<!-- Start of page-specific content -->
<div class="email-view-container">
    <div class="email-header">
        <h2><?php echo htmlspecialchars($email['subject']); ?></h2>
        <div class="email-meta-grid">
            <div class="label">Von:</div>
            <div><?php echo htmlspecialchars($email['senderFirstName'] . ' ' . $email['senderLastName']); ?></div>

            <div class="label">An:</div>
            <div><?php echo htmlspecialchars(implode(', ', $email['to'])); ?></div>

            <?php if (!empty($email['cc'])): ?>
                <div class="label">CC:</div>
                <div><?php echo htmlspecialchars(implode(', ', $email['cc'])); ?></div>
            <?php endif; ?>

            <div class="label">Datum:</div>
            <div><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($email['timestamp']))); ?></div>
        </div>
    </div>
    <div class="email-body">
        <?php echo nl2br(htmlspecialchars($email['body'])); ?>
    </div>
    <div class="email-actions">
        <a href="index.php?page=mailbox" class="button button-secondary">Zurück zum Posteingang</a>
    </div>
</div>

<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>