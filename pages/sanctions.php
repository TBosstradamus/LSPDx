<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
// requirePermission('hr_sanctions_manage');

require_once BASE_PATH . '/src/Sanction.php';

$sanctionModel = new Sanction();
$sanctions = $sanctionModel->getAll();

$pageTitle = 'Sanktionsverwaltung';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="flex justify-between items-center mb-6">
    <p class="text-gray-400">Übersicht aller verhängten Sanktionen.</p>
    <div>
        <a href="index.php?page=add_sanction" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" /></svg>
            Sanktion hinzufügen
        </a>
    </div>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'sanction_added'): ?>
    <div class="bg-green-500 text-white p-4 rounded-lg mb-6">
        Die Sanktion wurde erfolgreich verhängt.
    </div>
<?php endif; ?>

<div class="bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Datum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Betroffener</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Art</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Grund</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Ausgestellt von</th>
            </tr>
        </thead>
        <tbody class="bg-gray-800 divide-y divide-gray-700">
            <?php if (empty($sanctions)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">Keine Sanktionen gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($sanctions as $sanction): ?>
                    <tr class="hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($sanction['timestamp']))); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white"><?php echo htmlspecialchars($sanction['officerFirstName'] . ' ' . $sanction['officerLastName']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($sanction['sanctionType']); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-300 max-w-sm truncate"><?php echo htmlspecialchars($sanction['reason']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"><?php echo htmlspecialchars($sanction['issuerFirstName'] . ' ' . $sanction['issuerLastName']); ?></td>
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