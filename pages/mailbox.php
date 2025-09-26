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

$mailModel = new Mail($_SESSION['organization_id']);
$inbox = $mailModel->getInboxForOfficer($_SESSION['officer_id']);

$pageTitle = 'Postfach';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white">Postfach</h1>
        <p class="mt-1 text-brand-text-secondary">Hier können Sie Ihre internen Nachrichten einsehen und verwalten.</p>
    </div>
    <div>
        <a href="index.php?page=compose_email" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            Neue Nachricht
        </a>
    </div>
</div>

<div class="bg-brand-card border border-brand-border rounded-lg shadow">
    <!-- Tabs -->
    <div class="px-6 border-b border-brand-border">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="#" class="border-brand-blue text-brand-blue whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" aria-current="page">
                Posteingang
            </a>
            <a href="#" class="border-transparent text-brand-text-secondary hover:text-brand-text-primary hover:border-gray-500 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Gesendet
            </a>
             <a href="#" class="border-transparent text-brand-text-secondary hover:text-brand-text-primary hover:border-gray-500 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Entwürfe
            </a>
        </nav>
    </div>

    <!-- Email List -->
    <div class="divide-y divide-brand-border">
        <?php if (empty($inbox)): ?>
            <div class="p-6 text-center text-brand-text-secondary">
                Ihr Posteingang ist leer.
            </div>
        <?php else: ?>
            <?php foreach ($inbox as $email): ?>
                <a href="index.php?page=view_email&id=<?php echo $email['id']; ?>" class="block p-4 hover:bg-gray-800/50">
                    <div class="flex items-center">
                        <?php if (!$email['is_read']): ?>
                            <div class="flex-shrink-0">
                                <span class="h-2.5 w-2.5 bg-brand-blue rounded-full"></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex-grow grid grid-cols-12 gap-4 ml-3">
                            <div class="col-span-4 <?php echo $email['is_read'] ? 'text-brand-text-secondary' : 'text-white font-bold'; ?>">
                                <?php echo htmlspecialchars($email['sender_name'] ?? 'System'); ?>
                            </div>
                            <div class="col-span-6 <?php echo $email['is_read'] ? 'text-brand-text-secondary' : 'text-white'; ?>">
                                <?php echo htmlspecialchars($email['subject']); ?>
                            </div>
                            <div class="col-span-2 text-right text-sm <?php echo $email['is_read'] ? 'text-gray-500' : 'text-brand-text-secondary'; ?>">
                                <?php echo date('d.m.Y H:i', strtotime($email['timestamp'])); ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>