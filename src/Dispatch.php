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
        // Base query to get all clocked-in, active officers
        $sql = "SELECT DISTINCT o.id, o.firstName, o.lastName, o.badgeNumber, o.rank
                FROM officers o
                JOIN time_tracking tt ON o.id = tt.officer_id
                WHERE o.isActive = TRUE AND tt.clockOutTime IS NULL";

        if (!empty($assignedOfficerIds)) {
            $placeholders = implode(',', array_fill(0, count($assignedOfficerIds), '?'));
            $sql .= " AND o.id NOT IN ($placeholders)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($assignedOfficerIds);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getOnActivityOfficers() {
        $sql = "SELECT aa.activity_name, o.id as officer_id, o.firstName, o.lastName, o.badgeNumber, o.rank
                FROM activity_assignments aa
                JOIN officers o ON aa.officer_id = o.id
                ORDER BY aa.timestamp DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignOfficerToVehicle($officerId, $vehicleId, $seatIndex) {
        return $this->performTransactionalAssignment(function() use ($officerId, $vehicleId, $seatIndex) {
            $this->_unassignOfficer($officerId);
            $sql = "INSERT INTO vehicle_assignments (vehicle_id, officer_id, seat_index) VALUES (?, ?, ?)";
            $this->db->prepare($sql)->execute([$vehicleId, $officerId, $seatIndex]);
        });
    }

    public function assignOfficerToHeader($officerId, $roleName) {
        return $this->performTransactionalAssignment(function() use ($officerId, $roleName) {
            $this->_unassignOfficer($officerId);
            $sql = "INSERT INTO header_assignments (role_name, officer_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE officer_id = ?";
            $this->db->prepare($sql)->execute([$roleName, $officerId, $officerId]);
        });
    }

    public function assignOfficerToActivity($officerId, $activityName) {
        return $this->performTransactionalAssignment(function() use ($officerId, $activityName) {
            $this->_unassignOfficer($officerId);
            $sql = "INSERT INTO activity_assignments (officer_id, activity_name) VALUES (?, ?)";
            $this->db->prepare($sql)->execute([$officerId, $activityName]);
        });
    }

    public function unassignOfficerFromAll($officerId) {
        return $this->performTransactionalAssignment(function() use ($officerId) {
            $this->_unassignOfficer($officerId);
        });
    }

    private function performTransactionalAssignment(callable $callback) {
        $this->db->beginTransaction();
        try {
            $callback();
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Transactional assignment failed: " . $e->getMessage());
            return false;
        }
    }

    private function _unassignOfficer($officerId) {
        $this->db->prepare("DELETE FROM vehicle_assignments WHERE officer_id = ?")->execute([$officerId]);
        $this->db->prepare("DELETE FROM header_assignments WHERE officer_id = ?")->execute([$officerId]);
        $this->db->prepare("DELETE FROM activity_assignments WHERE officer_id = ?")->execute([$officerId]);
    }

    private function getHeaderAssignments() {
        try {
            $allRoles = ['dispatch' => null, 'co-dispatch' => null, 'air1' => null, 'air2' => null];
            $assignedRoles = $this->db->query("SELECT h.role_name, o.id as officer_id, o.firstName, o.lastName FROM header_assignments h JOIN officers o ON h.officer_id = o.id")->fetchAll(PDO::FETCH_GROUP);
            foreach ($assignedRoles as $role => $officerArray) {
                if (isset($allRoles[$role])) {
                    $allRoles[$role] = $officerArray[0];
                }
            }
            return $allRoles;
        } catch (PDOException $e) {
            error_log("Error fetching header assignments: " . $e->getMessage());
            return [];
        }
    }
}
?>