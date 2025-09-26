<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
// requirePermission('hr_sanctions_manage');

require_once BASE_PATH . '/src/Officer.php';

$officerModel = new Officer();
$officers = $officerModel->getAll();

$sanctionTypes = [
  'Verwarnung',
  'Suspendierung (24h)',
  'Suspendierung (72h)',
  'Degradierung',
  'Entlassung',
];

$pageTitle = 'Sanktion verhängen';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="max-w-3xl mx-auto">
    <div class="bg-gray-800 rounded-lg shadow-lg">
        <div class="p-6">
            <form action="index.php?page=handle_add_sanction" method="POST">
                <div class="space-y-6">
                    <!-- Officer -->
                    <div>
                        <label for="officer_id" class="block text-sm font-medium text-gray-300">Betroffener Beamter</label>
                        <select id="officer_id" name="officer_id" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Beamten auswählen --</option>
                            <?php foreach ($officers as $officer): ?>
                                <option value="<?php echo $officer['id']; ?>">
                                    <?php echo htmlspecialchars($officer['lastName'] . ', ' . $officer['firstName'] . ' (#' . $officer['badgeNumber'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Sanction Type -->
                    <div>
                        <label for="sanctionType" class="block text-sm font-medium text-gray-300">Art der Sanktion</label>
                        <select id="sanctionType" name="sanctionType" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500">
                            <?php foreach ($sanctionTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Reason -->
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-300">Begründung</label>
                        <textarea id="reason" name="reason" rows="6" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="index.php?page=sanctions" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Abbrechen
                    </a>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
                        Sanktion verhängen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>