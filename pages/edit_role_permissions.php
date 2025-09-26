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
requirePermission('system_rights_manage');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Roles.php';

// --- PAGE-SPECIFIC LOGIC ---
$roleId = $_GET['role_id'] ?? null;
if (!$roleId) {
    header('Location: index.php?page=system_rights_management');
    exit;
}

$rolesModel = new Roles();
// Note: We need a way to get a single role's details, let's assume a method exists or add it later.
// For now, we'll just proceed.
$allPermissions = $rolesModel->getAllPermissions();
$rolePermissions = $rolesModel->getPermissionIdsForRole($roleId);


$pageTitle = 'Berechtigungen bearbeiten';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .form-container { max-width: 900px; margin: 0 auto; background-color: #2d3748; padding: 2rem; border-radius: 0.5rem; }
    .permission-category { margin-bottom: 2rem; }
    .permission-category h3 { color: #90cdf4; border-bottom: 1px solid #4a5568; padding-bottom: 0.5rem; }
    .permission-list { list-style: none; padding: 0; column-count: 2; column-gap: 2rem; }
    .permission-item { display: flex; align-items: center; margin-bottom: 0.75rem; }
    .permission-item input[type="checkbox"] { width: 18px; height: 18px; margin-right: 1rem; }
    .permission-item label { color: #e2e8f0; margin: 0; }
    .permission-item .description { font-size: 0.85rem; color: #a0aec0; }
    .form-actions { margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; }
</style>

<div class="form-container">
    <form action="index.php?page=handle_edit_role_permissions" method="POST">
        <input type="hidden" name="role_id" value="<?php echo $roleId; ?>">

        <?php foreach ($allPermissions as $category => $permissions): ?>
            <div class="permission-category">
                <h3><?php echo htmlspecialchars($category); ?></h3>
                <ul class="permission-list">
                    <?php foreach ($permissions as $permission): ?>
                        <li class="permission-item">
                            <input type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>" id="perm-<?php echo $permission['id']; ?>"
                                <?php echo in_array($permission['id'], $rolePermissions) ? 'checked' : ''; ?>>
                            <div>
                                <label for="perm-<?php echo $permission['id']; ?>"><?php echo htmlspecialchars($permission['name']); ?></label>
                                <div class="description"><?php echo htmlspecialchars($permission['description']); ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <div class="form-actions">
            <a href="index.php?page=system_rights_management" class="button button-secondary">Abbrechen</a>
            <button type="submit" class="button">Berechtigungen speichern</button>
        </div>
    </form>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>