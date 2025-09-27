<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
require_once BASE_PATH . '/src/Auth.php';
Auth::requirePermission('dispatch_view');

$pageTitle = 'Dispatch';
include_once BASE_PATH . '/templates/header.php';
?>

<!-- Start of page-specific content -->
<div class="h-[calc(100vh-10rem)] flex flex-col">
    <!-- Header with 10-Codes Button -->
    <div class="flex-shrink-0 flex justify-between items-center mb-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Dispatch Control</h1>
            <p class="mt-1 text-brand-text-secondary">Manage units and assignments in real-time.</p>
        </div>
        <button id="open-callsign-modal" class="bg-brand-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
            10-Codes
        </button>
    </div>

    <!-- Main Dispatch Layout -->
    <div class="flex-grow flex flex-row gap-6 overflow-hidden">
        <!-- Sidebar (Left) -->
        <aside class="w-80 flex-shrink-0 bg-brand-card border border-brand-border rounded-lg flex flex-col">
            <div class="px-4 py-3 border-b border-brand-border">
                <h3 class="text-lg font-bold text-white">Einheiten</h3>
            </div>
            <div id="officer-list" class="flex-1 overflow-y-auto p-2 space-y-2">
                <!-- Available officers will be loaded here by JS -->
            </div>
            <div class="border-t border-brand-border my-2"></div>
            <div class="px-4 py-2">
                 <h3 class="text-lg font-bold text-white">Weitere Tätigkeiten</h3>
            </div>
            <div id="activity-zones" class="flex-1 overflow-y-auto p-2 space-y-2">
                 <div class="activity-zone bg-brand-bg rounded-lg p-3" data-activity-name="Innendienst">
                    <h4 class="text-sm font-semibold text-brand-text-secondary">Innendienst</h4>
                    <div class="activity-officers min-h-[40px] space-y-2 mt-2"></div>
                </div>
                <div class="activity-zone bg-brand-bg rounded-lg p-3" data-activity-name="Persogespräch">
                    <h4 class="text-sm font-semibold text-brand-text-secondary">Persogespräch</h4>
                    <div class="activity-officers min-h-[40px] space-y-2 mt-2"></div>
                </div>
            </div>
        </aside>

        <!-- Main Content (Center) -->
        <div class="flex-1 flex flex-col gap-6">
            <!-- Header Roles -->
            <header class="flex-shrink-0 bg-brand-card border border-brand-border rounded-lg p-3">
                <div id="header-role-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="header-role-wrapper bg-brand-bg rounded-lg p-2 text-center" data-role-name="dispatch">
                        <span class="text-xs font-bold uppercase text-brand-text-secondary">Dispatch</span>
                        <div class="role-officer min-h-[28px] mt-1"></div>
                    </div>
                     <div class="header-role-wrapper bg-brand-bg rounded-lg p-2 text-center" data-role-name="co-dispatch">
                        <span class="text-xs font-bold uppercase text-brand-text-secondary">Co-Dispatch</span>
                        <div class="role-officer min-h-[28px] mt-1"></div>
                    </div>
                     <div class="header-role-wrapper bg-brand-bg rounded-lg p-2 text-center" data-role-name="air1">
                        <span class="text-xs font-bold uppercase text-brand-text-secondary">Air-1</span>
                        <div class="role-officer min-h-[28px] mt-1"></div>
                    </div>
                     <div class="header-role-wrapper bg-brand-bg rounded-lg p-2 text-center" data-role-name="air2">
                        <span class="text-xs font-bold uppercase text-brand-text-secondary">Air-2</span>
                        <div class="role-officer min-h-[28px] mt-1"></div>
                    </div>
                </div>
            </header>

            <!-- Vehicle Grid -->
            <main class="flex-1 overflow-y-auto bg-brand-card border border-brand-border rounded-lg p-4">
                <div id="vehicle-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                    <!-- Vehicles will be loaded here by JS -->
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Callsign List Modal -->
<div id="callsign-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 items-center justify-center" style="display: none;">
    <div class="bg-brand-card border border-brand-border rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
        <div class="p-4 border-b border-brand-border flex justify-between items-center">
            <h2 class="text-xl font-bold text-white">Callsigns & 10-Codes</h2>
            <button id="close-callsign-modal" class="text-gray-400 hover:text-white text-3xl">&times;</button>
        </div>
        <div id="callsign-modal-body" class="p-6 overflow-y-auto"></div>
    </div>
</div>

<script src="public/js/dispatch.js"></script>
<!-- End of page-specific content -->
<?php
include_once BASE_PATH . '/templates/footer.php';
?>