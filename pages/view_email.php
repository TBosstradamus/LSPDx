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

require_once BASE_PATH . '/src/Mail.php';

$emailId = $_GET['id'] ?? null;
if (!$emailId) {
    header('Location: index.php?page=mailbox&error=not_found');
    exit;
}

$mailModel = new Mail($_SESSION['organization_id']);
$email = $mailModel->getEmailById($emailId, $_SESSION['officer_id']);

// If email is found and user is authorized, mark it as read
if ($email) {
    $mailModel->markAsRead($emailId, $_SESSION['officer_id']);
}

$pageTitle = 'Nachricht ansehen';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="index.php?page=mailbox" class="text-brand-blue hover:underline">&larr; Zurück zum Postfach</a>
    </div>

    <?php if ($email): ?>
        <div class="bg-brand-card border border-brand-border rounded-lg shadow-lg">
            <div class="p-6 border-b border-brand-border">
                <h1 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($email['subject']); ?></h1>
                <div class="mt-2 flex items-center text-sm text-brand-text-secondary">
                    <p>
                        <strong>Von:</strong> <?php echo htmlspecialchars($email['sender_name'] ?? 'System'); ?>
                    </p>
                    <span class="mx-2">|</span>
                    <p>
                        <strong>An:</strong> <?php echo htmlspecialchars($email['recipients']); ?>
                    </p>
                    <span class="mx-2">|</span>
                    <p>
                        <?php echo date('d.m.Y \u\m H:i', strtotime($email['timestamp'])); ?>
                    </p>
                </div>
            </div>
            <div class="p-6">
                <div class="prose prose-invert max-w-none text-brand-text-primary">
                    <?php echo nl2br(htmlspecialchars($email['body'])); ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-brand-card border border-brand-border rounded-lg shadow-lg p-6 text-center">
            <h2 class="text-2xl font-bold text-white">Nachricht nicht gefunden</h2>
            <p class="mt-2 text-brand-text-secondary">Die angeforderte Nachricht konnte nicht gefunden werden oder Sie haben keine Berechtigung, sie anzuzeigen.</p>
        </div>
    <?php endif; ?>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>