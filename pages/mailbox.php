<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['officer_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}

require_once BASE_PATH . '/src/Mail.php';
require_once BASE_PATH . '/src/Officer.php';

$mailModel = new Mail($_SESSION['organization_id']);
$officerModel = new Officer($_SESSION['organization_id']); // Instantiate Officer model for display names

$inbox = $mailModel->getInboxForOfficer($_SESSION['officer_id']);

$pageTitle = 'Postfach';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-white">Postfach</h1>
        <a href="index.php?page=compose_email" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
            Neue Nachricht
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg shadow-lg">
        <!-- Tabs -->
        <div class="px-4 py-3 border-b border-gray-700">
            <nav class="flex space-x-4" aria-label="Tabs">
                <a href="#" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium" aria-current="page">Posteingang</a>
                <a href="#" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Gesendet</a>
                <a href="#" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Entwürfe</a>
            </nav>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Von</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Betreff</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Datum</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-900 divide-y divide-gray-800">
                        <?php if (empty($inbox)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 text-center">Ihr Posteingang ist leer.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inbox as $email): ?>
                                <tr class="hover:bg-gray-700 cursor-pointer <?php echo $email['is_read'] ? 'text-gray-400' : 'text-white font-bold'; ?>" onclick="window.location.href='index.php?page=view_email&id=<?php echo $email['id']; ?>';">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php echo htmlspecialchars($email['sender_name'] ?? 'System'); ?>
                                        <span class="text-gray-500"><?php echo htmlspecialchars($email['sender_rank'] ?? ''); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($email['subject']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d.m.Y H:i', strtotime($email['timestamp'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- End of page-specific content -->

<?php
include_once BASE_PATH . '/templates/footer.php';
?>