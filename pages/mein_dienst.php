<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['officer_id']) || !isset($_SESSION['organization_id'])) {
    header('Location: index.php?page=login'); exit;
}

require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/TimeClock.php';

// Fix: Instantiate Officer model with the organization_id from the session
$officerModel = new Officer($_SESSION['organization_id']);
$currentUser = $officerModel->findByIdInOrg($_SESSION['officer_id']);

if (!$currentUser) {
    error_log("Could not find officer with ID {$_SESSION['officer_id']} in organization {$_SESSION['organization_id']}");
    header('Location: index.php?page=logout'); exit;
}

// Instantiate TimeClock model
try {
    $timeClockModel = new TimeClock($currentUser['organization_id']);
    $currentClockIn = $timeClockModel->getCurrentStatus($currentUser['id']);
} catch (InvalidArgumentException $e) {
    error_log($e->getMessage());
    die("A critical error occurred. The time clock feature is unavailable.");
}

function formatDuration($totalSeconds) {
    if (!$totalSeconds || $totalSeconds < 0) return '0h 0m';
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    return "{$hours}h {$minutes}m";
}

$pageTitle = 'My Profile';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Officer Details Card -->
        <div class="bg-brand-card border border-brand-border rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-xl font-bold text-white mb-4">Officer Details</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-sm font-medium text-brand-text-secondary">Name</dt>
                        <dd class="mt-1 text-lg text-white"><?php echo htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-brand-text-secondary">Badge Number</dt>
                        <dd class="mt-1 text-lg text-white">#<?php echo htmlspecialchars($currentUser['badgeNumber']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-brand-text-secondary">Rank</dt>
                        <dd class="mt-1 text-lg text-white"><?php echo htmlspecialchars($currentUser['rank']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-brand-text-secondary">Phone Number</dt>
                        <dd class="mt-1 text-lg text-white"><?php echo htmlspecialchars($currentUser['phoneNumber'] ?? 'N/A'); ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Licenses Card -->
        <div class="bg-brand-card border border-brand-border rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-xl font-bold text-white mb-4">Your Licenses</h3>
                <p class="text-brand-text-secondary">This feature will be implemented in a future update.</p>
            </div>
        </div>
    </div>

    <!-- Right Column (Time Clock) -->
    <div class="lg:col-span-1">
        <div class="bg-brand-card border border-brand-border rounded-lg shadow p-6 text-center">
            <h3 class="text-xl font-bold text-white mb-4">Time Clock</h3>
            <?php if ($currentClockIn): ?>
                <p class="text-brand-text-secondary">Current Session:</p>
                <div id="running-time-display" class="text-5xl font-mono font-bold text-white my-4">00:00:00</div>
                <form action="index.php?page=handle_clock_out" method="POST">
                    <input type="hidden" name="record_id" value="<?php echo $currentClockIn['id']; ?>">
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg text-lg transition-colors">
                        Clock Out
                    </button>
                </form>
            <?php else: ?>
                <p class="text-brand-text-secondary">You are currently off duty.</p>
                <div class="text-5xl font-mono font-bold text-gray-600 my-4">--:--:--</div>
                 <form action="index.php?page=handle_clock_in" method="POST">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-lg transition-colors">
                        Clock In
                    </button>
                </form>
            <?php endif; ?>
            <div class="mt-6 border-t border-brand-border pt-4">
                <p class="text-sm font-medium text-brand-text-secondary">Total Approved Duty Time</p>
                <p class="text-2xl font-bold text-white mt-1"><?php echo formatDuration($currentUser['totalHours']); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($currentClockIn): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const display = document.getElementById('running-time-display');
    // Use the UTC timestamp from the server
    const clockInTimestamp = new Date('<?php echo $currentClockIn['clockInTime'] . 'Z'; ?>').getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const duration = now - clockInTimestamp;

        if (duration < 0) return;

        const hours = String(Math.floor(duration / 3600000)).padStart(2, '0');
        const minutes = String(Math.floor((duration % 3600000) / 60000)).padStart(2, '0');
        const seconds = String(Math.floor((duration % 60000) / 1000)).padStart(2, '0');

        display.textContent = `${hours}:${minutes}:${seconds}`;
    }

    // Set up the interval and also run it immediately
    updateTimer();
    setInterval(updateTimer, 1000);
});
</script>
<?php endif; ?>

<!-- End of page-specific content -->
<?php
include_once BASE_PATH . '/templates/footer.php';
?>