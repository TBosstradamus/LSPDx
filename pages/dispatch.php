<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403); die('Forbidden');
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}
// requirePermission('dispatch_view'); // Will be enforced later

$pageTitle = 'Dispatch';
// The new header doesn't need the title passed this way, but we keep it for consistency
?>
<!DOCTYPE html>
<html lang="de" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - LSPD Intranet</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside class="w-80 flex-shrink-0 bg-gray-800 border-r border-gray-700 flex flex-col">
            <div class="px-4 py-3 border-b border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white">Einheiten</h3>
                <button id="open-callsign-modal" class="text-sm bg-gray-700 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded-lg">
                    10-Codes
                </button>
            </div>
            <div id="officer-list" class="flex-1 overflow-y-auto p-2 space-y-2">
                <!-- Available officers will be loaded here by JS -->
            </div>
            <div class="sidebar-divider border-t border-gray-700 my-2"></div>
            <div class="px-4 py-2">
                 <h3 class="text-lg font-bold text-white">Weitere Tätigkeiten</h3>
            </div>
            <div id="activity-zones" class="flex-1 overflow-y-auto p-2 space-y-2">
                 <div class="activity-zone bg-gray-900 rounded-lg p-3" data-activity-name="Innendienst">
                    <h4 class="text-sm font-semibold text-gray-400">Innendienst</h4>
                    <div class="activity-officers min-h-[40px] space-y-2 mt-2"></div>
                </div>
                <div class="activity-zone bg-gray-900 rounded-lg p-3" data-activity-name="Persogespräch">
                    <h4 class="text-sm font-semibold text-gray-400">Persogespräch</h4>
                    <div class="activity-officers min-h-[40px] space-y-2 mt-2"></div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header Roles -->
            <header class="bg-gray-900 border-b border-gray-700 p-3">
                <div id="header-role-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="header-role bg-gray-800 rounded-lg p-2 text-center" data-role-name="dispatch">
                        <span class="text-xs font-bold uppercase text-gray-400">Dispatch</span>
                        <span class="role-officer block text-white font-semibold truncate">--</span>
                    </div>
                     <div class="header-role bg-gray-800 rounded-lg p-2 text-center" data-role-name="co-dispatch">
                        <span class="text-xs font-bold uppercase text-gray-400">Co-Dispatch</span>
                        <span class="role-officer block text-white font-semibold truncate">--</span>
                    </div>
                     <div class="header-role bg-gray-800 rounded-lg p-2 text-center" data-role-name="air1">
                        <span class="text-xs font-bold uppercase text-gray-400">Air-1</span>
                        <span class="role-officer block text-white font-semibold truncate">--</span>
                    </div>
                     <div class="header-role bg-gray-800 rounded-lg p-2 text-center" data-role-name="air2">
                        <span class="text-xs font-bold uppercase text-gray-400">Air-2</span>
                        <span class="role-officer block text-white font-semibold truncate">--</span>
                    </div>
                </div>
            </header>

            <!-- Vehicle Grid -->
            <main class="flex-1 overflow-y-auto p-4">
                <div id="vehicle-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                    <!-- Vehicles will be loaded here by JS -->
                </div>
            </main>
        </div>
    </div>

    <!-- Callsign List Modal -->
    <div id="callsign-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 items-center justify-center" style="display: none;">
        <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
            <div class="p-4 border-b border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Callsigns & 10-Codes</h2>
                <button id="close-callsign-modal" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            <div id="callsign-modal-body" class="p-6 overflow-y-auto"></div>
        </div>
    </div>

    <script src="public/js/dispatch.js"></script>
</body>
</html>