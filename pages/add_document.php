<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
// requirePermission('manage_documents');

$pageTitle = 'Neues Dokument erstellen';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="max-w-4xl mx-auto">
    <div class="bg-gray-800 rounded-lg shadow-lg">
        <div class="p-6">
            <form action="index.php?page=handle_add_document" method="POST">
                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-300">Titel des Dokuments</label>
                        <input type="text" name="title" id="title" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-300">Inhalt</label>
                        <p class="text-xs text-gray-400 mb-1">Einfaches Markdown wird unterstützt (z.B. `# Überschrift`, `*fett*`, `- Listenpunkt`).</p>
                        <textarea id="content" name="content" rows="15" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500 font-mono"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="index.php?page=documents" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Abbrechen
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                        Dokument speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>