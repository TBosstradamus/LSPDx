<?php
// Prevent direct access
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__ . '/Database.php';

class Dispatch {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Gets the entire current state of the dispatch board.
     * @return array
     */
    public function getState() {
        $vehicles = $this->getOnDutyVehiclesWithAssignments();
        $headerRoles = $this->getHeaderAssignments();

        $vehicleOfficerIds = $this->getAssignedOfficerIds($vehicles);
        $headerOfficerIds = array_values(array_filter(array_column($headerRoles, 'officer_id')));
        $assignedOfficerIds = array_unique(array_merge($vehicleOfficerIds, $headerOfficerIds));

        $availableOfficers = $this->getAvailableOfficers($assignedOfficerIds);

        return [
            'vehicles' => $vehicles,
            'header_roles' => $headerRoles,
            'available_officers' => $availableOfficers
        ];
    }

    private function getOnDutyVehiclesWithAssignments() {
        $sql = "SELECT v.* FROM vehicles v WHERE v.on_duty = TRUE ORDER BY v.name";
        $stmt = $this->db->query($sql);
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Now, for each vehicle, get its assigned officers
        $vehicleAssignmentsSql = "
            SELECT va.seat_index, o.id, o.firstName, o.lastName, o.badgeNumber, o.rank
            FROM vehicle_assignments va
            JOIN officers o ON va.officer_id = o.id
            WHERE va.vehicle_id = :vehicle_id
            ORDER BY va.seat_index
        ";
        $stmtAssignments = $this->db->prepare($vehicleAssignmentsSql);

        foreach ($vehicles as &$vehicle) {
            $stmtAssignments->execute([':vehicle_id' => $vehicle['id']]);
            $assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

            // Create a seats array based on capacity, filled with nulls
            $seats = array_fill(0, $vehicle['capacity'], null);
            foreach ($assignments as $assignment) {
                // Place the officer in the correct seat index
                $seats[$assignment['seat_index']] = $assignment;
            }
            $vehicle['seats'] = $seats;
        }

        return $vehicles;
    }

    private function getAssignedOfficerIds($vehicles) {
        $assignedIds = [];
        foreach($vehicles as $vehicle) {
            foreach($vehicle['seats'] as $seat) {
                if($seat !== null) {
                    $assignedIds[] = $seat['id'];
                }
            }
        }
        return $assignedIds;
    }

    private function getAvailableOfficers($assignedOfficerIds) {
        if (empty($assignedOfficerIds)) {
            $sql = "SELECT id, firstName, lastName, badgeNumber, rank FROM officers WHERE isActive = TRUE";
            $stmt = $this->db->query($sql);
        } else {
            // Create placeholders for the IN clause
            $placeholders = implode(',', array_fill(0, count($assignedOfficerIds), '?'));
            $sql = "SELECT id, firstName, lastName, badgeNumber, rank FROM officers WHERE isActive = TRUE AND id NOT IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($assignedOfficerIds);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Assigns an officer to a vehicle seat.
     * This method will remove the officer from any previous assignment.
     * @param int $officerId
     * @param int $vehicleId
     * @param int $seatIndex
     * @return bool
     */
    public function assignOfficerToVehicle($officerId, $vehicleId, $seatIndex) {
        // First, remove the officer from any existing assignment to prevent conflicts
        $this->unassignOfficer($officerId);

        try {
            $sql = "INSERT INTO vehicle_assignments (vehicle_id, officer_id, seat_index) VALUES (:vehicle_id, :officer_id, :seat_index)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':vehicle_id', $vehicleId, PDO::PARAM_INT);
            $stmt->bindParam(':officer_id', $officerId, PDO::PARAM_INT);
            $stmt->bindParam(':seat_index', $seatIndex, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error assigning officer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes an officer from any assignment they currently have.
     * @param int $officerId
     */
    public function unassignOfficer($officerId) {
        try {
            $this->db->beginTransaction();
            // Remove from vehicle assignments
            $sqlVehicle = "DELETE FROM vehicle_assignments WHERE officer_id = :officer_id";
            $stmtVehicle = $this->db->prepare($sqlVehicle);
            $stmtVehicle->execute([':officer_id' => $officerId]);

            // Remove from header assignments
            $sqlHeader = "DELETE FROM header_assignments WHERE officer_id = :officer_id";
            $stmtHeader = $this->db->prepare($sqlHeader);
            $stmtHeader->execute([':officer_id' => $officerId]);

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error unassigning officer: " . $e->getMessage());
        }
    }

    /**
     * Assigns an officer to a header role.
     * @param int $officerId
     * @param string $roleName
     * @return bool
     */
    public function assignOfficerToHeader($officerId, $roleName) {
        $this->unassignOfficer($officerId);

        try {
            $sql = "INSERT INTO header_assignments (role_name, officer_id)
                    VALUES (:role_name, :officer_id)
                    ON DUPLICATE KEY UPDATE officer_id = :officer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':role_name', $roleName, PDO::PARAM_STR);
            $stmt->bindParam(':officer_id', $officerId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error assigning officer to header: " . $e->getMessage());
            return false;
        }
    }

    private function getHeaderAssignments() {
        try {
            $sql = "SELECT h.role_name, o.id as officer_id, o.firstName, o.lastName
                    FROM header_assignments h
                    JOIN officers o ON h.officer_id = o.id";
            $stmt = $this->db->query($sql);
            $assignments = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Ensure all roles are present in the final array
            $allRoles = ['dispatch' => null, 'co-dispatch' => null, 'air1' => null, 'air2' => null];
            $assignedRoles = $this->db->query("SELECT h.role_name, o.id as officer_id, o.firstName, o.lastName FROM header_assignments h JOIN officers o ON h.officer_id = o.id")->fetchAll(PDO::FETCH_GROUP);

            foreach ($assignedRoles as $role => $officerArray) {
                $allRoles[$role] = $officerArray[0]; // fetchAll(PDO::FETCH_GROUP) creates a nested array
            }

            return $allRoles;

        } catch (PDOException $e) {
            error_log("Error fetching header assignments: " . $e->getMessage());
            return [];
        }
    }
}
?>