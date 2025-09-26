<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
// requirePermission('hr_manage_credentials');

require_once BASE_PATH . '/src/Auth.php';

$authModel = new Auth();
$users = $authModel->getAllUsersWithOfficerDetails();

$newPasswordInfo = null;
if (isset($_SESSION['new_password_info'])) {
    $newPasswordInfo = $_SESSION['new_password_info'];
    unset($_SESSION['new_password_info']);
}

$pageTitle = 'Zugangsdaten verwalten';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<p class="text-gray-400 mb-6">Verwalten Sie hier die Benutzerkonten und setzen Sie bei Bedarf Passwörter zurück.</p>

<?php if ($newPasswordInfo): ?>
    <div class="bg-green-500 text-white p-4 rounded-lg mb-6">
        Das Passwort für <strong><?php echo htmlspecialchars($newPasswordInfo['username']); ?></strong> wurde erfolgreich zurückgesetzt.
        <br>
        Das neue Passwort lautet: <code class="text-lg bg-gray-900 px-2 py-1 rounded"><?php echo htmlspecialchars($newPasswordInfo['password']); ?></code>
        <br>
        <small>Bitte geben Sie dieses Passwort sicher an den Benutzer weiter. Es wird nur einmal angezeigt.</small>
    </div>
<?php endif; ?>

<div class="bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Beamter</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Dienstnummer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Benutzername</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Erstellt am</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Aktionen</th>
            </tr>
        </thead>
        <tbody class="bg-gray-800 divide-y divide-gray-700">
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">Keine Benutzerkonten gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($user['badgeNumber']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars(date('d.m.Y', strtotime($user['createdAt']))); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <form action="index.php?page=handle_regenerate_password" method="POST" class="inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                <button type="submit" class="text-indigo-400 hover:text-indigo-300" onclick="return confirm('Sind Sie sicher, dass Sie das Passwort für <?php echo htmlspecialchars($user['username']); ?> zurücksetzen möchten?');">
                                    Passwort zurücksetzen
                                </button>
                            </form>
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