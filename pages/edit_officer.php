<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('hr_officers_manage');

require_once BASE_PATH . '/src/Officer.php';

$officerId = $_GET['id'] ?? null;
if (!$officerId) {
    header('Location: index.php?page=hr'); exit;
}

$officerModel = new Officer($_SESSION['organization_id']);
$officer = $officerModel->findByIdInOrg($officerId);

if (!$officer) {
    header('Location: index.php?page=hr&error=not_found'); exit;
}

$ranks = [
  'Police Officer I', 'Police Officer II', 'Police Officer III', 'Detective',
  'Sergeant', 'Sr. Sergeant', 'Lieutenant', 'Captain', 'Commander',
  'Deputy Chief of Police', 'Assistant Chief of Police', 'Chief of Police',
];

$pageTitle = 'Beamten bearbeiten';
include_once BASE_PATH . '/templates/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="bg-brand-card border border-brand-border rounded-lg shadow-lg">
        <div class="p-6">
            <form action="index.php?page=handle_edit_officer" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($officer['id']); ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="firstName" class="block text-sm font-medium text-brand-text-primary">Vorname</label>
                        <input type="text" name="firstName" id="firstName" value="<?php echo htmlspecialchars($officer['firstName']); ?>" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                    </div>
                    <div>
                        <label for="lastName" class="block text-sm font-medium text-brand-text-primary">Nachname</label>
                        <input type="text" name="lastName" id="lastName" value="<?php echo htmlspecialchars($officer['lastName']); ?>" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                    </div>
                    <div>
                        <label for="badgeNumber" class="block text-sm font-medium text-brand-text-primary">Dienstnummer</label>
                        <input type="text" name="badgeNumber" id="badgeNumber" value="<?php echo htmlspecialchars($officer['badgeNumber']); ?>" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                    </div>
                    <div>
                        <label for="phoneNumber" class="block text-sm font-medium text-brand-text-primary">Telefonnummer</label>
                        <input type="text" name="phoneNumber" id="phoneNumber" value="<?php echo htmlspecialchars($officer['phoneNumber']); ?>" class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                    </div>
                    <div class="md:col-span-2">
                        <label for="rank" class="block text-sm font-medium text-brand-text-primary">Rang</label>
                        <select id="rank" name="rank" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                            <?php foreach ($ranks as $rank): ?>
                                <option value="<?php echo htmlspecialchars($rank); ?>" <?php echo ($officer['rank'] === $rank) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rank); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="gender" class="block text-sm font-medium text-brand-text-primary">Geschlecht</label>
                        <select id="gender" name="gender" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                            <option value="male" <?php echo ($officer['gender'] === 'male') ? 'selected' : ''; ?>>Männlich</option>
                            <option value="female" <?php echo ($officer['gender'] === 'female') ? 'selected' : ''; ?>>Weiblich</option>
                        </select>
                    </div>
                    <div>
                        <label for="isActive" class="block text-sm font-medium text-brand-text-primary">Status</label>
                        <select id="isActive" name="isActive" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                            <option value="1" <?php echo ($officer['isActive']) ? 'selected' : ''; ?>>Aktiv</option>
                            <option value="0" <?php echo (!$officer['isActive']) ? 'selected' : ''; ?>>Inaktiv</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="index.php?page=hr" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Abbrechen
                    </a>
                    <button type="submit" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                        Änderungen speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once BASE_PATH . '/templates/footer.php';
?>