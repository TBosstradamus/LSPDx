<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['officer_id'])) {
    header('Location: index.php?page=login'); exit;
}

// This page requires authentication, so Auth should have been checked already,
// and $_SESSION['organization_id'] should be set.
require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/TimeClock.php';

// We instantiate the Officer model using the organization_id from the session.
$officerModel = new Officer();
$currentUser = $officerModel->findByIdInOrg($_SESSION['officer_id']);

if (!$currentUser) {
    // If the officer can't be found in their own organization, something is wrong.
    // Log out for safety.
    error_log("Could not find officer with ID {$_SESSION['officer_id']} in organization {$_SESSION['organization_id']}");
    header('Location: index.php?page=logout'); exit;
}

// Now that we have the user and we are sure they belong to the session's organization,
// we can safely instantiate the TimeClock model for that organization.
try {
    $timeClockModel = new TimeClock($currentUser['organization_id']);
    $currentClockIn = $timeClockModel->getCurrentStatus($currentUser['id']);
} catch (InvalidArgumentException $e) {
    // Handle the case where TimeClock fails to initialize, though it shouldn't with this logic.
    error_log($e->getMessage());
    // Display a user-friendly error instead of crashing.
    die("Ein kritischer Fehler ist aufgetreten. Die Stempeluhr-Funktion ist nicht verfügbar.");
}


// Placeholder for licenses
$licenses = [];

function formatDuration($totalSeconds) {
    if (!$totalSeconds || $totalSeconds < 0) return '0 Std. 0 Min.';
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    return "{$hours} Std. {$minutes} Min.";
}

$pageTitle = 'Mein Dienst';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personalakte Widget -->
        <div class="bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-xl font-bold text-white mb-4">Ihre Personalakte</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-400">Name</dt>
                    <dd class="mt-1 text-lg text-white"><?php echo htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-400">Dienstnummer</dt>
                    <dd class="mt-1 text-lg text-white">#<?php echo htmlspecialchars($currentUser['badgeNumber']); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-400">Rang</dt>
                    <dd class="mt-1 text-lg text-white"><?php echo htmlspecialchars($currentUser['rank']); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-400">Telefonnummer</dt>
                    <dd class="mt-1 text-lg text-white"><?php echo htmlspecialchars($currentUser['phoneNumber'] ?? 'N/A'); ?></dd>
                </div>
            </dl>
        </div>

        <!-- Lizenzen Widget -->
        <div class="bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-xl font-bold text-white mb-4">Ihre Lizenzen</h3>
            <div class="space-y-4">
                <p class="text-gray-400">Diese Funktion wird in einer zukünftigen Phase implementiert.</p>
                <!-- Placeholder for license list -->
            </div>
        </div>
    </div>

    <!-- Right Column (Time Clock) -->
    <div class="lg:col-span-1">
        <div class="bg-gray-800 rounded-lg shadow p-6 text-center">
            <h3 class="text-xl font-bold text-white mb-4">Stempeluhr</h3>
            <?php if ($currentClockIn): ?>
                <p class="text-gray-400">Aktuelle Dienstzeit:</p>
                <div id="running-time-display" class="text-5xl font-mono font-bold text-white my-4">00:00:00</div>
                <form action="index.php?page=handle_clock_out" method="POST">
                    <input type="hidden" name="record_id" value="<?php echo $currentClockIn['id']; ?>">
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg text-lg">
                        Ausstempeln
                    </button>
                </form>
            <?php else: ?>
                <p class="text-gray-400">Sie sind aktuell nicht im Dienst.</p>
                <div class="text-5xl font-mono font-bold text-gray-600 my-4">--:--:--</div>
                 <form action="index.php?page=handle_clock_in" method="POST">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-lg">
                        Einstempeln
                    </button>
                </form>
            <?php endif; ?>
            <div class="mt-6 border-t border-gray-700 pt-4">
                <p class="text-sm font-medium text-gray-400">Gesamte Dienstzeit</p>
                <p class="text-2xl font-bold text-white mt-1"><?php echo formatDuration($currentUser['totalHours']); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($currentClockIn): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const display = document.getElementById('running-time-display');
    const clockInTimestamp = new Date('<?php echo $currentClockIn['clockInTime']; ?>').getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const duration = now - clockInTimestamp;
        const hours = String(Math.floor(duration / 3600000)).padStart(2, '0');
        const minutes = String(Math.floor((duration % 3600000) / 60000)).padStart(2, '0');
        const seconds = String(Math.floor((duration % 60000) / 1000)).padStart(2, '0');
        display.textContent = `${hours}:${minutes}:${seconds}`;
    }
    updateTimer();
    setInterval(updateTimer, 1000);
});
</script>
<?php endif; ?>

<!-- End of page-specific content -->
<?php
include_once BASE_PATH . '/templates/footer.php';
?>