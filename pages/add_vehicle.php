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
requirePermission('fleet_manage');

// --- TEMPLATE ---
$pageTitle = 'Fahrzeug hinzufügen';
include_once BASE_PATH . '/templates/header.php';

// Vehicle categories from the original app
$categories = ['SUV Scout', 'Buffalo', 'Cruiser', 'Interceptor'];
?>

<style>
    .form-container { max-width: 800px; margin: 0 auto; background-color: #2d3748; padding: 2rem; border-radius: 0.5rem; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.5rem; color: #a0aec0; }
    .form-group input, .form-group select { width: 100%; padding: 0.75rem; border-radius: 0.25rem; background-color: #1a202c; border: 1px solid #4a5568; color: #e2e8f0; box-sizing: border-box; }
    .form-actions { grid-column: 1 / -1; margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; }
</style>

<div class="form-container">
    <form action="index.php?page=handle_add_vehicle" method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Fahrzeugname (z.B. "Scout 1")</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="licensePlate">Kennzeichen</label>
                <input type="text" id="licensePlate" name="licensePlate" required>
            </div>
            <div class="form-group">
                <label for="category">Kategorie</label>
                <select id="category" name="category" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
             <div class="form-group">
                <label for="capacity">Sitzplätze</label>
                <input type="number" id="capacity" name="capacity" min="1" max="10" value="4" required>
            </div>
             <div class="form-group">
                <label for="mileage">Kilometerstand</label>
                <input type="number" id="mileage" name="mileage" min="0" value="0" required>
            </div>
            <div class="form-group">
                <label for="lastCheckup">Letzter Checkup</label>
                <input type="date" id="lastCheckup" name="lastCheckup">
            </div>
             <div class="form-group">
                <label for="nextCheckup">Nächster Checkup</label>
                <input type="date" id="nextCheckup" name="nextCheckup">
            </div>
        </div>
        <div class="form-actions">
            <a href="index.php?page=fuhrpark" class="button button-secondary">Abbrechen</a>
            <button type="submit" class="button">Fahrzeug speichern</button>
        </div>
    </form>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>