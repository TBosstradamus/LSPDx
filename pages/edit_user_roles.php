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

// TODO: Add permission check for Admin

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Roles.php';
require_once BASE_PATH . '/src/Officer.php';

// --- PAGE-SPECIFIC LOGIC ---
$officerId = $_GET['officer_id'] ?? null;
if (!$officerId) {
    header('Location: index.php?page=hr');
    exit;
}

$officerModel = new Officer();
$officer = $officerModel->findById($officerId);

if (!$officer) {
    header('Location: index.php?page=hr&error=not_found');
    exit;
}

$rolesModel = new Roles();
$allRoles = $rolesModel->getAllRoles();
$userRoles = $rolesModel->getRolesForUser($officerId);
$userRoleIds = array_column($userRoles, 'id');


$pageTitle = 'Rollen bearbeiten: ' . htmlspecialchars($officer['firstName'] . ' ' . $officer['lastName']);
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .form-container { max-width: 800px; margin: 0 auto; background-color: #2d3748; padding: 2rem; border-radius: 0.5rem; }
    .form-group { margin-bottom: 1rem; }
    .role-list { list-style: none; padding: 0; }
    .role-item { display: flex; align-items: center; margin-bottom: 0.75rem; }
    .role-item input[type="checkbox"] { width: 20px; height: 20px; margin-right: 1rem; }
    .role-item label { color: #e2e8f0; margin: 0; }
    .role-item .description { font-size: 0.9rem; color: #a0aec0; margin-left: 2.5rem; }
    .form-actions { margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; }
</style>

<div class="form-container">
    <form action="index.php?page=handle_edit_user_roles" method="POST">
        <input type="hidden" name="officer_id" value="<?php echo $officer['id']; ?>">

        <div class="form-group">
            <h3>Rollen für <?php echo htmlspecialchars($officer['firstName'] . ' ' . $officer['lastName']); ?></h3>
            <ul class="role-list">
                <?php foreach ($allRoles as $role): ?>
                    <li class="role-item">
                        <input type="checkbox" name="roles[]" value="<?php echo $role['id']; ?>" id="role-<?php echo $role['id']; ?>"
                            <?php echo in_array($role['id'], $userRoleIds) ? 'checked' : ''; ?>>
                        <div>
                            <label for="role-<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></label>
                            <div class="description"><?php echo htmlspecialchars($role['description']); ?></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="form-actions">
            <a href="index.php?page=hr" class="button button-secondary">Abbrechen</a>
            <button type="submit" class="button">Rollen speichern</button>
        </div>
    </form>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>