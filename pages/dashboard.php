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
        <!-- Header roles will be implemented later -->
        <div class="header-role">DISPATCH: --</div>
        <div class="header-role">CO-DISPATCH: --</div>
        <div class="header-role">AIR-1: --</div>
        <div class="header-role">AIR-2: --</div>
        <div class="header-actions">
            <a href="index.php?page=hr" class="button button-secondary">Personal</a>
            <a href="index.php?page=logout" class="button button-danger">Abmelden</a>
        </div>
    </header>

    <div class="dispatch-main-content">
        <aside class="dispatch-sidebar">
            <h3>Verfügbare Einheiten</h3>
            <div id="officer-list">
                <?php foreach ($availableOfficers as $officer): ?>
                    <div class="officer-card" draggable="true" data-officer-id="<?php echo $officer['id']; ?>">
                        <strong><?php echo htmlspecialchars($officer['lastName'] . ', ' . $officer['firstName']); ?></strong>
                        <span>#<?php echo htmlspecialchars($officer['badgeNumber']); ?> | <?php echo htmlspecialchars($officer['rank']); ?></span>
                    </div>
                <?php endforeach; ?>
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
</body>
</html>