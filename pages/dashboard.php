<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login');
    exit;
}

require_once BASE_PATH . '/src/Officer.php';
// Use the organization_id from the session to correctly instantiate the model
$officerModel = new Officer($_SESSION['organization_id']);
$currentUser = $officerModel->findByIdInOrg($_SESSION['officer_id']);

if (!$currentUser) {
    // Failsafe, redirect to logout if user data is inconsistent
    header('Location: index.php?page=logout');
    exit;
}

$pageTitle = 'Dashboard';
include_once BASE_PATH . '/templates/header.php';

// Define the quick access links for the dashboard cards
$quickLinks = [
    'mein_dienst' => [
        'name' => 'Mein Dienst',
        'desc' => 'Dienststatus und persönliche Informationen verwalten.',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />'
    ],
    'hr' => [
        'name' => 'Personal',
        'desc' => 'Personal der Abteilung einsehen und verwalten.',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.663M12 12A3 3 0 1012 6a3 3 0 000 6z" />'
    ],
    'fuhrpark' => [
        'name' => 'Fahrzeuge',
        'desc' => 'Greifen Sie auf die Fahrzeugflotte zu.',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5v-1.875a3.375 3.375 0 013.375-3.375h1.5a1.125 1.125 0 011.125 1.125v-1.5a3.375 3.375 0 013.375-3.375H9.75" />'
    ],
    'mailbox' => [
        'name' => 'Postfach',
        'desc' => 'Überprüfen Sie Ihre internen Nachrichten.',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />'
    ],
];
?>

<!-- Start of page-specific content -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-white">Dashboard</h1>
    <p class="mt-1 text-brand-text-secondary">Willkommen zurück, Officer <?php echo htmlspecialchars($currentUser['lastName']); ?>.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php foreach ($quickLinks as $page => $details): ?>
    <a href="index.php?page=<?php echo $page; ?>" class="group block bg-brand-card border border-brand-border rounded-lg p-6 hover:border-brand-blue transition-colors duration-200">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-gray-700/50 rounded-md p-3">
                 <svg class="h-6 w-6 text-brand-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <?php echo $details['icon']; ?>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-brand-text-primary"><?php echo $details['name']; ?></h3>
                <p class="text-sm text-brand-text-secondary mt-1"><?php echo $details['desc']; ?></p>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<!-- End of page-specific content -->
<?php
include_once BASE_PATH . '/templates/footer.php';
?>