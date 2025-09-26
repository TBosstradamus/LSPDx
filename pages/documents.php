<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
// requirePermission('documents_view');

require_once BASE_PATH . '/src/Document.php';

$documentModel = new Document();
$documents = $documentModel->getAll();

$pageTitle = 'Dokumente';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <p class="text-gray-400">Verwalten Sie hier dienstliche Dokumente wie Regelwerke und Anleitungen.</p>
    <div>
        <a href="index.php?page=add_document" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Dokument hinzufügen
        </a>
    </div>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="bg-green-500 text-white p-4 rounded-lg mb-6">
        <?php
        if ($_GET['status'] === 'document_added') echo 'Das Dokument wurde erfolgreich hinzugefügt.';
        if ($_GET['status'] === 'document_deleted') echo 'Das Dokument wurde erfolgreich gelöscht.';
        ?>
    </div>
<?php endif; ?>

<div class="bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Titel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Erstellt von</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Erstellt am</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Aktionen</th>
            </tr>
        </thead>
        <tbody class="bg-gray-800 divide-y divide-gray-700">
            <?php if (empty($documents)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-400">Keine Dokumente gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                    <tr class="hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?php echo htmlspecialchars($doc['title']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($doc['firstName'] . ' ' . $doc['lastName']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars(date('d.m.Y', strtotime($doc['created_at']))); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="index.php?page=view_document&id=<?php echo $doc['id']; ?>" class="text-indigo-400 hover:text-indigo-300 mr-4">Anzeigen</a>
                            <form action="index.php?page=handle_delete_document" method="POST" class="inline">
                                <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                                <button type="submit" class="text-red-400 hover:text-red-300" onclick="return confirm('Sind Sie sicher, dass Sie dieses Dokument löschen möchten?');">
                                    Löschen
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>