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

// requirePermission('hr_manage_roles'); // This will be added in the rights management phase

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/Roles.php';

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
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - LSPD Intranet</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
        <form action="index.php?page=handle_edit_user_roles" method="POST">
            <input type="hidden" name="officer_id" value="<?php echo $officer['id']; ?>">

            <div class="form-group">
                <h3>Rollen</h3>
                <?php if (empty($allRoles)): ?>
                    <p>Keine Rollen im System definiert.</p>
                <?php else: ?>
                    <?php foreach ($allRoles as $role): ?>
                        <div class="role-item">
                            <input type="checkbox" name="roles[]" value="<?php echo $role['id']; ?>" id="role-<?php echo $role['id']; ?>"
                                <?php echo in_array($role['id'], $userRoleIds) ? 'checked' : ''; ?>>
                            <label for="role-<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></label>
                            <p class="description"><?php echo htmlspecialchars($role['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a href="index.php?page=hr" class="button button-secondary">Abbrechen</a>
                <button type="submit" class="button">Rollen speichern</button>
            </div>
        </form>
    </div>
</body>
</html>