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

$roleId = $_GET['id'] ?? null;
$role = null;
$rolePermissions = [];

if ($roleId) {
    // Editing an existing role
    $role = $roleModel->findById($roleId);
    if ($role) {
        $rolePermissions = $roleModel->getPermissionsForRole($roleId);
    } else {
        // Role not found in this organization, redirect
        header('Location: index.php?page=manage_roles&error=not_found');
        exit;
    }
}

$allPermissions = $roleModel->getAllPermissions();
$permissionsByCategory = [];
foreach ($allPermissions as $permission) {
    $permissionsByCategory[$permission['category']][] = $permission;
}

$pageTitle = $roleId ? 'Rolle bearbeiten' : 'Neue Rolle erstellen';
include_once BASE_PATH . '/templates/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white"><?php echo $pageTitle; ?></h1>
        </div>
    </div>

    <form action="index.php?page=handle_edit_role" method="POST">
        <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($role['id'] ?? ''); ?>">
        <div class="bg-brand-card border border-brand-border rounded-lg shadow space-y-6 p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-brand-text-primary">Rollenname</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($role['name'] ?? ''); ?>" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-brand-text-primary">Beschreibung</label>
                    <input type="text" name="description" id="description" value="<?php echo htmlspecialchars($role['description'] ?? ''); ?>" class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                </div>
            </div>

            <div class="border-t border-brand-border my-6"></div>

            <div>
                <h3 class="text-lg font-semibold text-white">Berechtigungen</h3>
                <div class="mt-4 space-y-6">
                    <?php foreach ($permissionsByCategory as $category => $permissions): ?>
                        <fieldset>
                            <legend class="text-base font-medium text-brand-text-primary"><?php echo htmlspecialchars($category); ?></legend>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($permissions as $permission): ?>
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="perm-<?php echo $permission['id']; ?>" name="permissions[]" type="checkbox" value="<?php echo $permission['id']; ?>"
                                                   class="focus:ring-brand-blue h-4 w-4 text-brand-blue border-gray-500 rounded"
                                                   <?php echo in_array($permission['id'], $rolePermissions) ? 'checked' : ''; ?>>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="perm-<?php echo $permission['id']; ?>" class="font-medium text-brand-text-primary"><?php echo htmlspecialchars($permission['name']); ?></label>
                                            <p class="text-brand-text-secondary"><?php echo htmlspecialchars($permission['description']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="index.php?page=manage_roles" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">Abbrechen</a>
                <button type="submit" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Rolle speichern</button>
            </div>
        </div>
    </form>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>