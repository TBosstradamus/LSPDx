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
$emailModel = new Email();
$inbox = $emailModel->getInboxForOfficer($_SESSION['officer_id']);

// --- TEMPLATE ---
$pageTitle = 'Posteingang';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
.email-container {
    background-color: #2d3748;
    border-radius: 0.5rem;
    overflow: hidden;
}
.email-actions {
    padding: 1rem;
    border-bottom: 1px solid #4a5568;
}
.email-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.email-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #4a5568;
    text-decoration: none;
    color: #cbd5e0;
    transition: background-color 0.2s;
}
.email-item:hover {
    background-color: #4a5568;
}
.email-item.unread {
    background-color: #3b4d68;
    font-weight: bold;
}
.email-item .sender {
    width: 200px;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.email-item .subject {
    flex-grow: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.email-item .timestamp {
    width: 150px;
    flex-shrink: 0;
    text-align: right;
    font-size: 0.9rem;
    color: #a0aec0;
}
</style>

<!-- Start of page-specific content -->
<div class="email-container">
    <div class="email-actions">
        <a href="index.php?page=compose_email" class="button">Neue E-Mail</a>
    </div>
    <ul class="email-list">
        <?php if (empty($inbox)): ?>
            <li style="padding: 2rem; text-align: center; color: #a0aec0;">Ihr Posteingang ist leer.</li>
        <?php else: ?>
            <?php foreach ($inbox as $email): ?>
                <a href="index.php?page=view_email&id=<?php echo $email['id']; ?>" class="email-item <?php echo !$email['is_read'] ? 'unread' : ''; ?>">
                    <div class="sender">
                        <?php echo htmlspecialchars($email['senderFirstName'] . ' ' . $email['senderLastName']); ?>
                    </div>
                    <div class="subject">
                        <?php echo htmlspecialchars($email['subject']); ?>
                    </div>
                    <div class="timestamp">
                        <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($email['timestamp']))); ?>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>