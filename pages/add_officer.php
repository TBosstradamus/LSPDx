<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('hr_officers_manage');

$ranks = [
  'Police Officer I', 'Police Officer II', 'Police Officer III', 'Detective',
  'Sergeant', 'Sr. Sergeant', 'Lieutenant', 'Captain', 'Commander',
  'Deputy Chief of Police', 'Assistant Chief of Police', 'Chief of Police',
];

$pageTitle = 'Beamten hinzufügen';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="max-w-3xl mx-auto">
    <div class="bg-brand-card border border-brand-border rounded-lg shadow-lg">
        <div class="p-6">
            <form action="index.php?page=handle_add_officer" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- First Name -->
                    <div>
                        <label for="firstName" class="block text-sm font-medium text-brand-text-primary">Vorname</label>
                        <input type="text" name="firstName" id="firstName" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                    </div>
                    <!-- Last Name -->
                    <div>
                        <label for="lastName" class="block text-sm font-medium text-brand-text-primary">Nachname</label>
                        <input type="text" name="lastName" id="lastName" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                    </div>
                    <!-- Badge Number -->
                    <div>
                        <label for="badgeNumber" class="block text-sm font-medium text-brand-text-primary">Dienstnummer</label>
                        <input type="text" name="badgeNumber" id="badgeNumber" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                    </div>
                    <!-- Phone Number -->
                    <div>
                        <label for="phoneNumber" class="block text-sm font-medium text-brand-text-primary">Telefonnummer</label>
                        <input type="text" name="phoneNumber" id="phoneNumber" class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                    </div>
                    <!-- Rank -->
                    <div class="md:col-span-2">
                        <label for="rank" class="block text-sm font-medium text-brand-text-primary">Rang</label>
                        <select id="rank" name="rank" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                            <?php foreach ($ranks as $rank): ?>
                                <option value="<?php echo htmlspecialchars($rank); ?>"><?php echo htmlspecialchars($rank); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-sm font-medium text-brand-text-primary">Geschlecht</label>
                        <select id="gender" name="gender" required class="mt-1 block w-full bg-brand-bg border-brand-border rounded-md shadow-sm text-white focus:ring-brand-blue focus:border-brand-blue">
                            <option value="male">Männlich</option>
                            <option value="female">Weiblich</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="index.php?page=hr" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Abbrechen
                    </a>
                    <button type="submit" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                        Beamten speichern
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