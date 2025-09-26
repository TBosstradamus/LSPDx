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
requirePermission('hr_manage_credentials');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Auth.php';

// --- PAGE-SPECIFIC LOGIC ---
$authModel = new Auth();
$users = $authModel->getAllUsersWithOfficerDetails();

// Check if a new password was just generated and stored in the session
$newPasswordInfo = null;
if (isset($_SESSION['new_password_info'])) {
    $newPasswordInfo = $_SESSION['new_password_info'];
    // Unset the session variable so it's only shown once
    unset($_SESSION['new_password_info']);
}

// --- TEMPLATE ---
$pageTitle = 'Zugangsdaten verwalten';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<p>Verwalten Sie hier die Benutzerkonten und setzen Sie bei Bedarf Passwörter zurück.</p>

<?php if ($newPasswordInfo): ?>
    <div class="message-success">
        Das Passwort für <strong><?php echo htmlspecialchars($newPasswordInfo['username']); ?></strong> wurde erfolgreich zurückgesetzt.
        <br>
        Das neue Passwort lautet: <strong style="font-size: 1.2em; background-color: #1a202c; padding: 0.2rem 0.5rem; border-radius: 4px;"><?php echo htmlspecialchars($newPasswordInfo['password']); ?></strong>
        <br>
        <small>Bitte geben Sie dieses Passwort sicher an den Benutzer weiter. Es wird nur einmal angezeigt.</small>
    </div>
<?php endif; ?>

<section id="user-list">
    <h2>Benutzerkonten</h2>
    <table>
        <thead>
            <tr>
                <th>Beamter</th>
                <th>Dienstnummer</th>
                <th>Benutzername</th>
                <th>Erstellt am</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Keine Benutzerkonten gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($user['badgeNumber']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars(date('d.m.Y', strtotime($user['createdAt']))); ?></td>
                        <td>
                            <form action="index.php?page=handle_regenerate_password" method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                <button type="submit" class="button button-secondary" onclick="return confirm('Sind Sie sicher, dass Sie das Passwort für <?php echo htmlspecialchars($user['username']); ?> zurücksetzen möchten?');">
                                    Passwort zurücksetzen
                                </button>
                            </form>
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