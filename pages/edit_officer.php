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
requirePermission('hr_manage_officers');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';

// --- PAGE-SPECIFIC LOGIC ---
$officerId = $_GET['id'] ?? null;
if (!$officerId) {
    // Redirect if no ID is provided
    header('Location: index.php?page=hr');
    exit;
}

$officerModel = new Officer();
$officer = $officerModel->findById($officerId);

if (!$officer) {
    // Officer not found, redirect
    header('Location: index.php?page=hr&error=not_found');
    exit;
}

// List of ranks for the dropdown
$ranks = [
  'Police Officer I', 'Police Officer II', 'Police Officer III', 'Detective',
  'Sergeant', 'Sr. Sergeant', 'Lieutenant', 'Captain', 'Commander',
  'Deputy Chief of Police', 'Assistant Chief of Police', 'Chief of Police',
];

// --- TEMPLATE ---
$pageTitle = 'Beamten bearbeiten';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .form-container { max-width: 800px; margin: 0 auto; background-color: #2d3748; padding: 2rem; border-radius: 0.5rem; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; color: #a0aec0; }
    .form-group input, .form-group select { width: 100%; padding: 0.75rem; border-radius: 0.25rem; background-color: #1a202c; border: 1px solid #4a5568; color: #e2e8f0; box-sizing: border-box; }
    .form-actions { grid-column: 1 / -1; margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; }
</style>

<div class="form-container">
    <form action="index.php?page=handle_edit_officer" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($officer['id']); ?>">

        <div class="form-grid">
            <div class="form-group">
                <label for="firstName">Vorname</label>
                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($officer['firstName']); ?>" required>
            </div>
            <div class="form-group">
                <label for="lastName">Nachname</label>
                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($officer['lastName']); ?>" required>
            </div>
            <div class="form-group">
                <label for="badgeNumber">Dienstnummer</label>
                <input type="text" id="badgeNumber" name="badgeNumber" value="<?php echo htmlspecialchars($officer['badgeNumber']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phoneNumber">Telefonnummer</label>
                <input type="text" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($officer['phoneNumber'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="rank">Rang</label>
                <select id="rank" name="rank" required>
                    <?php foreach ($ranks as $rank): ?>
                        <option value="<?php echo htmlspecialchars($rank); ?>" <?php echo ($officer['rank'] === $rank) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($rank); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="gender">Geschlecht</label>
                <select id="gender" name="gender" required>
                    <option value="male" <?php echo ($officer['gender'] === 'male') ? 'selected' : ''; ?>>Männlich</option>
                    <option value="female" <?php echo ($officer['gender'] === 'female') ? 'selected' : ''; ?>>Weiblich</option>
                </select>
            </div>

            <?php if ($officer['organization_id'] == 2): // Show only for FIB (ID 2 is default for FIB) ?>
            <div class="form-group">
                <label for="display_name">Agentenname (Display Name)</label>
                <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($officer['display_name'] ?? ''); ?>">
            </div>
            <?php endif; ?>
             <div class="form-group">
                <label for="isActive">Status</label>
                <select id="isActive" name="isActive" required>
                    <option value="1" <?php echo ($officer['isActive'] == 1) ? 'selected' : ''; ?>>Aktiv</option>
                    <option value="0" <?php echo ($officer['isActive'] == 0) ? 'selected' : ''; ?>>Inaktiv</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?page=hr" class="button button-secondary">Abbrechen</a>
            <button type="submit" class="button">Änderungen speichern</button>
        </div>
    </form>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>