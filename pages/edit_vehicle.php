<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
// requirePermission('fleet_manage');

require_once BASE_PATH . '/src/Vehicle.php';

$vehicleId = $_GET['id'] ?? null;
if (!$vehicleId) {
    header('Location: index.php?page=fuhrpark'); exit;
}

$vehicleModel = new Vehicle($_SESSION['organization_id']);
$vehicle = $vehicleModel->findById($vehicleId);

if (!$vehicle) {
    header('Location: index.php?page=fuhrpark&error=not_found'); exit;
}

$categories = ['SUV Scout', 'Buffalo', 'Cruiser', 'Interceptor'];

$pageTitle = 'Fahrzeug bearbeiten';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="max-w-3xl mx-auto">
    <div class="bg-gray-800 rounded-lg shadow-lg">
        <div class="p-6">
            <form action="index.php?page=handle_edit_vehicle" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($vehicle['id']); ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Vehicle Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300">Fahrzeugname</label>
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($vehicle['name']); ?>" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <!-- License Plate -->
                    <div>
                        <label for="licensePlate" class="block text-sm font-medium text-gray-300">Kennzeichen</label>
                        <input type="text" name="licensePlate" id="licensePlate" value="<?php echo htmlspecialchars($vehicle['licensePlate']); ?>" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-300">Kategorie</label>
                        <select id="category" name="category" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($vehicle['category'] === $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Capacity -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-300">Sitzplätze</label>
                        <input type="number" name="capacity" id="capacity" min="1" max="10" value="<?php echo htmlspecialchars($vehicle['capacity']); ?>" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <!-- Mileage -->
                    <div class="md:col-span-2">
                        <label for="mileage" class="block text-sm font-medium text-gray-300">Kilometerstand</label>
                        <input type="number" name="mileage" id="mileage" min="0" value="<?php echo htmlspecialchars($vehicle['mileage']); ?>" required class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md shadow-sm text-white focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="index.php?page=fuhrpark" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Abbrechen
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                        Änderungen speichern
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