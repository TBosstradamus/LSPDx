<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('system_rights_manage');

require_once BASE_PATH . '/src/Role.php';
$roleModel = new Role($_SESSION['organization_id']);
$roles = $roleModel->getAll();

$pageTitle = 'Rollen & Berechtigungen';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white"><?php echo $pageTitle; ?></h1>
        <p class="mt-1 text-brand-text-secondary">Verwalten Sie die Rollen und die damit verbundenen Berechtigungen für Ihre Organisation.</p>
    </div>
    <div>
        <a href="index.php?page=edit_role" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Neue Rolle erstellen
        </a>
    </div>
</div>

<div class="bg-brand-card border border-brand-border rounded-lg shadow">
    <div class="divide-y divide-brand-border">
        <?php if (empty($roles)): ?>
            <div class="p-6 text-center text-brand-text-secondary">
                Keine Rollen gefunden.
            </div>
        <?php else: ?>
            <?php foreach ($roles as $role): ?>
                <div class="p-4 hover:bg-gray-800/50 flex justify-between items-center">
                    <div>
                        <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($role['name']); ?></div>
                        <div class="text-xs text-brand-text-secondary"><?php echo htmlspecialchars($role['description']); ?></div>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <a href="index.php?page=edit_role&id=<?php echo $role['id']; ?>" class="text-brand-blue hover:underline">Berechtigungen bearbeiten</a>
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