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

// requirePermission('hr_view'); // Will be enforced later

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';

// --- PAGE-SPECIFIC LOGIC ---
$officerModel = new Officer();
$officers = $officerModel->getAll();

$pageTitle = 'Personalabteilung';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <p class="text-gray-400">Verwalten Sie hier alle Beamten Ihrer Organisation.</p>
    <div>
        <a href="index.php?page=add_officer" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Beamten hinzufügen
        </a>
    </div>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="bg-green-500 text-white p-4 rounded-lg mb-6">
        <?php
        if ($_GET['status'] === 'officer_added') echo 'Der Beamte wurde erfolgreich hinzugefügt.';
        if ($_GET['status'] === 'officer_updated') echo 'Der Beamte wurde erfolgreich aktualisiert.';
        if ($_GET['status'] === 'roles_updated') echo 'Die Rollen des Beamten wurden erfolgreich aktualisiert.';
        ?>
    </div>
<?php endif; ?>

<div class="bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Dienstnummer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Rang</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Aktionen</th>
            </tr>
        </thead>
        <tbody class="bg-gray-800 divide-y divide-gray-700">
            <?php if (empty($officers)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">Keine Beamten in der Datenbank gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($officers as $officer): ?>
                    <tr class="hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?php echo htmlspecialchars($officer['badgeNumber']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($officer['firstName'] . ' ' . $officer['lastName']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($officer['rank']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $officer['isActive'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                                <?php echo $officer['isActive'] ? 'Aktiv' : 'Inaktiv'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="index.php?page=edit_user_roles&officer_id=<?php echo $officer['id']; ?>" class="text-indigo-400 hover:text-indigo-300 mr-4">Rollen</a>
                            <a href="index.php?page=edit_officer&id=<?php echo $officer['id']; ?>" class="text-indigo-400 hover:text-indigo-300">Bearbeiten</a>
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