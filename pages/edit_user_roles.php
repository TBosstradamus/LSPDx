<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('hr_manage_roles');

require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/Role.php';

$officerId = $_GET['officer_id'] ?? null;
if (!$officerId) {
    header('Location: index.php?page=hr'); exit;
}

$officerModel = new Officer($_SESSION['organization_id']);
$officer = $officerModel->findByIdInOrg($officerId);

if (!$officer) {
    header('Location: index.php?page=hr&error=not_found'); exit;
}

$roleModel = new Role($_SESSION['organization_id']);
$allRoles = $roleModel->getAll();
$userRoles = $roleModel->getRolesForOfficer($officerId);
$userRoleNames = array_column($userRoles, 'name');

$pageTitle = 'Rollen bearbeiten';
include_once BASE_PATH . '/templates/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="bg-brand-card border border-brand-border rounded-lg shadow-lg">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-white">Rollen für <?php echo htmlspecialchars($officer['firstName'] . ' ' . $officer['lastName']); ?></h2>
            <form action="index.php?page=handle_edit_user_roles" method="POST" class="mt-6">
                <input type="hidden" name="officer_id" value="<?php echo $officer['id']; ?>">

                <div class="space-y-4">
                    <?php foreach ($allRoles as $role): ?>
                        <div class="flex items-center">
                            <input id="role-<?php echo $role['id']; ?>" name="roles[]" type="checkbox" value="<?php echo $role['id']; ?>"
                                   class="h-4 w-4 text-brand-blue bg-brand-bg border-brand-border rounded focus:ring-brand-blue"
                                   <?php echo in_array($role['name'], $userRoleNames) ? 'checked' : ''; ?>>
                            <label for="role-<?php echo $role['id']; ?>" class="ml-3 block text-sm font-medium text-brand-text-primary">
                                <?php echo htmlspecialchars($role['name']); ?>
                                <span class="text-brand-text-secondary ml-2"><?php echo htmlspecialchars($role['description']); ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="index.php?page=hr" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Abbrechen
                    </a>
                    <button type="submit" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                        Rollen speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>