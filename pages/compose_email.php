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

require_once BASE_PATH . '/src/Officer.php';
$officerModel = new Officer($_SESSION['organization_id']);
$officers = $officerModel->getAll();

$pageTitle = 'Neue Nachricht';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Neue Nachricht verfassen</h1>
            <p class="mt-1 text-brand-text-secondary">Senden Sie eine interne Nachricht.</p>
        </div>
    </div>

    <form action="index.php?page=handle_compose_email" method="POST">
        <div class="bg-brand-card border border-brand-border rounded-lg shadow space-y-6 p-6">
            <div>
                <label for="recipients" class="block text-sm font-medium text-brand-text-primary">Empfänger</label>
                <select id="recipients" name="recipients[]" multiple required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-brand-text-primary focus:ring-brand-blue focus:border-brand-blue">
                    <?php foreach ($officers as $officer): ?>
                        <option value="<?php echo $officer['id']; ?>"><?php echo htmlspecialchars($officer['firstName'] . ' ' . $officer['lastName']); ?> (#<?php echo $officer['badgeNumber']; ?>)</option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-2 text-sm text-brand-text-secondary">Halten Sie Strg (oder Cmd auf Mac) gedrückt, um mehrere Empfänger auszuwählen.</p>
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-brand-text-primary">Betreff</label>
                <input type="text" name="subject" id="subject" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-brand-text-primary focus:ring-brand-blue focus:border-brand-blue">
            </div>

            <div>
                <label for="body" class="block text-sm font-medium text-brand-text-primary">Nachricht</label>
                <textarea id="body" name="body" rows="10" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-brand-text-primary focus:ring-brand-blue focus:border-brand-blue"></textarea>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="index.php?page=mailbox" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                    Abbrechen
                </a>
                <button type="submit" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                    Nachricht Senden
                </button>
            </div>
        </div>
    </form>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>