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

require_once BASE_PATH . '/src/Officer.php';
$officerModel = new Officer();
$currentUser = $officerModel->findById($_SESSION['officer_id']);

if (!$currentUser) {
    header('Location: index.php?page=logout');
    exit;
}

$pageTitle = 'Dashboard';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="rounded-lg bg-gray-800 p-6">
    <h2 class="text-xl font-bold text-white">Willkommen zurück, <?php echo htmlspecialchars($currentUser['firstName']); ?>!</h2>
    <p class="mt-2 text-gray-400">Dies ist Ihre zentrale Anlaufstelle. Wählen Sie einen Bereich aus, um fortzufahren.</p>
</div>

<div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <!-- Dispatch Widget -->
    <a href="index.php?page=dispatch" class="group block rounded-lg bg-gray-800 p-6 hover:bg-gray-700 transition-colors">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-white">Dispatch</h3>
                <p class="text-sm text-gray-400 mt-1">Echtzeit-Übersicht der Einheiten.</p>
            </div>
        </div>
    </a>

    <!-- Mein Dienst Widget -->
    <a href="index.php?page=mein_dienst" class="group block rounded-lg bg-gray-800 p-6 hover:bg-gray-700 transition-colors">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-white">Mein Dienst</h3>
                <p class="text-sm text-gray-400 mt-1">Stempeluhr, Lizenzen und Akte.</p>
            </div>
        </div>
    </a>

    <!-- HR Widget -->
    <a href="index.php?page=hr" class="group block rounded-lg bg-gray-800 p-6 hover:bg-gray-700 transition-colors">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197" /></svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-white">Personal</h3>
                <p class="text-sm text-gray-400 mt-1">Beamte und Sanktionen verwalten.</p>
            </div>
        </div>
    </a>
</div>

<!-- End of page-specific content -->
<?php
include_once BASE_PATH . '/templates/footer.php';
?>