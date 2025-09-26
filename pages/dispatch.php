<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// --- DEPENDENCIES ---
require_once BASE_PATH . '/src/Officer.php';
require_once BASE_PATH . '/src/Vehicle.php';

// --- PAGE-SPECIFIC LOGIC ---
// We will fetch the initial state here. Dynamic updates will be done via JS.
$officerModel = new Officer();
// For now, let's just get all officers. We'll refine this later to "available" officers.
$availableOfficers = $officerModel->getAll();

$vehicleModel = new Vehicle();
// For now, let's get all vehicles. We'll refine this to "on-duty" vehicles.
$onDutyVehicles = $vehicleModel->getAll();


// --- TEMPLATE ---
$pageTitle = 'Dispatch Dashboard';
// For the dashboard, we use a slightly different template structure without the main sidebar.
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - LSPD Intranet</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/dispatch.css"> <!-- New CSS file for this page -->
</head>
<body class="dispatch-body">

    <header class="dispatch-header">
        <div class="header-role-container">
            <div class="header-role" data-role-name="dispatch">
                <span class="role-title">DISPATCH</span>
                <span class="role-officer">--</span>
            </div>
            <div class="header-role" data-role-name="co-dispatch">
                <span class="role-title">CO-DISPATCH</span>
                <span class="role-officer">--</span>
            </div>
            <div class="header-role" data-role-name="air1">
                <span class="role-title">AIR-1</span>
                <span class="role-officer">--</span>
            </div>
            <div class="header-role" data-role-name="air2">
                <span class="role-title">AIR-2</span>
                <span class="role-officer">--</span>
            </div>
        </div>
        <div class="header-actions">
            <a href="index.php?page=fleet_management" class="button button-secondary">Flotte verwalten</a>
            <a href="index.php?page=hr" class="button button-secondary">Personal</a>
            <a href="index.php?page=logout" class="button button-danger">Abmelden</a>
        </div>
    </header>

    <div class="dispatch-main-content">
        <aside class="dispatch-sidebar">
            <div class="sidebar-header">
                <h3>Verfügbare Einheiten</h3>
                <button id="open-callsign-modal" class="button button-secondary">Callsigns</button>
            </div>
            <div id="officer-list">
                <?php foreach ($availableOfficers as $officer): ?>
                    <div class="officer-card" draggable="true" data-officer-id="<?php echo $officer['id']; ?>">
                        <strong><?php echo htmlspecialchars($officer['lastName'] . ', ' . $officer['firstName']); ?></strong>
                        <span>#<?php echo htmlspecialchars($officer['badgeNumber']); ?> | <?php echo htmlspecialchars($officer['rank']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="sidebar-divider"></div>
            <h3>Weitere Tätigkeiten</h3>
            <div id="activity-zones">
                <div class="activity-zone" data-activity-name="Innendienst">
                    <h4>Innendienst</h4>
                    <div class="activity-officers"></div>
                </div>
                <div class="activity-zone" data-activity-name="Persogespräch">
                    <h4>Persogespräch</h4>
                    <div class="activity-officers"></div>
                </div>
                 <div class="activity-zone" data-activity-name="Wartung/Reparatur">
                    <h4>Wartung/Reparatur</h4>
                    <div class="activity-officers"></div>
                </div>
            </div>
        </aside>

        <main class="dispatch-grid-container">
            <div id="vehicle-grid">
                 <?php foreach ($onDutyVehicles as $vehicle): ?>
                    <div class="vehicle-card" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                        <div class="vehicle-header">
                            <span class="vehicle-name"><?php echo htmlspecialchars($vehicle['name']); ?></span>
                            <span class="vehicle-status status-1">Code 1</span>
                        </div>
                        <div class="vehicle-details">
                            <span>Callsign: --</span>
                            <span>Funk: --</span>
                        </div>
                        <div class="vehicle-seats">
                            <?php for ($i = 0; $i < $vehicle['capacity']; $i++): ?>
                                <div class="seat" data-seat-index="<?php echo $i; ?>">
                                    Sitz <?php echo $i + 1; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="public/js/dispatch.js"></script> <!-- New JS file for this page -->

    <!-- Callsign List Modal -->
    <div id="callsign-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Callsigns & 10-Codes</h2>
                <button id="close-callsign-modal" class="modal-close-button">&times;</button>
            </div>
            <div id="callsign-modal-body" class="modal-body">
                <!-- Content will be loaded here by JavaScript -->
                <p>Lade Daten...</p>
            </div>
        </div>
    </div>
</body>
</html>