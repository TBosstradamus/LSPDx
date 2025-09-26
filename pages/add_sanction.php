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
requirePermission('hr_manage_sanctions');

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';

// --- PAGE-SPECIFIC LOGIC ---
$officerModel = new Officer();
$officers = $officerModel->getAll();

// Define the available sanction types
$sanctionTypes = [
  'Verwarnung',
  'Suspendierung (24h)',
  'Suspendierung (72h)',
  'Degradierung',
  'Entlassung',
];

// --- TEMPLATE ---
$pageTitle = 'Sanktion hinzufügen';
include_once BASE_PATH . '/templates/header.php';
?>

<style>
    .form-container { max-width: 800px; margin: 0 auto; background-color: #2d3748; padding: 2rem; border-radius: 0.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; color: #a0aec0; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border-radius: 0.25rem; background-color: #1a202c; border: 1px solid #4a5568; color: #e2e8f0; box-sizing: border-box; font-family: inherit; }
    .form-group textarea { min-height: 150px; }
    .form-actions { margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; }
</style>

<div class="form-container">
    <form action="index.php?page=handle_add_sanction" method="POST">

        <div class="form-group">
            <label for="officer_id">Betroffener Beamter</label>
            <select id="officer_id" name="officer_id" required>
                <option value="">-- Beamten auswählen --</option>
                <?php foreach ($officers as $officer): ?>
                    <option value="<?php echo $officer['id']; ?>">
                        <?php echo htmlspecialchars($officer['firstName'] . ' ' . $officer['lastName'] . ' (#' . $officer['badgeNumber'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="sanctionType">Art der Sanktion</label>
            <select id="sanctionType" name="sanctionType" required>
                 <?php foreach ($sanctionTypes as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="reason">Begründung</label>
            <textarea id="reason" name="reason" required></textarea>
        </div>

        <div class="form-actions">
            <a href="index.php?page=sanctions" class="button button-secondary">Abbrechen</a>
            <button type="submit" class="button button-danger">Sanktion verhängen</button>
        </div>
    </form>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>