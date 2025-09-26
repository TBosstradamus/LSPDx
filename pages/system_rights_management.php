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
$rolesModel = new Roles();
$roles = $rolesModel->getAllRoles(); // This is now scoped to the organization

// --- TEMPLATE ---
$pageTitle = 'Rollen & Berechtigungen';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<p>Verwalten Sie hier die Rollen und die damit verbundenen Berechtigungen für Ihre Organisation.</p>

<section id="roles-list">
    <h2>Rollen-Liste</h2>
    <table>
        <thead>
            <tr>
                <th>Rollen-Name</th>
                <th>Beschreibung</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($roles)): ?>
                <tr>
                    <td colspan="3" style="text-align: center;">Keine Rollen für diese Organisation gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($role['name']); ?></td>
                        <td><?php echo htmlspecialchars($role['description']); ?></td>
                        <td>
                            <a href="index.php?page=edit_role_permissions&role_id=<?php echo $role['id']; ?>" class="button button-secondary">Berechtigungen bearbeiten</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>