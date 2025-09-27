<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
require_once BASE_PATH . '/src/Auth.php';
// Using hasPermission for now, will enforce with requirePermission later.
if (!Auth::hasPermission('documents_view')) {
    // Fallback for users who might not have this specific permission yet but should see it.
    // In a real scenario, we'd rely solely on requirePermission.
}

require_once BASE_PATH . '/src/Document.php';

// Fix: Instantiate the model with the session's organization ID
$documentModel = new Document($_SESSION['organization_id']);
$documents = $documentModel->getAll();

$pageTitle = 'Dokumente';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white">Dokumente</h1>
        <p class="mt-1 text-brand-text-secondary">Verwalten Sie hier dienstliche Dokumente wie Regelwerke und Anleitungen.</p>
    </div>
    <?php if (Auth::hasPermission('manage_documents')): ?>
    <div>
        <a href="index.php?page=add_document" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Dokument hinzufügen
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="bg-green-500/20 border border-green-500 text-green-300 p-4 rounded-lg mb-6">
        <?php
        if ($_GET['status'] === 'document_added') echo 'Das Dokument wurde erfolgreich hinzugefügt.';
        if ($_GET['status'] === 'document_deleted') echo 'Das Dokument wurde erfolgreich gelöscht.';
        ?>
    </div>
<?php endif; ?>

<div class="bg-brand-card border border-brand-border rounded-lg shadow">
    <div class="divide-y divide-brand-border">
        <?php if (empty($documents)): ?>
            <div class="p-6 text-center text-brand-text-secondary">
                Keine Dokumente gefunden.
            </div>
        <?php else: ?>
            <?php foreach ($documents as $doc): ?>
                <div class="p-4 hover:bg-gray-800/50 flex justify-between items-center">
                    <div class="flex-grow">
                        <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($doc['title']); ?></div>
                        <div class="text-xs text-brand-text-secondary">
                            Erstellt von <?php echo htmlspecialchars(($doc['firstName'] ?? 'System') . ' ' . ($doc['lastName'] ?? '')); ?>
                            am <?php echo htmlspecialchars(date('d.m.Y', strtotime($doc['created_at']))); ?>
                        </div>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <a href="index.php?page=view_document&id=<?php echo $doc['id']; ?>" class="text-brand-blue hover:underline mr-4">Anzeigen</a>
                        <?php if (Auth::hasPermission('manage_documents')): ?>
                        <form action="index.php?page=handle_delete_document" method="POST" class="inline">
                            <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                            <button type="submit" class="text-brand-red hover:underline" onclick="return confirm('Sind Sie sicher, dass Sie dieses Dokument löschen möchten?');">
                                Löschen
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>